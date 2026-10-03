<?php

namespace App\Domain\Eligibility;

use App\Domain\Enums\Desfecho;
use App\Domain\Enums\Operador;
use App\Domain\Enums\ResultadoAutomatico;
use App\Domain\Enums\TipoNo;
use App\Models\RegraElegibilidade;
use Illuminate\Support\Collection;

class MotorElegibilidade
{
    /** @return array{0: ResultadoAutomatico, 1: array<int, array<string, mixed>>} */
    public function avaliar(RegraElegibilidade $raiz, Collection $nos, array $valores): array
    {
        $porPai = $nos->groupBy(fn (RegraElegibilidade $no): string => (string) ($no->parent_id ?? 'root'));
        $resultados = [];
        $ordem = 0;
        $visitar = function (RegraElegibilidade $no, string $caminho) use (&$visitar, &$resultados, &$ordem, $porPai, $valores): Desfecho {
            $posicao = ++$ordem;
            $base = [
                'regra_elegibilidade_id' => $no->id,
                'chave_no' => $no->chave_no,
                'caminho' => $caminho,
                'ordem' => $posicao,
                'tipo_no' => $no->tipo_no,
            ];

            if ($no->tipo_no === TipoNo::CONDITION) {
                $requisito = $no->requisito;
                $valor = $valores[$requisito->codigo.'/'.$requisito->versao_semantica];
                $observado = $valor['valor'];
                $desfecho = match (true) {
                    $observado === null => Desfecho::INDETERMINADA,
                    $no->operador === Operador::PRESENT && $observado === false => Desfecho::DOCUMENTACAO_PENDENTE,
                    $no->operador === Operador::PRESENT && $observado === true => Desfecho::ATENDIDA,
                    $no->operador === Operador::NOT_PRESENT && $observado === false => Desfecho::ATENDIDA,
                    $no->operador === Operador::NOT_PRESENT && $observado === true => Desfecho::NAO_ATENDIDA,
                    $no->operador === Operador::EQ && $observado === $no->valor_esperado['valor'] => Desfecho::ATENDIDA,
                    $no->operador === Operador::NEQ && $observado !== $no->valor_esperado['valor'] => Desfecho::ATENDIDA,
                    default => Desfecho::NAO_ATENDIDA,
                };
                $detalhe = $valor['estado_efetivo'] ?? match ($observado) {
                    true => 'informado', false => 'não informado', default => 'indeterminado',
                };
                $resultados[] = $base + [
                    'requisito_codigo' => $requisito->codigo,
                    'versao_semantica' => $requisito->versao_semantica,
                    'requisito_rotulo' => $requisito->rotulo,
                    'tipo_requisito' => $requisito->tipo->value,
                    'operador' => $no->operador->value,
                    'valor_observado' => $valor,
                    'valor_esperado' => $no->valor_esperado ?? ['presenca' => $no->operador === Operador::PRESENT],
                    'desfecho' => $desfecho,
                    'explicacao' => $requisito->rotulo.': '.mb_strtolower($detalhe).'.',
                ];

                return $desfecho;
            }

            $filhos = $porPai->get((string) $no->id);
            $desfechos = [];
            $indice = 0;
            foreach ($filhos as $filho) {
                $desfechos[] = $visitar($filho, $caminho.'.'.(++$indice));
            }
            $desfecho = $this->combinar($no->operador_grupo, $desfechos);
            $resultados[] = $base + [
                'operador' => $no->operador_grupo,
                'desfecho' => $desfecho,
                'explicacao' => 'Grupo '.$no->operador_grupo.': '.$desfecho->value.'.',
            ];

            return $desfecho;
        };

        $raizDesfecho = $visitar($raiz, '1');
        usort($resultados, fn (array $a, array $b): int => $a['ordem'] <=> $b['ordem']);

        return [match ($raizDesfecho) {
            Desfecho::ATENDIDA => ResultadoAutomatico::ELEGIVEL,
            Desfecho::NAO_ATENDIDA => ResultadoAutomatico::INELEGIVEL,
            Desfecho::DOCUMENTACAO_PENDENTE => ResultadoAutomatico::PENDENTE_DOCUMENTACAO,
            Desfecho::INDETERMINADA => ResultadoAutomatico::REQUER_ANALISE_HUMANA,
        }, $resultados];
    }

    /** @param array<int, Desfecho> $desfechos */
    private function combinar(string $operador, array $desfechos): Desfecho
    {
        $precedencia = $operador === 'AND'
            ? [Desfecho::NAO_ATENDIDA, Desfecho::INDETERMINADA, Desfecho::DOCUMENTACAO_PENDENTE, Desfecho::ATENDIDA]
            : [Desfecho::ATENDIDA, Desfecho::INDETERMINADA, Desfecho::DOCUMENTACAO_PENDENTE, Desfecho::NAO_ATENDIDA];

        foreach ($precedencia as $candidato) {
            if (in_array($candidato, $desfechos, true)) {
                return $candidato;
            }
        }

        throw new \DomainException('Grupo sem filhos válidos.');
    }
}

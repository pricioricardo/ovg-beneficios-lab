<?php

namespace App\Domain\Eligibility;

use App\Domain\Enums\Operador;
use App\Domain\Enums\TipoNo;
use App\Domain\Enums\TipoRequisito;
use App\Models\RegraElegibilidade;
use App\Models\VersaoRegra;
use DomainException;
use Illuminate\Support\Collection;

class ValidadorRegra
{
    public function __construct(private readonly ResolvedorRequisitos $resolvedor) {}

    /** @return array{0: RegraElegibilidade, 1: Collection<int, RegraElegibilidade>} */
    public function validar(VersaoRegra $versao): array
    {
        $nos = $versao->nos()->with('requisito')->orderBy('ordem')->get();
        $raizes = $nos->whereNull('parent_id');

        if ($raizes->count() !== 1 || $nos->count() > 100) {
            throw new DomainException('A regra precisa ter uma raiz e tamanho controlado.');
        }

        $porPai = $nos->groupBy(fn (RegraElegibilidade $no): string => (string) ($no->parent_id ?? 'root'));
        $visitados = [];
        $condicoes = 0;
        $percorrer = function (RegraElegibilidade $no, int $grupos) use (&$percorrer, &$visitados, &$condicoes, $porPai, $versao): void {
            if (isset($visitados[$no->id]) || $no->versao_regra_id !== $versao->id) {
                throw new DomainException('A árvore contém ciclo ou referência inválida.');
            }
            $visitados[$no->id] = true;
            $filhos = $porPai->get((string) $no->id, collect());

            if ($no->tipo_no === TipoNo::GROUP) {
                $grupos++;
                if ($grupos > 3 || $filhos->count() < 2
                    || ! in_array($no->operador_grupo, ['AND', 'OR'], true)
                    || $no->requisito_id !== null || $no->operador !== null || $no->valor_esperado !== null) {
                    throw new DomainException('Grupo declarativo inválido.');
                }
                foreach ($filhos as $filho) {
                    $percorrer($filho, $grupos);
                }

                return;
            }

            if ($no->tipo_no !== TipoNo::CONDITION || $filhos->isNotEmpty()
                || $no->requisito === null || $no->operador_grupo !== null || $no->operador === null) {
                throw new DomainException('Condição declarativa inválida.');
            }
            $condicoes++;
            if ($condicoes > 30) {
                throw new DomainException('Regra excede 30 Condições.');
            }

            $this->resolvedor->validar($no->requisito);
            if ($no->requisito->tipo === TipoRequisito::BOOLEAN) {
                if (! in_array($no->operador, [Operador::EQ, Operador::NEQ], true)
                    || ! is_array($no->valor_esperado)
                    || array_keys($no->valor_esperado) !== ['valor']
                    || ! is_bool($no->valor_esperado['valor'])) {
                    throw new DomainException('Operador ou literal incompatível com BOOLEAN.');
                }
            } elseif ($no->requisito->tipo === TipoRequisito::DOCUMENT_PRESENCE) {
                if (! in_array($no->operador, [Operador::PRESENT, Operador::NOT_PRESENT], true)
                    || $no->valor_esperado !== null) {
                    throw new DomainException('Operador incompatível com DOCUMENT_PRESENCE.');
                }
            } else {
                throw new DomainException('Tipo de Requisito ainda não suportado neste gate.');
            }
        };

        $raiz = $raizes->first();
        $percorrer($raiz, 0);
        if (count($visitados) !== $nos->count()) {
            throw new DomainException('Há nós fora da raiz da regra.');
        }

        return [$raiz, $nos];
    }
}

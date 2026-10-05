<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class TestDatabaseGuard
{
    public const DATABASE = 'ovg_beneficios_lab_test';

    public static function assertSafe(): void
    {
        if (! app()->environment('testing') || config('database.default') !== 'mysql') {
            throw new RuntimeException('Reset recusado: ambiente testing e conexão mysql são obrigatórios.');
        }

        $configured = config('database.connections.mysql');
        if (! is_array($configured)
            || ($configured['driver'] ?? null) !== 'mysql'
            || ($configured['database'] ?? null) !== self::DATABASE
            || ! empty($configured['url'])) {
            throw new RuntimeException('Reset recusado: configuração MySQL de teste incompatível ou DB_URL ativa.');
        }

        $connection = DB::connection();
        if ($connection->getDriverName() !== 'mysql'
            || $connection->getDatabaseName() !== self::DATABASE
            || ($connection->selectOne('SELECT DATABASE() AS banco')->banco ?? null) !== self::DATABASE) {
            throw new RuntimeException('Reset recusado: banco efetivo diferente do banco descartável de teste.');
        }
    }
}

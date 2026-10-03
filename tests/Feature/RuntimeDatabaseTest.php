<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RuntimeDatabaseTest extends TestCase
{
    public function test_mysql_connection_can_execute_a_query(): void
    {
        $result = DB::connection('mysql')->selectOne('SELECT 1 AS connectivity_check');
        $server = DB::connection('mysql')->selectOne('SELECT VERSION() AS server_version');

        $this->assertSame(1, (int) $result->connectivity_check);
        $this->assertMatchesRegularExpression('/^8\.4\./', $server->server_version);
    }
}

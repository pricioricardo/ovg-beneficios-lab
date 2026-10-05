<?php

namespace Tests\Feature;

use App\Support\TestDatabaseGuard;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;
use Tests\Support\SafeRefreshDatabase;

class TestDatabaseGuardTest extends TestCase
{
    use SafeRefreshDatabase;

    public function test_accepts_only_the_effective_disposable_mysql_database(): void
    {
        TestDatabaseGuard::assertSafe();
        $this->assertSame('ovg_beneficios_lab_test', DB::selectOne('SELECT DATABASE() AS banco')->banco);
    }

    public function test_rejects_db_url_even_when_db_database_looks_safe(): void
    {
        $this->assertSame('ovg_beneficios_lab_test', config('database.connections.mysql.database'));
        config(['database.connections.mysql.url' => 'mysql://mysql/ovg_beneficios_lab']);

        $this->expectException(RuntimeException::class);
        TestDatabaseGuard::assertSafe();
    }

    public function test_rejects_incompatible_resolved_configuration_including_cached_values(): void
    {
        config(['database.connections.mysql.database' => 'ovg_beneficios_lab']);

        $this->expectException(RuntimeException::class);
        TestDatabaseGuard::assertSafe();
    }

    public function test_rejects_another_driver(): void
    {
        config(['database.default' => 'sqlite']);

        try {
            $this->expectException(RuntimeException::class);
            TestDatabaseGuard::assertSafe();
        } finally {
            config(['database.default' => 'mysql']);
        }
    }

    public function test_rejects_non_testing_environment(): void
    {
        $this->app->detectEnvironment(fn (): string => 'local');

        $this->expectException(RuntimeException::class);
        TestDatabaseGuard::assertSafe();
    }

    public function test_rejects_different_database_reported_by_mysql_itself(): void
    {
        DB::connection()->getPdo()->exec('USE `ovg_beneficios_lab`');

        try {
            $this->expectException(RuntimeException::class);
            TestDatabaseGuard::assertSafe();
        } finally {
            DB::connection()->getPdo()->exec('USE `ovg_beneficios_lab_test`');
        }
    }
}

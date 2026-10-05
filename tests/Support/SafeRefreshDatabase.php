<?php

namespace Tests\Support;

use App\Support\TestDatabaseGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;

trait SafeRefreshDatabase
{
    use RefreshDatabase {
        refreshDatabase as protected refreshDatabaseAfterGuard;
    }

    public function refreshDatabase()
    {
        TestDatabaseGuard::assertSafe();
        $this->refreshDatabaseAfterGuard();
    }
}

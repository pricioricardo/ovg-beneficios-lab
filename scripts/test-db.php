<?php

use App\Support\TestDatabaseGuard;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

require dirname(__DIR__).'/vendor/autoload.php';

$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

try {
    TestDatabaseGuard::assertSafe();
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage().PHP_EOL);
    exit(1);
}

echo 'MySQL efetivo: '.TestDatabaseGuard::DATABASE.PHP_EOL;

if (($argv[1] ?? null) === '--reset') {
    $exit = Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
    echo Artisan::output();
    exit($exit);
}

if (isset($argv[1])) {
    fwrite(STDERR, 'Uso: php scripts/test-db.php [--reset]'.PHP_EOL);
    exit(2);
}

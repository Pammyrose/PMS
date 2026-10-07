<?php

// Read-only page profiling; output contains timings and query counts, never user data.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$started = microtime(true);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app['config']->set('session.driver', 'array');
if (! in_array('--persistent-cache', $argv, true)) {
    $app['config']->set('cache.default', 'array');
}
echo 'Bootstrap: '.round((microtime(true) - $started) * 1000).' ms'.PHP_EOL;
$queries = [];
Illuminate\Support\Facades\DB::listen(function ($query) use (&$queries) {
    $queries[] = ['sql' => $query->sql, 'ms' => $query->time];
});
try {
    $user = App\Models\User::query()->where('role', 'admin')->first();
    if (! $user) {
        throw new RuntimeException('No local administrator available for profiling.');
    }
    Illuminate\Support\Facades\Auth::setUser($user);
    foreach (['/dashboard', '/dashboard'] as $path) {
        $queries = [];
        $request = Illuminate\Http\Request::create($path, 'GET');
        $request->setLaravelSession($app['session']->driver());
        $app->instance('request', $request);
        $started = microtime(true);
        $controller = $app->make($path === '/dashboard' ? App\Http\Controllers\DashboardController::class : App\Http\Controllers\GassController::class);
        $view = $controller->index($request);
        $controllerMs = (microtime(true) - $started) * 1000;
        $html = $view->render();
        echo json_encode(['page' => $path, 'controller_ms' => round($controllerMs), 'total_ms' => round((microtime(true) - $started) * 1000), 'queries' => count($queries), 'database_ms' => round(array_sum(array_column($queries, 'ms'))), 'html_bytes' => strlen($html)]).PHP_EOL;
        $groups = [];
        foreach ($queries as $query) {
            $sql = $query['sql'];
            $groups[$sql] = ($groups[$sql] ?? 0) + 1;
        }
        arsort($groups);
        foreach (array_slice($groups, 0, 8, true) as $sql => $count) {
            echo $count.'x '.substr($sql, 0, 180).PHP_EOL;
        }
        usort($queries, fn ($a, $b) => $b['ms'] <=> $a['ms']);
        foreach (array_slice($queries, 0, 5) as $query) {
            echo $query['ms'].' ms '.substr($query['sql'], 0, 220).PHP_EOL;
        }
    }
    foreach (['physical_targets', 'physical_accomplishments', 'financial_target', 'financial_accomplishment'] as $table) {
        echo $table.': '.Illuminate\Support\Facades\DB::table($table)->count().' rows'.PHP_EOL;
    }
} catch (Throwable $error) {
    // Avoid printing exception text, which may include connection credentials.
    fwrite(STDERR, 'Profiling failed: '.get_class($error).PHP_EOL);
    exit(1);
}

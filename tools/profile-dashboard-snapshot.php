<?php

// Private, read-only fixtures for browser profiling; never expose record contents.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$request = Illuminate\Http\Request::create('/dashboard', 'GET');
$app->instance('request', $request);
$app->make(Illuminate\Contracts\Http\Kernel::class)->bootstrap();
$app['config']->set('session.driver', 'array');
$app['config']->set('cache.default', 'array');
Illuminate\Support\Facades\Auth::setUser(App\Models\User::query()->where('role', 'admin')->firstOrFail());
foreach (['full' => [], 'partial' => ['X-Dashboard-Partial' => '1'], 'pap' => ['X-Dashboard-List' => 'pap'], 'indicator' => ['X-Dashboard-List' => 'indicator']] as $name => $headers) {
    $request = Illuminate\Http\Request::create('/dashboard', 'GET', $name === 'full' ? [] : ['sector' => 'gass']);
    foreach ($headers as $key => $value) {
        $request->headers->set($key, $value);
    }
    $app->instance('request', $request);
    $html = $app->make(App\Http\Controllers\DashboardController::class)->index($request)->render();
    $html = str_replace(rtrim(asset(''), '/').'/', 'file:///'.str_replace('\\', '/', public_path()).'/', $html);
    file_put_contents(storage_path('app/private/dashboard-'.$name.'-profile.html'), $html);
    echo $name.': '.strlen($html).' bytes'.PHP_EOL;
}

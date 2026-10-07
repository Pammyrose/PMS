<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$request = Illuminate\Http\Request::create('/gass_physical', 'GET');
$app->instance('request', $request);
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
$app['config']->set('session.driver', 'array');
$app['config']->set('cache.default', 'array');
$user = App\Models\User::query()->where('role', 'admin')->firstOrFail();
Illuminate\Support\Facades\Auth::setUser($user);
$response = $kernel->handle($request);
if ($response->getStatusCode() !== 200) {
    fwrite(STDERR, 'Table snapshot failed with status '.$response->getStatusCode().PHP_EOL);
    exit(1);
}
$html = $response->getContent();
$publicUrl = 'file:///'.str_replace('\\', '/', public_path()).'/';
$html = str_replace(rtrim(asset(''), '/').'/', $publicUrl, $html);
$path = storage_path('app/private/table-profile.html');
file_put_contents($path, $html);
echo 'Private table snapshot generated ('.strlen($html).' bytes).'.PHP_EOL;

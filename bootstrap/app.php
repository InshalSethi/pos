<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'admin/api/*',
        ]);

        $middleware->append(\App\Http\Middleware\SetSystemTimezone::class);
        $middleware->append(\App\Http\Middleware\PreventBackHistory::class);
        
        $middleware->api(append: [
            \App\Http\Middleware\CheckEmployeeActiveStatus::class,
        ], prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetUserLocalizationContext::class,
            \App\Http\Middleware\SetTenantLocalization::class,
        ]);

        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return $request->expectsJson() ? null : url('/admin/login');
            }
            return $request->expectsJson() ? null : route('login');
        });

        $middleware->redirectUsersTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('admin/*') || $request->is('admin')) {
                return url('/admin');
            }
            return '/';
        });
        $middleware->alias([
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'check.employee.active' => \App\Http\Middleware\CheckEmployeeActiveStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

if (getenv('LARAVEL_STORAGE_PATH')) {
    $_ENV['LARAVEL_STORAGE_PATH'] = getenv('LARAVEL_STORAGE_PATH');
    $_SERVER['LARAVEL_STORAGE_PATH'] = getenv('LARAVEL_STORAGE_PATH');
}

if (env('LARAVEL_STORAGE_PATH')) {
    $storagePath = env('LARAVEL_STORAGE_PATH');
    $_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
    $_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;
    $app->useStoragePath($storagePath);

    $viewsPath = env('VIEW_COMPILED_PATH', $storagePath . '/framework/views');
    $sessionsPath = $storagePath . '/framework/sessions';
    $cachePath = $storagePath . '/framework/cache/data';
    $logsPath = $storagePath . '/logs/laravel.log';

    foreach ([$viewsPath, $sessionsPath, dirname($cachePath), dirname($logsPath)] as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
    }

    config([
        'view.compiled' => $viewsPath,
        'session.files' => $sessionsPath,
        'cache.stores.file.path' => $cachePath,
        'cache.stores.file.lock_path' => $cachePath,
        'logging.channels.single.path' => $logsPath,
        'logging.channels.daily.path' => $logsPath,
    ]);
}

$app->booted(function () {
    if (env('LARAVEL_STORAGE_PATH')) {
        $storagePath = env('LARAVEL_STORAGE_PATH');
        config([
            'view.compiled' => env('VIEW_COMPILED_PATH', $storagePath . '/framework/views'),
            'session.files' => $storagePath . '/framework/sessions',
            'cache.stores.file.path' => $storagePath . '/framework/cache/data',
            'cache.stores.file.lock_path' => $storagePath . '/framework/cache/data',
            'logging.channels.single.path' => $storagePath . '/logs/laravel.log',
            'logging.channels.daily.path' => $storagePath . '/logs/laravel.log',
        ]);
    }
});

return $app;

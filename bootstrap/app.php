<?php

use App\Http\Middleware\EnsureAdminIsActive;
use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
        then: function () {
            Route::middleware('web')
                ->prefix('dashboard')
                ->name('dashboard.')
                ->group(base_path('routes/dashboard.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The app only ever sits behind the host nginx reverse proxy (never
        // exposed directly), so trust it for X-Forwarded-* headers -
        // otherwise APP_URL/asset URLs generate as http:// and
        // Request::isSecure() is wrong behind TLS termination.
        $middleware->trustProxies(at: '*');

        // Every API request is treated as JSON, so validation/auth errors
        // are always rendered as JSON regardless of the client's Accept header.
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);

        // The Inertia website and dashboard: the visitor's language first,
        // so the shared Inertia props are rendered in it.
        $middleware->web(append: [
            SetLocale::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin' => EnsureIsAdmin::class,
            'admin.active' => EnsureAdminIsActive::class,
        ]);

        // Only dashboard routes are guarded by the `web` session guard.
        $middleware->redirectGuestsTo(fn () => route('dashboard.login'));
        $middleware->redirectUsersTo(fn () => route('dashboard.index'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Website/dashboard errors render the branded Inertia error page
        // outside local development (where the debug page is more useful),
        // and an expired CSRF token sends the visitor back with a notice.
        $exceptions->respond(function (Response $response, Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $response;
            }

            $status = $response->getStatusCode();

            if ($status === 419) {
                return back()->with('error', __('The page expired, please try again.'));
            }

            if (! app()->environment(['local', 'testing']) && in_array($status, [403, 404, 500, 503], true)) {
                // The web middleware may not have run (unknown route, binding
                // 404), so apply the locale and shared props here too.
                app()->setLocale(SetLocale::resolve($request)->value);
                Inertia::share(app(HandleInertiaRequests::class)->share($request));

                return Inertia::render('website/Error', ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }

            return $response;
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Keep the JSON error envelope consistent - { "message": ... } (or
        // { "message": ..., "errors": ... } for validation) - regardless of
        // APP_DEBUG. Without this, Laravel's default renderer dumps the full
        // exception + stack trace into the response body whenever debug mode
        // is on, for every exception except ValidationException.
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null;
            }

            if ($e instanceof ValidationException) {
                return null; // Laravel's default shape already matches our envelope.
            }

            // Note: Laravel's Handler::prepareException() already converts
            // AuthorizationException/ModelNotFoundException into
            // AccessDeniedHttpException/NotFoundHttpException before this
            // callback runs, so both are caught by the HttpExceptionInterface
            // branch below. AuthenticationException is the one exception
            // left untouched by that step.
            $status = match (true) {
                $e instanceof AuthenticationException => 401,
                $e instanceof HttpExceptionInterface => $e->getStatusCode(),
                default => 500,
            };

            $message = match (true) {
                $e instanceof AuthenticationException => 'Unauthenticated.',
                $status === 404 && $e->getPrevious() instanceof ModelNotFoundException => 'Resource not found.',
                $status === 500 && ! config('app.debug') => 'Server error.',
                default => $e->getMessage() ?: 'Error.',
            };

            $payload = ['message' => $message];

            if ($status === 500 && config('app.debug')) {
                $payload['exception'] = $e::class;
            }

            return response()->json($payload, $status);
        });
    })->create();

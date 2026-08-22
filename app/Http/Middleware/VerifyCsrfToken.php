<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        // Désactiver CSRF en environnement de test
        $isTesting = ($this->app && $this->app->runningUnitTests())
            || class_exists('PHPUnit\Framework\TestCase')
            || (app()->bound('phpunit') || defined('PHPUNIT_TEST'));

        if ($isTesting) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}

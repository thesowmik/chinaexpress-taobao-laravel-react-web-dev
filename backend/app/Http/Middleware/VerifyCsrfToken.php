<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

/**
 * Class VerifyCsrfToken.
 */
class VerifyCsrfToken extends Middleware
{
  /**
   * The URIs that should be excluded from CSRF verification.
   *
   * @var array
   */
  protected $except = [
    '/success', '/cancel', '/fail', '/ipn',
    '/bkash/execute',
  ];
}
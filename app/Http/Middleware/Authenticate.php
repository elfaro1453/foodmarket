<?php

namespace App\Http\Middleware;

use Illuminate\Support\Str;
use App\Helpers\ResponseFormatter;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {

        if (request()->wantsJson() || Str::startsWith(request()->path(), 'api')) {
            // return unauthorized message
            return route('api.unauthorized');
        }

        return route('login');
    }
}

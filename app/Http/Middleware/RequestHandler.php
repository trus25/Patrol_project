<?php

namespace App\Http\Middleware;

use App\Http\Controllers\v1\Helper\ResponseHandler;
use App\Models\v1\Report;
use App\Models\v1\Checkpoint;

use Closure;

class RequestHandler
{
    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct()
    {
        $this->respHandler = new ResponseHandler();
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check integrity variable
        if (! Report::find($request->id_report) && $request->id_report)
            return $this->respHandler->success('Id report not found.');
        if (! Checkpoint::find($request->id_checkpoint) && $request->id_report)
            return $this->respHandler->success('Id checkpoint not found.');

        return $next($request);
    }
}

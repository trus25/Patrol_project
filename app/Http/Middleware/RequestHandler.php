<?php

namespace App\Http\Middleware;

use App\Http\Controllers\v1\Helper\ResponseHandler;
use App\Models\v1\Report;
use App\Models\v1\Checkpoint;
use App\Models\v1\Corporate;
use App\Models\v1\Customer;
use App\Models\v1\Message;
use App\Models\v1\People;
use App\Models\v1\ReportDetail;
use App\Models\v1\Schedule;
use App\Models\v1\SecuritySchedule;
use App\Models\v1\Security;
use App\Models\v1\Site;
use App\Models\v1\SiteSchedule;
use App\Models\v1\User;

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
        if (! Schedule::find($request->id_schedule) && $request->id_schedule)
            return $this->respHandler->success('Id schedule not found.');
        if (! SiteSchedule::find($request->id_site_schedule) && $request->id_site_schedule)
            return $this->respHandler->success('Id site schedule not found.');
        if (! Site::find($request->id_site) && $request->id_site)
            return $this->respHandler->success('Id site not found.');
        if (! Corporate::find($request->id_corporate) && $request->id_corporate)
            return $this->respHandler->success('Id corporate not found.');
        if (! Customer::find($request->id_customer) && $request->id_customer)
            return $this->respHandler->success('Id customer not found.');
        if (! People::find($request->id_people) && $request->id_people)
            return $this->respHandler->success('Id people not found.');
        if (! User::find($request->id_user) && $request->id_user)
            return $this->respHandler->success('Id user not found.');
        if (! Message::find($request->id_message) && $request->id_message)
            return $this->respHandler->success('Id people not found.');
        if (! ReportDetail::find($request->id_report_detail) && $request->id_report_detail)
            return $this->respHandler->success('Id report detail not found.');
        if (! Report::find($request->id_report) && $request->id_report)
            return $this->respHandler->success('Id report not found.');
        if (! Checkpoint::find($request->id_checkpoint) && $request->id_checkpoint)
            return $this->respHandler->success('Id checkpoint not found.');
        if (! SecuritySchedule::find($request->id_security_schedule) && $request->id_security_schedule)
            return $this->respHandler->success('Id security schedule not found.');
        if (! Security::find($request->id_security) && $request->id_security)
            return $this->respHandler->success('Id security not found.');
        if (! Security::find($request->id_security_real) && $request->id_security_real)
            return $this->respHandler->success('Id security real not found.');
        if (! Security::find($request->id_security_plan) && $request->id_security_plan)
            return $this->respHandler->success('Id security plan not found.');

        return $next($request);
    }
}

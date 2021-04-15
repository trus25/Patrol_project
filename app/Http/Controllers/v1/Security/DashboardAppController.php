<?php

namespace App\Http\Controllers\v1\Security;

use App\Http\Controllers\Controller;
use App\Models\v1\Checkpoint;
use App\Models\v1\Message;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DashboardAppController extends Controller
{
    /**
     * Get dashboard data
     * GET api/v1/security/dashboard
     * @return Response
     **/
	public function index()
	{
        try
        {
            // Get total data
            $data = [
                'total_checkpoint' => Checkpoint::get()->count(),
                'total_sos' => Message::where('report_type', 'S')->get()->count()
            ];

            return $this->respHandler->success('Success get data.', $data);
        }
        catch(\Exception $e)
        {
            return $this->respHandler->requestError($e->getMessage());
        }
	}
}

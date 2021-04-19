<?php

namespace App\Http\Controllers\v1\Client;

use App\Http\Controllers\Controller;
use App\Models\v1\Report;
use App\Models\v1\ReportDetail;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Get report list
     * GET api/v1/client/report
     * @return Response
     **/
	public function index()
	{
        try
        {
            $report = Report::with('security_schedule.site_schedule.site', 'security_schedule.site_schedule.schedule', 'security_schedule.security_plan.people')->get();

            $reportValue = [];
            foreach($report as $reportCollection)
            {
                $data[] = [
                    'id' => $reportCollection->id,
                    'site_name' => $reportCollection->security_schedule->site_schedule->site->name,
                    'security' => $reportCollection->security_schedule->security_plan->people->name,
                    'date' => $reportCollection->date,
                    'start' => $reportCollection->start,
                    'end' => $reportCollection->end,
                ];
            }

            return $this->respHandler->success('Success get data.', $data);
        }
        catch(\Exception $e)
        {
            return $this->respHandler->requestError($e->getMessage());
        }
	}

    /**
     * Get report detail data by id report
     * GET api/v1/client/report/detail
     * @param id_report
     * @return Response
     **/
	public function reportDetail($id)
	{
        try
        {
            $reportDetail = ReportDetail::where('id_report', $id)->with('message')->get();

            if ($reportDetail)
            {
                return $this->respHandler->success('Success get data.', $reportDetail);
            }
            else
                return $this->respHandler->success('Report detail not found.');
        }
        catch(\Exception $e)
        {
            return $this->respHandler->requestError($e->getMessage());
        }
	}
}

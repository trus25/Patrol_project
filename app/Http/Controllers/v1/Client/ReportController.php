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
	public function index(Request $request)
	{
        try
        {
            /** 
             * Filter status $request->filter
             * default = Watching Patrol 
             * 1 = Completed Patrol
             **/ 
            $report = Report::with('security_schedule.site_schedule.site', 'security_schedule.site_schedule.schedule', 'security_schedule.security_plan.people');
            if ($request->filter == 1) 
                $report = $report->where('end', '!=', NULL);
            else
                $report = $report->where('end', NULL);
            $report = $report->orderBy('created_at', 'DESC')->get();

            $data = [];
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
            $reportDetail = ReportDetail::where('id_report', $id)->with('message', 'report.security_schedule.site_schedule.site.checkpoint')->get();

            $report = [];
            foreach($reportDetail as $detailCollection)
            {
                // Get message list
                $message = [];
                foreach($detailCollection->message as $messageCollection)
                {
                    $message[] = [
                        'respondent_name' => $messageCollection->user->people->name,
                        'content' => $messageCollection->message,
                        'created_at' => $messageCollection->created_at,
                    ];
                }

                $report[] = [
                    'id' => $detailCollection->id,
                    'id_report' => $detailCollection->id_report,
                    'id_checkpoint' => $detailCollection->id_checkpoint,
                    'checkpoint_name' => $detailCollection->checkpoint->name,
                    'message' => $message,
                ];
            }

            // Get checkpoint list
            $data = [];
            $checkpointList_Check = [];
            $checkpoint = Report::find($id);
            foreach($checkpoint->security_schedule->site_schedule->site->checkpoint as $checkpointList)
            {
                // Return all to data
                foreach($report as $reportValue)
                {
                    if ($reportValue['id_checkpoint'] == $checkpointList->id)
                    {
                        $data[] = $reportValue;
                        $checkpointList_Check[] = $reportValue['id_checkpoint'];
                    }
                }
                
                // Push empty checkpoint to data
                if (! in_array($checkpointList->id, $checkpointList_Check))
                {
                    $data[] = [
                        'id' => $checkpointList->id,
                        'checkpoint_name' => $checkpointList->name,
                    ];
                }
            }

            return $this->respHandler->success('Success get data.', $data);
        }
        catch(\Exception $e)
        {
            return $this->respHandler->requestError($e->getMessage());
        }
	}
}

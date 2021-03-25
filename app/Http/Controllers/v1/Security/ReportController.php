<?php

namespace App\Http\Controllers\v1\Security;

use App\Http\Controllers\Controller;
use App\Models\v1\Report;
use App\Models\v1\ReportDetail;
use App\Models\v1\Checkpoint;
use App\Models\v1\SecuritySchedule;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Main check status patrol, start or continue
     * POST api/v1/security/report/start-patrol/
     * @param id_people
     * @return Response
     **/
	public function startPatrol(Request $request)
	{
        try
        {
            $validator = Validator::make($request->post(), [
                'id_people' => 'required',
            ]);

            if (! $validator->fails())
            {
                // Check security schedule time, compare time from date now
                $dateNow = $this->coreSystem->dateNow();
                $siteScheduleActive = $this->coreSystem->siteActive();
                $securityScheduleCheck = SecuritySchedule::whereIn('id', $siteScheduleActive)
                    ->where('id_security_plan', $request->id_people)
                    ->with('site_schedule.schedule')
                    ->get();
                
                // Matched schedule
                if ($securityScheduleCheck->count() == 1) 
                {
                    // Get checkpoint list
                    $checkpointList = [];
                    $checkpoint = Checkpoint::where('id_site', $securityScheduleCheck[0]->site_schedule->site->id)->get();

                    foreach ($checkpoint as $dataCheckpoint) 
                    {
                        $checkpointList[] = $dataCheckpoint->toArray();
                    }

                    // Check data exists
                    $report = Report::where('id_security_schedule', $securityScheduleCheck[0]->id)
                        ->where('id_security_real', $request->id_people)
                        ->where('date', $dateNow['date']->toDateString())
                        ->where('end', NULL)->get();

                    if ($report->isEmpty()) 
                    {
                        $report = new Report;
                        $report->id_security_schedule = $securityScheduleCheck[0]->id;
                        $report->id_security_real = $request->id_people;
                        $report->date = $dateNow['date']->toDateString();
                        $report->start = $dateNow['time'];
                        $report->save();

                        $data = [
                            'report' => $report,
                            'checkpoint' => $checkpointList,
                        ];

                        return $this->respHandler->success('Patrol has been started, please check this checkpoint.', $data);
                    } 
                    else
                    {
                        $data = [
                            'report' => $report,
                            'checkpoint' => $checkpointList,
                        ];

                        return $this->respHandler->success('Patrol already started, please check this checkpoint.', $data);
                    }
                }
                else
                {
                    // todo else, security schedule not found, send request to access app
                    // MODEL
                    // - remove id_security_real on reports model
                    // - added new model called security_request
                    // -> attribut = id, id_security_schedule, id_security_request, status
                    // CONTROLLER
                    // - check active site for security_request
                    // - insert data to security_request
                    // - return data to json

                    $data = [
                        // 'id_security_schedule' => // Get security schedule today
                        'id_security_request' => $request->id_people,
                    ];

                    return $this->respHandler->success('Its not your schedule.', $data);
                }
            }
            else
                return $this->respHandler->requestError($validator->errors());
        }
        catch(\Exception $e)
        {
            return $this->respHandler->requestError($e->getMessage());
        }
	}

    /**
     * Stop patrol status
     * POST api/v1/security/report/stop-patrol/
     * @param id_report
     * @return Response
     **/
	public function stopPatrol(Request $request)
	{
        try
        {
            $validator = Validator::make($request->post(), [
                'id_report' => 'required',
            ]);

            if (! $validator->fails())
            {
                $dateNow = $this->coreSystem->dateNow();
                // Check data exists
                $report = Report::find($request->id_report);
                
                if ($report)
                {
                    if (! $report->end)
                    {
                        $report->end = $dateNow['time'];
                        $report->save();
                        
                        $data = [
                            'report' => $report,
                        ];
                        
                        return $this->respHandler->success('Patrol today has been stopped.', $data);
                    }
                    else
                        return $this->respHandler->success('Patrol already stopped.');
                }
                else
                {
                    $data = [
                        'report' => $report,
                    ];

                    return $this->respHandler->success('Data not exists.', $data);
                }
            }
            else
                return $this->respHandler->requestError($validator->errors());
        }
        catch(\Exception $e)
        {
            return $this->respHandler->requestError($e->getMessage());
        }
	}

    /**
     * Send report message
     * POST api/v1/security/report/send/
     * @param id_report
     * @param id_checkpoint
     * @param message
     * @return Response
     **/
	public function sendReport(Request $request)
	{
        try
        {
            dd($request->post());
            $validator = Validator::make($request->post(), [
                'id_report' => 'required',
                'id_checkpoint' => 'required',
                'message' => 'required',
            ]);

            if (! $validator->fails())
            {
                // todo, insert to report_detail and messages
                // check report_detail first if exist, just  insert
            }
            else
                return $this->respHandler->requestError($validator->errors());
        }
        catch(\Exception $e)
        {
            return $this->respHandler->requestError($e->getMessage());
        }
	}
}

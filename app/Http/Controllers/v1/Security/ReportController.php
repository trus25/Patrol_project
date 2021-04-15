<?php

namespace App\Http\Controllers\v1\Security;

use App\Http\Controllers\Controller;
use App\Models\v1\Report;
use App\Models\v1\ReportDetail;
use App\Models\v1\Message;
use App\Models\v1\Checkpoint;
use App\Models\v1\SecuritySchedule;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Main check status patrol, start or continue
     * GET api/v1/security/report/start-patrol/
     * @param id_people
     * @return Response
     **/
	public function startPatrol(Request $request)
	{
        try
        {
            $user = $this->authUser();
            // People security not found
            if (empty($user->people->security))
                return $this->respHandler->success('Securities not found.');

            // Check security schedule time, compare time from date now
            $dateNow = $this->coreSystem->dateNow();
            $siteScheduleActive = $this->coreSystem->siteActive();
            $securityScheduleCheck = SecuritySchedule::whereIn('id_site_schedule', $siteScheduleActive)
                ->where('id_security_plan', $user->people->security->id)
                ->with('site_schedule.schedule')
                ->get();

            // Matched schedule
            if ($securityScheduleCheck->count() == 1) 
            {
                // Check data exists
                $report = Report::where('id_security_schedule', $securityScheduleCheck[0]->id)
                    ->where('id_security_real', $user->people->security->id)
                    ->where('date', $dateNow['date']->toDateString())
                    ->where('end', NULL)->first();
                
                // Data not exists, creating new report 
                if (! $report) 
                {
                    $report = new Report;
                    $report->id_security_schedule = $securityScheduleCheck[0]->id;
                    $report->id_security_real = $user->people->security->id;
                    $report->date = $dateNow['date']->toDateString();
                    $report->start = $dateNow['time'];
                    $report->save();

                    $data = [
                        'status' => 1,
                        'id_site' => $securityScheduleCheck[0]->site_schedule->site->id,
                        'report' => $report
                    ];

                    return $this->respHandler->success('Patrol has been started.', $data);
                } 
                // Data exists, show report collection
                else
                {
                    $data = [
                        'status' => 1,
                        'id_site' => $securityScheduleCheck[0]->site_schedule->site->id,
                        'report' => $report,
                    ];

                    return $this->respHandler->success('Patrol already started.', $data);
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
                    'message' => 'Work in Progress!',
                    'status_request' => 1,
                    'id_security_request' => $user->people->security->id,
                ];

                return $this->respHandler->success('Its not your schedule.', $data);
            }
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
                
                // Report exist, stopped patrol
                if ($report)
                {
                    // Updated stopped time for patrol
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
                    return $this->respHandler->success('Data not exists.');
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
     * Get checkpoint list
     * GET api/v1/security/report/checkpoint-list
     * @param id_report
     * @param id_checkpoint
     * @return Response
     **/
	public function checkpointList(Request $request)
	{
        try
        {
            $validator = Validator::make($request->all(), [
                'id_site' => 'required',
            ]);

            if (! $validator->fails())
            {
                // Get checkpoint list
                $checkpointList = [];
                $checkpoint = Checkpoint::where('id_site', $request->id_site)->get();

                foreach ($checkpoint as $dataCheckpoint) 
                {
                    $checkpointList[] = $dataCheckpoint->toArray();
                }

                return $this->respHandler->success('Success get data.', ['checkpoint' => $checkpointList]);
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
     * POST api/v1/security/report/send
     * @param id_report
     * @param id_checkpoint
     * @param message
     * @return Response
     **/
	public function sendReport(Request $request)
	{
        try
        {
            $validator = Validator::make($request->post(), [
                'id_report' => 'required',
                'id_checkpoint' => 'required',
                'type' => 'required|in:Video,Text,Audio,Image',
                'message' => 'required',
                'lat' => 'required',
                'long' => 'required',
            ]);

            if (! $validator->fails())
            {
                $user = $this->authUser();
                // Check data exists
                $reportDetail = ReportDetail::where('id_report', $request->id_report)
                    ->where('id_checkpoint', $request->id_checkpoint)->first();

                // Send report without creating new report detail
                if ($reportDetail)
                {
                    $message = new Message;
                    $message->id_report = $request->id_report;
                    $message->id_report_detail = $reportDetail->id;
                    $message->id_respondent = $user->id;
                    $message->message = $request->message;
                    $message->lat = $request->lat;
                    $message->long = $request->long;
                    $message->save();
                }
                // Send report with creating new report detail
                else
                {
                    $dateNow = $this->coreSystem->dateNow();

                    $reportDetail = new ReportDetail;
                    $reportDetail->id_report = $request->id_report;
                    $reportDetail->id_checkpoint = $request->id_checkpoint;
                    $reportDetail->time = $dateNow['date'];
                    $reportDetail->save();

                    $message = new Message;
                    $message->id_report = $request->id_report;
                    $message->id_report_detail = $reportDetail->id;
                    $message->id_respondent = $user->id;
                    $message->message = $request->message;
                    $message->lat = $request->lat;
                    $message->long = $request->long;
                    $message->save();
                }

                return $this->respHandler->success('Message already sent.', $message);
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
     * Get checkpoint report data
     * GET api/v1/security/report/checkpoint-report
     * @param id_report
     * @param id_checkpoint
     * @return Response
     **/
	public function checkpointMessage(Request $request)
	{
        try
        {
            $validator = Validator::make($request->all(), [
                'id_report' => 'required',
                'id_checkpoint' => 'required',
            ]);

            if (! $validator->fails())
            {
                $reportDetail = ReportDetail::where('id_report', $request->id_report)
                    ->where('id_checkpoint', $request->id_checkpoint)->first();

                if ($reportDetail)
                {
                    return $this->respHandler->success('Success get data.', $reportDetail->message);
                }
                else
                    return $this->respHandler->success('Report not found.');
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

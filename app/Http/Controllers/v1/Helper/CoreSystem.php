<?php

namespace App\Http\Controllers\v1\Helper;

use App\Models\v1\SiteSchedule;

use Carbon\Carbon;

class CoreSystem
{
    /**
     * Get active site
     **/
	public function siteActive()
	{
        $siteScheduleActive = [];
        $dateNow = $this->dateNow();
        $siteSchedule = SiteSchedule::whereHas('schedule', function ($query) use ($dateNow) {
            $query->where('day', $dateNow['day']);
            $query->whereTime('start', '<=', $dateNow['time']);
            $query->whereTime('end', '>=', $dateNow['time']);
        })->get();

        foreach ($siteSchedule as $data) 
        {
            $siteScheduleActive[] = $data->id;
        }

        return $siteScheduleActive;
	}

    /**
     * Get date time now
     **/
    public function dateNow()
    {
        // Check security schedule time, compare time from date now
        $createDate = Carbon::now()->locale('id');
        $createDate->settings(['formatFunction' => 'translatedFormat']);
        $dateNow = [
            'date' => $createDate,
            'day' => $createDate->format('l'),
            'time' => $createDate->format('H:i:s')
        ];

        return $dateNow;
    }
}

<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecuritySchedule extends Model
{
    use HasFactory;
    protected $table = 'security_schedules';

    public function security_plan()
    {
    	return $this->belongsTo('App\Models\v1\Security', 'id_security_plan', 'id');
    }

    public function site_schedule()
    {
    	return $this->belongsTo('App\Models\v1\SiteSchedule', 'id_site_schedule', 'id');
    }

    public function report()
    {
    	return $this->hasMany('App\Models\v1\Report', 'id_site_schedule', 'id');
    }
}

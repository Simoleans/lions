<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'start_date',
        'end_date',
        'remaining_classes',
        'status',
    ];

    protected $appends = ['is_expired', 'remaining_days'];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function ($subscription) {
            if ($subscription->is_expired && $subscription->status === 'active') {
                $subscription->update(['status' => 'inactive']);
            }
        });
    }

    /**
     * Relación: la suscripción pertenece a un usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación: la suscripción pertenece a un plan
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Relación con asistencias: una suscripción puede tener muchas asistencias registradas
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function getIsExpiredAttribute()
    {
        if (is_null($this->plan_id)) {
            return $this->remaining_classes !== null && $this->attendances()->count() >= $this->remaining_classes;
        }

        return $this->end_date ? Carbon::parse($this->end_date)->isPast() : false;
    }

    //cuantos dias quedan de subscripcion con fecha
    public function getRemainingDaysAttribute()
    {
        if (is_null($this->end_date)) {
            return null;
        }

        return Carbon::parse($this->end_date)->diffInDays();
    }



}

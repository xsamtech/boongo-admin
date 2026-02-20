<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * ONE-TO-MANY
     * One type for several notifications
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several notifications
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One from_user for several notifications
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function from_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /**
     * ONE-TO-MANY
     * One to_user for several notifications
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function to_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * ONE-TO-MANY
     * One work for several notifications
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /**
     * ONE-TO-MANY
     * One like for several notifications
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function like(): BelongsTo
    {
        return $this->belongsTo(Like::class);
    }

    /**
     * ONE-TO-MANY
     * One event for several notifications
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * ONE-TO-MANY
     * One circle for several notifications
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class);
    }

    /**
     * MANY-TO-ONE
     * Several read_notification for a user
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function read_notification(): HasOne
    {
        return $this->hasOne(ReadNotification::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Like extends Model
{
    use HasFactory;

    protected $table = 'likes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * ONE-TO-MANY
     * One user for several likes
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ONE-TO-MANY
     * One work for several likes
     */
    public function for_work(): BelongsTo
    {
        return $this->belongsTo(Work::class, 'for_work_id');
    }

    /**
     * ONE-TO-MANY
     * One message for several likes
     */
    public function for_message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'for_message_id');
    }

    /**
     * MANY-TO-ONE
     * Several notifications for an event
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }
}

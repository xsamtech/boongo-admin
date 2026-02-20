<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * MANY-TO-MANY
     * Several users for several messages
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->orderByPivot('created_at', 'desc')->withTimestamps()->withPivot('status_id');
    }

    /**
     * ONE-TO-MANY
     * One type for several messages
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several messages
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One user for several messages
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * ONE-TO-MANY
     * One addressee_user for several messages
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function addressee_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'addressee_user_id');
    }

    /**
     * ONE-TO-MANY
     * One addressee_organization for several messages
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function addressee_organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'addressee_organization_id');
    }

    /**
     * ONE-TO-MANY
     * One addressee_circle for several messages
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function addressee_circle(): BelongsTo
    {
        return $this->belongsTo(Circle::class, 'addressee_circle_id');
    }

    /**
     * ONE-TO-MANY
     * One event for several messages
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * MANY-TO-ONE
     * Several likes for a message
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class, 'for_message_id');
    }

    /**
     * MANY-TO-ONE
     * Several files for a message
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }
}

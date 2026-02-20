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
class Organization extends Model
{
    use HasFactory;

    protected $table = 'organizations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * MANY-TO-MANY
     * Several users for several organizations
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * ONE-TO-MANY
     * One type for several organizations
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * ONE-TO-MANY
     * One status for several organizations
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * ONE-TO-MANY
     * One user_owner for several organizations
     */
    public function user_owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * MANY-TO-ONE
     * Several messages for an organization
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'addressee_organization_id');
    }

    /**
     * MANY-TO-ONE
     * Several programs for an organization
     */
    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    /**
     * MANY-TO-ONE
     * Several events for an organization
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * MANY-TO-ONE
     * Several works for an organization
     */
    public function works(): HasMany
    {
        return $this->hasMany(Work::class);
    }
}

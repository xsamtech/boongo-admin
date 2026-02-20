<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Session extends Model
{
    use HasFactory;

	protected $primaryKey = 'id';
	public $incrementing = false;
	protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * MANY-TO-MANY
     * Several works for several sessions
     */
    public function works(): BelongsToMany
    {
        return $this->belongsToMany(Work::class, 'work_session')->orderByPivot('created_at', 'desc')->withTimestamps()->withPivot(['read']);
    }

    /**
     * ONE-TO-MANY
     * One user for several sessions
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

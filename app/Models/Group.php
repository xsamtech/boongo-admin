<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @author Xanders
 * @see https://team.xsamtech.com/xanderssamoth
 */
class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * MANY-TO-ONE
     * Several statuses for a group
     */
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    /**
     * MANY-TO-ONE
     * Several types for a group
     */
    public function types()
    {
        return $this->hasMany(Type::class);
    }

    /**
     * MANY-TO-ONE
     * Several categories for a group
     */
    public function categories()
    {
        return $this->hasMany(Category::class);
    }
}

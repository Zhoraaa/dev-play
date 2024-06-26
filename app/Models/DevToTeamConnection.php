<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevToTeamConnection extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'developer_id',
        'team_id',
        'role'
    ];
}

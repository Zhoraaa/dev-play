<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevTeam extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'name',
        'avatar',
        'url',
        'description',
    ];
}

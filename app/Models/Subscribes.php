<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscribes extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'sub_type',
        'sub_for',
        'subscriber_id'
    ];
}

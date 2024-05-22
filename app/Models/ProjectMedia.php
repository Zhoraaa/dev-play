<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMedia extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'project_id',
        'author_id',
        'file_name',
        'snapshot_id',
        'for_download',
        'created_at',
    ];
}

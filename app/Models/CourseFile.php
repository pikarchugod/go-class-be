<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Chapter;

class CourseFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'chapter_id',
        'file_type',
        'file_url',
    ];

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}

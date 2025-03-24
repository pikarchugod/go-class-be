<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;
use App\Models\CourseFile;
use App\Models\ProgressTrack;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'video_url',
        'sort_order',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // 章節的附件
    public function courseFiles()
    {
        return $this->hasMany(CourseFile::class);
    }

    // 進度追蹤
    public function progressTracks()
    {
        return $this->hasMany(ProgressTrack::class);
    }
}

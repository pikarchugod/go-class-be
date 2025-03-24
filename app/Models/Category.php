<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Course;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'parent_id'];

    // 分類可有父、子階層
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // 該分類下的課程
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}

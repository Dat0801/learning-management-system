<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_question_id',
        'user_id',
        'content',
    ];

    public function question()
    {
        return $this->belongsTo(LessonQuestion::class, 'lesson_question_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

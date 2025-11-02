<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // Allow mass assignment for title and content
    protected $fillable = ['title', 'content', 'user_id', 'category', 'excerpt', 'read_time', 'tags', 'views'];

    // Optionally hide sensitive fields from JSON (none by default here)
    protected $hidden = [];

    // Cast attributes
    protected $casts = [
        'read_time' => 'integer',
        'views' => 'integer',
    ];
}
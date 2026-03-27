<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// #[Fillable(['title', 'slug', 'content', 'category_id', 'description', 'posted', 'image','user_id'])]
// #[With(['category'])]
class Post extends Model
{
    use HasFactory; 

    protected $fillable = ['title', 'slug', 'content', 'category_id', 'description', 'posted', 'image','user_id'];



}

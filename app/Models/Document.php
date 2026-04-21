<?php

namespace App\Models;

use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use HasFactory;

    protected $fillable = ['title', 'content', 'embedding'];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
        ];
    }
}

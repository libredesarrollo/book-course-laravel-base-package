<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// #[Fillable(['title', 'slug', 'content', 'category_id', 'description', 'posted', 'image','user_id'])]
// #[With(['category'])]
class Post extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'content', 'category_id', 'description', 'posted', 'image', 'user_id'];

    /**
     * Valores con los que la columna `posted` marca un post como publicado.
     *
     * @var array<int, int|string>
     */
    protected const PUBLISHED_VALUES = [1, '1', 'true', 'yes', 'si'];

    /**
     * La columna `posted` es un string, no un booleano: guarda 'yes', 'not', '1', etc.
     *
     * @param  Builder<Post>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereIn('posted', self::PUBLISHED_VALUES);
    }

    public function isPublished(): bool
    {
        return in_array((string) $this->posted, ['1', 'true', 'yes', 'si'], true);
    }
}

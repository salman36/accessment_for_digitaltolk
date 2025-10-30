<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Translation extends Model
{
    use HasFactory;
    protected $fillable = [
        'key',
        'locale',
        'value',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'translation_tag');
    }
}

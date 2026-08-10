<?php

namespace App\Models;

use App\Enums\ContentLanguage;
use Illuminate\Database\Eloquent\Model;

class HashtagSet extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'language' => ContentLanguage::class,
        ];
    }
}

<?php

namespace App\Models;

use App\Enums\ContentLanguage;
use App\Enums\SavedReplyCategory;
use Illuminate\Database\Eloquent\Model;

class SavedReply extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'category' => SavedReplyCategory::class,
            'language' => ContentLanguage::class,
        ];
    }
}

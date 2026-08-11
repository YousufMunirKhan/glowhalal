<?php

namespace App\Filament\Resources\BlogPosts\Schemas;

use App\Enums\PostStatus;
use App\Models\BlogCategory;
use App\Models\User;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BlogPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // A real dropdown, not a raw ID box. Defaults to the standing
                // "Herbal Care" category so a new post is filed automatically.
                Select::make('blog_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => BlogCategory::where('slug', 'herbal-care')->value('id')),

                // Defaults to the standing byline so every post is "published by"
                // the same name unless the editor picks someone else.
                Select::make('author_id')
                    ->label('Author (published by)')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload()
                    ->default(fn () => User::where('email', 'editorial@glowhalal.com')->value('id')),

                TextInput::make('title')
                    ->required()
                    ->maxLength(220)
                    ->columnSpanFull(),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(240)
                    ->helperText('The URL for this post. Keep it short and keyword-rich; do not reuse another post’s slug.'),
                Textarea::make('excerpt')
                    ->rows(2)
                    ->maxLength(500)
                    ->helperText('The summary shown on cards and used as the search-result description (aim for 150–160 characters).')
                    ->columnSpanFull(),

                // WYSIWYG. Stores HTML; the default toolbar includes headings,
                // lists, links and tables — everything the articles use.
                RichEditor::make('content')
                    ->label('Content')
                    ->columnSpanFull(),

                FileUpload::make('cover_image_path')
                    ->label('Cover image')
                    ->image()
                    ->disk('public')
                    ->directory('blog')
                    ->visibility('public'),
                // This is the image's alt text (a caption for accessibility &
                // SEO) — a text field, not an upload.
                TextInput::make('cover_image_alt')
                    ->label('Cover image alt text')
                    ->maxLength(255)
                    ->helperText('Describe the cover image in a few words, for screen readers and search engines.'),

                Select::make('status')
                    ->options(PostStatus::class)
                    ->default('draft')
                    ->required(),
                DateTimePicker::make('published_at')
                    ->label('Published at (auto-publish)')
                    ->helperText('AUTO-PUBLISH: set a FUTURE date & time with status "Published" and the post '
                        .'goes live by itself at that exact moment — its AI cover image is generated right '
                        .'then (Gemini first, free fallback; see Settings → Blog & AI Images). '
                        .'A past date publishes immediately.'),
                TextInput::make('reading_time_minutes')
                    ->label('Reading time (minutes)')
                    ->numeric()
                    ->helperText('Optional — left blank, it is estimated from the word count.'),
                Toggle::make('is_featured')
                    ->label('Featured'),
                Toggle::make('allow_comments')
                    ->label('Allow comments'),
            ]);
    }
}

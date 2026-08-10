<?php

namespace App\Filament\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;

/**
 * Shared components for the polymorphic `seo_metas` record (§7.3).
 * Used inside `Section::make()->relationship('seoMeta')` on any HasSeoMeta model.
 */
class SeoMetaSchema
{
    public static function components(): array
    {
        return [
            TextInput::make('meta_title')
                ->maxLength(255)
                ->helperText('Aim for 50–60 characters. Leave blank to fall back to the record title.'),

            Textarea::make('meta_description')
                ->rows(3)
                ->maxLength(320)
                ->helperText('Aim for 150–160 characters. This is the snippet in search results.'),

            TextInput::make('canonical_url')
                ->url()
                ->maxLength(512)
                ->helperText('Only set this when this page deliberately defers to another URL.'),

            Grid::make(2)->schema([
                TextInput::make('og_title')->label('Social title')->maxLength(255),
                Select::make('og_type')
                    ->label('Open Graph type')
                    ->options([
                        'website' => 'Website',
                        'article' => 'Article',
                        'product' => 'Product',
                    ])
                    ->native(false),
            ]),

            Textarea::make('og_description')->label('Social description')->rows(2)->maxLength(320),

            FileUpload::make('og_image_path')
                ->label('Social share image')
                ->image()
                ->imageEditor()
                ->imageEditorAspectRatioOptions(['16:9'])
                ->disk('public')
                ->directory('seo')
                ->visibility('public')
                ->maxSize(4096)
                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->helperText('1200×630 renders best across platforms.'),

            Select::make('twitter_card')
                ->options([
                    'summary' => 'Summary',
                    'summary_large_image' => 'Summary with large image',
                ])
                ->native(false),

            Grid::make(2)->schema([
                Toggle::make('is_indexable')
                    ->default(true)
                    ->helperText('Off writes noindex — the page disappears from search.'),
                Toggle::make('is_followable')->default(true),
            ]),
        ];
    }
}

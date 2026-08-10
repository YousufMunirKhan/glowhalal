<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\PageTemplate;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                RichEditor::make('content')
                    ->label('Content')
                    ->columnSpanFull(),
                Select::make('template')
                    ->options(PageTemplate::class)
                    ->default('default')
                    ->required(),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ])
                    ->default('draft')
                    ->required(),
                DateTimePicker::make('published_at'),
                Toggle::make('is_system')
                    ->required(),
                Toggle::make('show_in_footer')
                    ->required(),
                Toggle::make('show_in_header')
                    ->required(),
                TextInput::make('position')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }
}

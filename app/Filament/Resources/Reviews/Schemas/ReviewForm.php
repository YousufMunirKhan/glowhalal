<?php

namespace App\Filament\Resources\Reviews\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->label('Product')
                    ->disabled()
                    ->dehydrated(),

                Grid::make(2)->schema([
                    TextInput::make('author_name')
                        ->required()
                        ->maxLength(120),

                    Select::make('rating')
                        ->options([1 => '1 ★', 2 => '2 ★', 3 => '3 ★', 4 => '4 ★', 5 => '5 ★'])
                        ->required(),
                ]),

                TextInput::make('title')
                    ->maxLength(200),

                Textarea::make('body')
                    ->rows(4)
                    ->maxLength(2000)
                    ->columnSpanFull(),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'spam' => 'Spam',
                    ])
                    ->required()
                    ->helperText('Only "Approved" reviews show on the website.'),
            ]);
    }
}

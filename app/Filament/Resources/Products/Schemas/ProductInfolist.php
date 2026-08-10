<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Product;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            // The infolist root defaults to two columns on a ViewRecord page,
            // which halved this page and left the Commercial card ~127px wide
            // at a 1280px viewport. One column at the root; each block below
            // chooses its own split.
            ->columns(1)
            ->components([
                Grid::make(['default' => 1, 'xl' => 3])->schema([

                    Section::make('Product')->columnSpan(['xl' => 2])->schema([
                        // hiddenLabel(), not label(''): an empty string still
                        // renders the attribute name ("Path") as the label.
                        // Hidden entirely when the product has no image yet.
                        ImageEntry::make('primaryImage.path')
                            ->hiddenLabel()
                            ->disk('public')
                            ->height(160)
                            ->visible(fn (Product $record) => filled($record->primaryImage?->path)),
                        TextEntry::make('name')->size(TextSize::Large)->weight(FontWeight::Bold),
                        Grid::make(['default' => 1, 'md' => 2])->schema([
                            TextEntry::make('slug')->copyable()
                                ->extraAttributes(['style' => \App\Filament\Support\Identifier::style()]),
                            TextEntry::make('brand')->placeholder('—'),
                        ]),
                        TextEntry::make('short_description')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('description')->html()->placeholder('—')->columnSpanFull(),
                        TextEntry::make('how_to_use')->label('How to use')->html()->placeholder('—')->columnSpanFull(),
                    ]),

                    Section::make('Commercial')->columnSpan(['xl' => 1])->schema([
                        // Pairs up below xl so this is not a one-item-per-row
                        // ribbon when it sits full width under the Product card.
                        Grid::make(['default' => 2, 'xl' => 1])->schema([
                            TextEntry::make('status')->badge(),
                            TextEntry::make('price_range')
                                ->label('Price')
                                ->state(fn (Product $record) => $record->price_range),
                            TextEntry::make('total_stock')->label('Total stock')->badge(),
                            TextEntry::make('primaryCategory.name')->label('Primary category')->placeholder('—'),
                            TextEntry::make('published_at')->dateTime('d M Y H:i')->placeholder('Not published'),
                            IconEntry::make('is_featured')->boolean(),
                            IconEntry::make('is_new_arrival')->label('New arrival')->boolean(),
                        ]),
                    ]),
                ]),

                Section::make('Halal position')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(['default' => 1, 'md' => 2, 'xl' => 3])->schema([
                            TextEntry::make('halalProfile.overall_status')->label('Overall status')->badge()->placeholder('—'),
                            TextEntry::make('halalProfile.alcohol_status')->label('Alcohol')->badge()->placeholder('—'),
                            IconEntry::make('halalProfile.is_certified')->label('Third-party certified')->boolean(),
                            IconEntry::make('halalProfile.is_self_declared')->label('Self-declared only')->boolean(),
                            IconEntry::make('halalProfile.is_wudu_friendly')->label('Wudu friendly')->boolean(),
                            IconEntry::make('halalProfile.shared_facility_warning')->label('Shared facility')->boolean(),
                        ]),
                        TextEntry::make('halalProfile.summary')->label('Customer-facing summary')->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}

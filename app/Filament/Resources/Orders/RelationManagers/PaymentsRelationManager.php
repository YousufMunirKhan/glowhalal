<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Support\Money;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payments';

    protected static ?string $recordTitleAttribute = 'reference';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->columns([
                TextColumn::make('driver')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('amount')
                    ->formatStateUsing(fn ($state) => $state instanceof Money ? $state->format() : '—'),
                TextColumn::make('reference')->placeholder('—')->copyable(),
                TextColumn::make('paid_at')->dateTime('d M Y H:i')->placeholder('—'),
                TextColumn::make('verifiedBy.name')->label('Verified by')->placeholder('—'),
            ])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}

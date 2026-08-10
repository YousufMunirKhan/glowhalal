<?php

namespace App\Filament\Resources\SocialAssets;

use App\Enums\SocialAssetSource;
use App\Enums\SocialAssetType;
use App\Filament\Resources\SocialAssets\Pages\ManageSocialAssets;
use App\Models\SocialAsset;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SocialAssetResource extends Resource
{
    protected static ?string $model = SocialAsset::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static string|UnitEnum|null $navigationGroup = 'Social';

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'customer_display_name';

    protected static ?string $navigationLabel = 'UGC / Testimonials';

    protected static ?string $modelLabel = 'customer asset';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('What came in')
                ->columns(2)
                ->schema([
                    Select::make('type')
                        ->options(SocialAssetType::class)
                        ->default(SocialAssetType::Photo->value)
                        ->required()
                        ->live(),
                    Select::make('source_channel')
                        ->label('Where from')
                        ->options(SocialAssetSource::class)
                        ->required(),
                    TextInput::make('source_contact')
                        ->label('From (handle / number / name)')
                        ->maxLength(255),
                    DateTimePicker::make('received_at')
                        ->seconds(false)
                        ->default(now()),
                    TextInput::make('customer_display_name')
                        ->label('Customer display name')
                        ->maxLength(255),
                    Select::make('order_id')
                        ->label('Linked order (optional)')
                        ->relationship('order', 'id')
                        ->searchable(),
                    FileUpload::make('file')
                        ->label('Photo / video file')
                        ->disk('public')
                        ->directory('social/ugc')
                        ->visibility('public')
                        ->columnSpanFull()
                        ->visible(fn (Get $get) => $get('type') !== SocialAssetType::Text->value),
                    Textarea::make('quote')
                        ->label('Quote / testimonial text')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Section::make('Consent (required before this can ever be used)')
                ->description('No consent + proof = not usable. This is a hard rule: no fabricated or unconsented testimonials.')
                ->columns(2)
                ->schema([
                    Toggle::make('consent')
                        ->label('Customer gave consent to use this publicly')
                        ->live(),
                    FileUpload::make('consent_proof')
                        ->label('Consent proof (screenshot)')
                        ->disk('public')
                        ->directory('social/consent')
                        ->visibility('public')
                        ->helperText('A screenshot of the customer agreeing. Required to mark this usable.')
                        ->required(fn (Get $get): bool => (bool) $get('consent')),
                    Textarea::make('consent_note')
                        ->rows(2)
                        ->columnSpanFull(),
                    Toggle::make('used')
                        ->label('Already used in a post'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer_display_name')
                    ->label('Customer')
                    ->default('—')
                    ->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('source_channel')->label('From')->badge(),
                IconColumn::make('usable')
                    ->label('Usable?')
                    ->boolean()
                    ->state(fn (SocialAsset $record): bool => $record->usable)
                    ->trueIcon(Heroicon::CheckCircle)
                    ->falseIcon(Heroicon::XCircle)
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (SocialAsset $record) => $record->usable
                        ? 'Consent + proof on file — safe to use'
                        : 'Not usable: needs consent AND a consent-proof screenshot'),
                IconColumn::make('consent')->boolean(),
                IconColumn::make('used')->boolean(),
                TextColumn::make('received_at')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(SocialAssetType::class),
                Filter::make('usable')
                    ->label('Usable only')
                    ->query(fn (Builder $q) => $q->where('consent', true)->whereNotNull('consent_proof')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSocialAssets::route('/'),
        ];
    }
}

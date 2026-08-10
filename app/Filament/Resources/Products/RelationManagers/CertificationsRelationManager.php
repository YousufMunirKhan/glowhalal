<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\CertificationStatus;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CertificationsRelationManager extends RelationManager
{
    protected static string $relationship = 'certifications';

    protected static ?string $title = 'Halal certifications';

    protected static ?string $recordTitleAttribute = 'certificate_number';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                Select::make('certification_body_id')
                    ->label('Certification body')
                    ->relationship('body', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->required(),

                TextInput::make('certificate_number')
                    ->required()
                    ->maxLength(120),
            ]),

            TextInput::make('scope')
                ->maxLength(255)
                ->columnSpanFull()
                ->helperText('What the certificate actually covers — this SKU, the facility, or the manufacturer. Conflating these is how halal marketing becomes misleading.'),

            Grid::make(3)->schema([
                DatePicker::make('issued_at')->native(false),
                DatePicker::make('expires_at')->native(false)
                    ->helperText('Drives the expiry alert.'),
                Select::make('status')
                    ->options(CertificationStatus::class)
                    ->default(CertificationStatus::Active)
                    ->native(false)
                    ->required(),
            ]),

            TextInput::make('verification_url')
                ->url()
                ->maxLength(512)
                ->columnSpanFull()
                ->helperText('Leave blank to derive it from the body\'s lookup template.'),

            FileUpload::make('document_path')
                ->label('Certificate document')
                ->disk('public')
                ->directory('certifications')
                ->visibility('public')
                ->maxSize(8192)
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                ->columnSpanFull(),

            TextInput::make('document_alt')->label('Document description')->maxLength(255),

            Toggle::make('is_publicly_visible')
                ->default(true)
                ->helperText('Some certificates carry supplier commercial terms — upload without publishing.'),

            Textarea::make('notes')
                ->rows(2)
                ->columnSpanFull()
                ->helperText('Internal only. Never rendered to the storefront.'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('certificate_number')
            ->columns([
                TextColumn::make('body.name')->label('Body')->searchable()->wrap(),
                TextColumn::make('certificate_number')->searchable()->copyable(),
                TextColumn::make('scope')->limit(40)->placeholder('—')->toggleable(),
                TextColumn::make('issued_at')->date('d M Y')->placeholder('—')->sortable(),
                TextColumn::make('expires_at')->date('d M Y')->placeholder('—')->sortable()
                    ->color(fn ($record) => $record->expires_at === null
                        ? 'gray'
                        : ($record->expires_at->isPast() ? 'danger' : ($record->expires_at->diffInDays(now(), absolute: true) <= 60 ? 'warning' : null))),
                TextColumn::make('status')->badge(),
                IconColumn::make('is_publicly_visible')->label('Public')->boolean(),
            ])
            ->defaultSort('expires_at')
            ->headerActions([
                CreateAction::make()->mutateDataUsing(function (array $data): array {
                    $data['document_disk'] ??= 'public';
                    $data['added_by_user_id'] ??= auth()->id();

                    return $data;
                }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}

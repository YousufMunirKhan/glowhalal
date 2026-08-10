<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Support\Identifier;
use App\Models\User;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/**
 * Scoped to the `customer` role so staff and admin accounts are managed
 * separately from the people who buy things.
 */
class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'customer';

    protected static ?string $slug = 'customers';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas(
            'roles',
            fn (Builder $q) => $q->where('name', 'customer')
        );
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->required()->maxLength(150),
                    TextInput::make('email')->email()->required()->maxLength(255)->unique(ignoreRecord: true),
                    TextInput::make('phone')->tel()->maxLength(30),
                    DatePicker::make('date_of_birth')->native(false),
                ]),

                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrated(fn (?string $state) => filled($state))
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText('Leave blank to keep the existing password.'),

                Grid::make(2)->schema([
                    Toggle::make('accepts_marketing'),
                    Toggle::make('is_blocked')
                        ->helperText('A blocked account cannot sign in or check out.'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),

                // Emails and phone numbers are unbreakable tokens — clipped with
                // the full value on the tooltip and the copy button, never
                // hyphenated through the middle.
                TextColumn::make('email')->searchable()->copyable()
                    ->tooltip(fn ($record) => $record->email)
                    ->extraAttributes(['style' => Identifier::style()]),

                TextColumn::make('phone')->placeholder('—')->searchable()
                    ->extraAttributes(['style' => Identifier::style()]),

                TextColumn::make('orders_count')->label('Orders')->counts('orders')->badge()->color('gray'),

                // Secondary signals — dropped below 1536px so the columns that
                // matter are not pushed off a horizontally-scrolling table.
                IconColumn::make('accepts_marketing')->label('Marketing')->boolean()
                    ->visibleFrom('2xl'),

                IconColumn::make('is_blocked')->label('Blocked')->boolean()
                    ->trueColor('danger')->falseColor('gray'),

                TextColumn::make('last_login_at')->label('Last seen')->since()->placeholder('Never')
                    ->visibleFrom('2xl'),

                TextColumn::make('created_at')->label('Joined')->date('d M Y')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_blocked'),
                TernaryFilter::make('accepts_marketing'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ActionGroup::make([EditAction::make(), DeleteAction::make()]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}

<?php

namespace App\Filament\Resources\SocialPosts\Schemas;

use App\Enums\ContentLanguage;
use App\Enums\SocialCtaType;
use App\Enums\SocialMediaType;
use App\Enums\SocialPillar;
use App\Enums\SocialPlatform;
use App\Enums\SocialPostStatus;
use App\Enums\SocialTargetStatus;
use App\Models\HashtagSet;
use App\Models\SocialAsset;
use App\Settings\SocialSettings;
use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SocialPostForm
{
    /**
     * The four honesty checks. Every one must be ticked before a post can leave
     * Draft. These are enforced (not just displayed) by the validation rule on
     * the `status` field further down.
     */
    public const COMPLIANCE_ITEMS = [
        'no_medical_claim' => 'No medical cure, treatment or disease claim.',
        'no_halal_certified' => 'No "halal certified" claim unless we hold a real certificate.',
        'honest_reviews' => 'No fake reviews or testimonials — real, consented customers only.',
        'accurate_details' => 'Price, discount, COD and WhatsApp details are correct and honest.',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('The post')
                    ->description('Plan one post here; below you choose which platforms it goes to. Publishing stays manual in Phase 0.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Internal title')
                            ->helperText('Just for you — never shown publicly.')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('pillar')
                            ->label('Content pillar')
                            ->options(SocialPillar::class),

                        Select::make('language')
                            ->options(ContentLanguage::class)
                            ->default(ContentLanguage::RomanUrdu->value)
                            ->required(),

                        Textarea::make('caption_base')
                            ->label('Base caption')
                            ->rows(5)
                            ->helperText('The default caption. You can override it per platform below.')
                            ->columnSpanFull(),

                        Select::make('hashtag_set_picker')
                            ->label('Add a hashtag set')
                            ->helperText('Pick a saved set to append its tags to the list on the right.')
                            ->options(fn () => HashtagSet::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->dehydrated(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set) {
                                if (blank($state)) {
                                    return;
                                }
                                $chosen = HashtagSet::find($state);
                                if (! $chosen) {
                                    return;
                                }
                                $existing = $get('hashtags') ?? [];
                                $set('hashtags', array_values(array_unique(array_merge($existing, $chosen->tags ?? []))));
                                $set('hashtag_set_picker', null);
                            }),

                        TagsInput::make('hashtags')
                            ->helperText('Type a tag and press Enter. The # is optional.')
                            ->default(fn () => app(SocialSettings::class)->default_hashtags ?? []),

                        TextInput::make('link_url')
                            ->label('Link (optional)')
                            ->url()
                            ->maxLength(255),

                        Select::make('cta_type')
                            ->label('Call to action')
                            ->options(SocialCtaType::class)
                            ->default(SocialCtaType::None->value),
                    ]),

                Section::make('Media')
                    ->description('Images or videos for the post. Alt text is required so the post stays accessible.')
                    ->schema([
                        Repeater::make('media')
                            ->relationship()
                            ->label('Attachments')
                            ->orderColumn('position')
                            ->addActionLabel('Add media')
                            ->defaultItems(0)
                            ->columns(2)
                            ->schema([
                                FileUpload::make('path')
                                    ->label('File')
                                    ->disk('public')
                                    ->directory('social')
                                    ->visibility('public')
                                    ->columnSpanFull(),
                                Select::make('type')
                                    ->options(SocialMediaType::class)
                                    ->default(SocialMediaType::Image->value)
                                    ->required(),
                                TextInput::make('alt_text')
                                    ->label('Alt text (required)')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Describe the image for screen readers.'),
                            ]),
                    ]),

                Section::make('Platforms')
                    ->description('Which platforms this post goes to. Each becomes a manual to-do you tick off after posting.')
                    ->schema([
                        Repeater::make('targets')
                            ->relationship()
                            ->label('Target platforms')
                            ->addActionLabel('Add platform')
                            ->defaultItems(0)
                            ->columns(2)
                            ->schema([
                                Select::make('platform')
                                    ->options(SocialPlatform::class)
                                    ->required(),
                                Select::make('status')
                                    ->options(SocialTargetStatus::class)
                                    ->default(SocialTargetStatus::Pending->value)
                                    ->required(),
                                Textarea::make('caption_override')
                                    ->label('Caption override (optional)')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->helperText('Leave blank to use the base caption above.'),
                            ]),
                    ]),

                Section::make('Schedule')
                    ->schema([
                        DateTimePicker::make('scheduled_at')
                            ->label('Scheduled for (PKT)')
                            ->seconds(false)
                            ->helperText('When you plan to publish. Shown on the calendar and used by the daily reminder.'),
                    ]),

                Section::make('Honesty & compliance')
                    ->description('These honesty rules apply to every post, for every product. They are enforced, not optional.')
                    ->columns(2)
                    ->schema([
                        Toggle::make('contains_sensitive_content')
                            ->label('Health or safety-sensitive post')
                            ->helperText('Leave OFF for most posts. Turn ON only if a post makes a health, skin or safety point (any product) — then a short safety note is required, and never tell people to apply a product to a wound.')
                            ->live(),

                        Toggle::make('uses_ugc')
                            ->label('Uses customer content (UGC / testimonial)')
                            ->helperText('If on, you must attach a consented asset below.')
                            ->live(),

                        Textarea::make('safety_note')
                            ->label('Safety note')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(fn (Get $get): bool => (bool) $get('contains_sensitive_content'))
                            ->visible(fn (Get $get): bool => (bool) $get('contains_sensitive_content')),

                        Select::make('social_asset_id')
                            ->label('Consented customer asset')
                            ->relationship('asset', 'customer_display_name')
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn (SocialAsset $r) => trim(($r->customer_display_name ?: 'Asset #'.$r->id).($r->usable ? '' : '  — NO CONSENT')))
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => (bool) $get('uses_ugc'))
                            ->required(fn (Get $get): bool => (bool) $get('uses_ugc'))
                            ->rules([
                                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    if (! $get('uses_ugc') || blank($value)) {
                                        return;
                                    }
                                    $asset = SocialAsset::find($value);
                                    if (! $asset || ! $asset->usable) {
                                        $fail('That customer asset has no recorded consent (needs consent + a consent-proof screenshot). It cannot be used.');
                                    }
                                },
                            ]),

                        CheckboxList::make('compliance_items')
                            ->label('Honesty checklist — tick every box to unlock scheduling')
                            ->options(self::COMPLIANCE_ITEMS)
                            ->dehydrated(false)
                            ->live()
                            ->columnSpanFull()
                            ->afterStateHydrated(function (CheckboxList $component, Get $get) {
                                // On edit, pre-tick everything if the record was already cleared.
                                if ($get('compliance_checked')) {
                                    $component->state(array_keys(self::COMPLIANCE_ITEMS));
                                }
                            })
                            ->afterStateUpdated(function ($state, Set $set) {
                                $set('compliance_checked', count($state ?? []) === count(self::COMPLIANCE_ITEMS));
                            }),

                        Toggle::make('compliance_checked')
                            ->label('Compliance checked')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Turns on automatically once every box above is ticked.'),

                        Select::make('status')
                            ->options(SocialPostStatus::class)
                            ->default(SocialPostStatus::Draft->value)
                            ->required()
                            ->live()
                            ->helperText('Can only leave Draft once the honesty checklist is complete.')
                            ->rules([
                                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    $status = $value instanceof SocialPostStatus ? $value : SocialPostStatus::tryFrom((string) $value);
                                    if ($status && $status->requiresCompliance() && ! $get('compliance_checked')) {
                                        $fail('Complete the honesty checklist first. Only "Draft" is allowed until every box is ticked.');
                                    }
                                },
                            ]),
                    ]),
            ]);
    }
}

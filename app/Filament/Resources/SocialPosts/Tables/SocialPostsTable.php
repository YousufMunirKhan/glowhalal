<?php

namespace App\Filament\Resources\SocialPosts\Tables;

use App\Enums\SocialPostStatus;
use App\Enums\SocialTargetStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SocialPostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn ($record) => str($record->caption_base ?? '')->limit(60)),

                TextColumn::make('status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('targets.platform')
                    ->label('Platforms')
                    ->badge()
                    ->separator(),

                TextColumn::make('targets.status')
                    ->label('Progress')
                    ->badge()
                    ->separator(),

                TextColumn::make('scheduled_at')
                    ->label('Scheduled (PKT)')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('caption_base')
                    ->label('Caption')
                    ->limit(40)
                    ->copyable()
                    ->copyMessage('Caption copied — paste it into the app')
                    ->toggleable(),

                TextColumn::make('hashtags')
                    ->label('Hashtags')
                    ->state(fn ($record) => collect($record->hashtags ?? [])
                        ->map(fn ($t) => str_starts_with($t, '#') ? $t : '#'.$t)
                        ->implode(' '))
                    ->limit(30)
                    ->copyable()
                    ->copyMessage('Hashtags copied')
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('scheduled_at', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->options(SocialPostStatus::class),
            ])
            ->recordActions([
                // Manual publishing helper: copy the caption / hashtags and jump
                // to each platform's website to post by hand. No API, no auto-post.
                Action::make('publishHelper')
                    ->label('Copy / Open app')
                    ->icon(Heroicon::OutlinedClipboardDocument)
                    ->color('gray')
                    ->modalHeading(fn ($record) => 'Post manually: '.$record->title)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn ($record) => view('filament.social.publish-helper', ['record' => $record])),

                // Record that a platform was posted by hand.
                Action::make('markPosted')
                    ->label('Mark as posted')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn ($record) => $record->targets()->exists())
                    ->schema([
                        Select::make('target_id')
                            ->label('Which platform did you post to?')
                            ->options(fn ($record) => $record->targets
                                ->mapWithKeys(fn ($t) => [$t->id => $t->platform->getLabel().' — '.$t->status->getLabel()]))
                            ->required(),
                        TextInput::make('external_url')
                            ->label('Live post URL (optional)')
                            ->url(),
                    ])
                    ->action(function (array $data, $record) {
                        $target = $record->targets()->find($data['target_id']);
                        if (! $target) {
                            return;
                        }
                        $target->update([
                            'status' => SocialTargetStatus::PostedManually,
                            'posted_at' => now(),
                            'external_url' => $data['external_url'] ?? null,
                        ]);

                        // When every platform is done, flip the whole post to Posted.
                        $allDone = $record->targets()
                            ->where('status', '!=', SocialTargetStatus::PostedManually->value)
                            ->doesntExist();
                        if ($allDone) {
                            $record->update([
                                'status' => SocialPostStatus::Posted,
                                'published_at' => $record->published_at ?? now(),
                            ]);
                        }

                        Notification::make()
                            ->title('Marked as posted on '.$target->platform->getLabel())
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

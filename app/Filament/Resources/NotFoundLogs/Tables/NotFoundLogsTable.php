<?php

namespace App\Filament\Resources\NotFoundLogs\Tables;

use App\Models\NotFoundLog;
use App\Models\Redirect;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NotFoundLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('hits', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('resolvedRedirect'))
            ->columns([
                TextColumn::make('path')->label('المسار')->searchable()->limit(60)->copyable(),
                TextColumn::make('hits')->label('مرات الظهور')->numeric()->sortable(),
                IconColumn::make('is_bot')->label('روبوت')->boolean(),
                TextColumn::make('last_referrer')->label('آخر مصدر')->limit(40)->placeholder('—'),
                TextColumn::make('last_seen_at')->label('آخر مرة')->since()->sortable(),
                TextColumn::make('resolvedRedirect.to_url')->label('اتحوّل إلى')->placeholder('—'),
            ])
            ->filters([
                TernaryFilter::make('is_bot')->label('روبوت'),
                TernaryFilter::make('resolved')
                    ->label('اتحلّ؟')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('resolved_redirect_id'),
                        false: fn (Builder $q) => $q->whereNull('resolved_redirect_id'),
                    ),
            ])
            ->recordActions([
                Action::make('redirect')
                    ->label('اعمل تحويل')
                    ->icon('heroicon-o-arrows-right-left')
                    ->visible(fn (NotFoundLog $record) => $record->resolved_redirect_id === null)
                    ->schema([
                        TextInput::make('to_url')
                            ->label('حوّل إلى')
                            ->helperText('مسار داخلي (مثل /services) أو رابط كامل.')
                            ->required()
                            ->maxLength(250)
                            ->extraInputAttributes(['dir' => 'ltr']),
                    ])
                    ->action(function (NotFoundLog $record, array $data): void {
                        $redirect = Redirect::query()->updateOrCreate(
                            ['from_path' => Redirect::normalizePath($record->path)],
                            ['to_url' => $data['to_url'], 'status_code' => 301, 'source' => '404-suggestion', 'is_active' => true],
                        );
                        $record->update(['resolved_redirect_id' => $redirect->id]);
                        Notification::make()->title('اتعمل التحويل')->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

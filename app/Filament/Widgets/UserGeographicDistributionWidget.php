<?php

namespace App\Filament\Widgets;

use App\Models\UserSession;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class UserGeographicDistributionWidget extends TableWidget
{
    protected static ?string $heading = 'User Activity by Location';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => UserSession::query()
                    ->select(
                        'ip_address',
                        'device_type',
                        DB::raw('COUNT(DISTINCT user_id) as user_count'),
                        DB::raw('COUNT(*) as session_count'),
                        DB::raw('AVG(duration) as avg_duration')
                    )
                    ->whereNotNull('ip_address')
                    ->where('started_at', '>=', now()->subDays(30))
                    ->groupBy('ip_address', 'device_type')
                    ->orderBy('user_count', 'desc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('device_type')
                    ->label('Device Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'mobile' => 'success',
                        'desktop' => 'primary',
                        'tablet' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('user_count')
                    ->label('Unique Users')
                    ->sortable(),

                TextColumn::make('session_count')
                    ->label('Total Sessions')
                    ->sortable(),

                TextColumn::make('avg_duration')
                    ->label('Avg Session Duration')
                    ->formatStateUsing(fn ($state) => gmdate('i:s', (int) $state))
                    ->sortable(),
            ])
            ->defaultSort('user_count', 'desc');
    }

    public function getTableRecordKey($record): string
    {
        return md5($record->ip_address.'-'.$record->device_type);
    }
}

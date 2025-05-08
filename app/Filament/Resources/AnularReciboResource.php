<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnularReciboResource\Pages;
use App\Filament\Resources\AnularReciboResource\RelationManagers;
use App\Models\AnularRecibo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Recibo;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class AnularReciboResource extends Resource
{
    protected static ?string $model = Recibo::class;
    protected static ?string $navigationIcon = 'heroicon-o-x-circle';
    protected static ?string $navigationLabel = 'Anular Recibos';
    protected static ?string $navigationGroup = 'Gestión de Pagos';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Recibo a Anular';
    protected static ?string $slug = 'anular-recibos';
    protected static bool $shouldRegisterNavigation = false;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_recibo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prestamo.numero_prestamo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_recibo')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'CN' => 'Normal',
                        'CA' => 'Anticipado',
                        'LI' => 'Liquidación',
                        default => $state
                    }),
                Tables\Columns\TextColumn::make('monto_recibo')
                    ->money(fn($record) => $record->prestamo->moneda ?? 'CRC')
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_pago')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'I' => 'Incluido',
                        'C' => 'Completado',
                        'A' => 'Anulado',
                        default => $state
                    })
                    ->color(fn($state) => match ($state) {
                        'I' => 'info',
                        'C' => 'success',
                        'A' => 'danger',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('es_anulable')
                    ->label('¿Anulable?')
                    ->formatStateUsing(function ($state, $record) {
                        $masReciente = Recibo::whereIn('estado', ['I', 'C'])
                            ->orderBy('created_at', 'desc')
                            ->first();
                        return $masReciente && $masReciente->id === $record->id ? 'Sí' : 'No';
                    })
                    ->color(function ($state, $record) {
                        $masReciente = Recibo::whereIn('estado', ['I', 'C'])
                            ->orderBy('created_at', 'desc')
                            ->first();
                        return $masReciente && $masReciente->id === $record->id ? 'success' : 'danger';
                    }),
            ])
            ->modifyQueryUsing(
                fn(Builder $query) =>
                $query->whereIn('estado', ['I', 'C'])
                    ->orderBy('created_at', 'desc')
            )
            ->actions([
                Tables\Actions\Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(function ($record) {
                        $masReciente = Recibo::whereIn('estado', ['I', 'C'])
                            ->orderBy('created_at', 'desc')
                            ->first();
                        return $masReciente && $masReciente->id === $record->id;
                    })
                    ->action(function (Recibo $recibo) {
                        try {
                            DB::transaction(function () use ($recibo) {
                                $recibo->anular();

                                Notification::make()
                                    ->title('Recibo anulado correctamente')
                                    ->success()
                                    ->send();
                            });
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al anular recibo')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Anular Recibo')
                    ->modalDescription('¿Está seguro de anular este recibo? Esta acción revertirá los cambios aplicados.')
                    ->modalSubmitActionLabel('Sí, anular')
            ])
            ->bulkActions([])
            ->emptyStateActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnularRecibos::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
}

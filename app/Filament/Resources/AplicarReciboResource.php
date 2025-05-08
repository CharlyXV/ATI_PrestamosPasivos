<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AplicarReciboResource\Pages;
use App\Filament\Resources\AplicarReciboResource\RelationManagers;
use App\Models\AplicarRecibo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\AnularRecibo;
use App\Models\Recibo;
use Filament\Notifications\Notification;


class AplicarReciboResource extends Resource
{
    protected static ?string $model = Recibo::class;
    protected static ?string $navigationIcon = 'heroicon-o-check-circle';
    protected static ?string $navigationLabel = 'Aplicar Recibos';
    protected static ?string $navigationGroup = 'Gestión de Pagos';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Aplicar Recibo';
    protected static ?string $slug = 'aplicar-recibos'; // Define una slug única
    protected static bool $shouldRegisterNavigation = false; // Oculta del menú automático
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
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', 'I'))
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_recibo')
                    ->options([
                        'CN' => 'Normal',
                        'CA' => 'Anticipado',
                        'LI' => 'Liquidación'
                    ])
            ])
            ->actions([
                Tables\Actions\Action::make('aplicar')
                    ->label('Aplicar Recibo')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->estado === 'I')
                    ->action(function (Recibo $recibo) {
                        try {
                            $recibo->procesarPago();
                            Notification::make()
                                ->title('Recibo aplicado correctamente')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al aplicar recibo')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Aplicar Recibo')
                    ->modalDescription('¿Está seguro de aplicar este recibo? Los montos se marcarán como pagados.')
                    ->modalSubmitActionLabel('Sí, aplicar')
            ])
            ->bulkActions([])
            ->emptyStateActions([]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAplicarRecibos::route('/'),
        ];
    }
}

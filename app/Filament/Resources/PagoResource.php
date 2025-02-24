<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PagoResource\Pages;
use App\Models\Pago;
use App\Models\Planpago;
use App\Models\Prestamo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PagoResource extends Resource
{
    protected static ?string $model = Pago::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Gestión Financiera';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Campo para seleccionar el préstamo
                Forms\Components\Select::make('prestamo_id')
                    ->relationship('prestamos', 'numero_prestamo')
                    ->label('Préstamo')
                    ->required()
                    ->live(),
    
                // Campo para seleccionar la cuota (reactivo al préstamo seleccionado)
                Forms\Components\Select::make('planpago_id')
                    ->relationship('planpagos', 'numero_cuota')
                    ->label('Cuota')
                    ->required()
                    ->options(function (Forms\Get $get) {
                        $prestamoId = $get('prestamo_id');
                        if (!$prestamoId) {
                            return [];
                        }
                        return Planpago::where('prestamo_id', $prestamoId)
                            ->pluck('numero_cuota', 'id');
                    })
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        $planpago = Planpago::find($state);
                        if ($planpago) {
                            $set('monto', $planpago->monto_principal + $planpago->monto_interes);
                            $fechaPago = $planpago->fecha_pago;
                            if ($fechaPago instanceof \Carbon\Carbon) {
                                $fechaPago = $fechaPago->format('Y-m-d'); // Solo fecha, sin hora
                            }
                            $set('fecha_pago', $fechaPago); // Actualiza el campo en el formulario
                        } else {
                            $set('monto', 0);
                            $set('fecha_pago', null);
                        }
                    }),
    
                    Forms\Components\TextInput::make('monto')
                    ->label('Monto del Pago')
                    ->numeric()
                    ->required()
                    ->default(0)
                    ->dehydrateStateUsing(fn ($state) => $state), // Forzar el envío del valor
    
                // Campo para la fecha de pago (autocompletado)
                Forms\Components\DatePicker::make('fecha_pago')
                    ->label('Fecha de Pago')
                    ->required()
                    ->date()
                    ->dehydrated(),
    
                // Campo para la referencia del depósito
                Forms\Components\TextInput::make('referencia')
                    ->label('Referencia del Depósito')
                    ->nullable(),
    
                // Campo para el estado del pago
                Forms\Components\Select::make('estado')
                    ->options([
                        'pendiente' => 'Pendiente',
                        'completado' => 'Completado',
                    ])
                    ->default('pendiente')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('prestamos.numero_prestamo')
                    ->label('Préstamo')
                    ->sortable(),

                Tables\Columns\TextColumn::make('planpagos.numero_cuota')
                    ->label('Cuota')
                    ->sortable(),

                Tables\Columns\TextColumn::make('monto')
                    ->label('Monto')
                    ->money('CRC' )
                    ->sortable(),

                Tables\Columns\TextColumn::make('fecha_pago')
                    ->label('Fecha de Pago')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('referencia')
                    ->label('Referencia')
                    ->searchable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pendiente' => 'warning',
                        'completado' => 'success',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPagos::route('/'),
            'create' => Pages\CreatePago::route('/create'),
            'edit' => Pages\EditPago::route('/{record}/edit'),
        ];
    }
}
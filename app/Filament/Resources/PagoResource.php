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
use Illuminate\Database\Eloquent\Model;

class PagoResource extends Resource
{
    protected static ?string $model = Pago::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Gestión Financiera';
    protected static ?int $navigationSort = 2;

    
        public static function form(Form $form): Form
        {
            return $form
                ->schema([
                    Forms\Components\Select::make('prestamo_id')
                        ->relationship('prestamo', 'numero_prestamo')
                        ->label('Préstamo')
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                            $set('planpago_id', null);
                            $set('monto', 0);
                            $set('moneda', Prestamo::find($state)?->moneda ?? 'CRC');
                        }),
            
                    Forms\Components\Select::make('planpago_id')
                        ->relationship('planpago', 'numero_cuota')
                        ->label('Cuota')
                        ->required()
                        ->options(function (Forms\Get $get) {
                            $prestamoId = $get('prestamo_id');
                            if (!$prestamoId) {
                                return [];
                            }
                            
                            return Planpago::with('prestamo')
                                ->where('prestamo_id', $prestamoId)
                                ->orderBy('numero_cuota')
                                ->pluck('numero_cuota', 'id');
                        })
                        ->live()
                        ->afterStateUpdated(function (Forms\Set $set, $state) {
                            if (!$state) {
                                $set('monto', 0);
                                $set('fecha_pago', null);
                                return;
                            }
                        
                            $planpago = Planpago::with('prestamo')->find($state);
                            
                            if ($planpago && $planpago->prestamo) {
                                $montoTotal = $planpago->monto_principal + $planpago->monto_interes;
                                $set('monto', number_format($montoTotal, 2, '.', ''));
                                $set('moneda', $planpago->prestamo->moneda);
                                
                                try {
                                    $fechaPago = $planpago->fecha_pago 
                                        ? \Carbon\Carbon::parse($planpago->fecha_pago)->format('Y-m-d')
                                        : null;
                                    $set('fecha_pago', $fechaPago);
                                } catch (\Exception $e) {
                                    $set('fecha_pago', null);
                                }
                            }
                        }),
                        
                    // Campo Monto modificado
                    Forms\Components\TextInput::make('monto')
                        ->label('Monto del Pago')
                        ->numeric()
                        ->required()
                        ->readOnly() // Cambiado de disabled() a readOnly()
                        ->default(0),
            
                    // Campo Moneda modificado
                    Forms\Components\TextInput::make('moneda')
                        ->label('Moneda')
                        ->readOnly() // Cambiado de disabled() a readOnly()
                        ->formatStateUsing(fn ($state) => match($state) {
                            'USD' => 'USD ($)',
                            'CRC' => 'CRC (₡)',
                            'EUR' => 'EUR (€)',
                            default => $state
                        })
                        ->dehydrated(),
            
                    // Campo Fecha de Pago modificado
                    Forms\Components\DatePicker::make('fecha_pago')
                        ->label('Fecha de Pago')
                        ->required()
                        ->readOnly() // Cambiado de disabled() a readOnly()
                        ->native(false)
                        ->format('Y-m-d')
                        ->displayFormat('d/m/Y'),
            
                    Forms\Components\TextInput::make('referencia')
                        ->label('Referencia del Depósito')
                        ->nullable(),
            
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
                Tables\Columns\TextColumn::make('prestamo.numero_prestamo')
                    ->label('Préstamo')
                    ->sortable(),

// Por esta versión corregida y mejorada:
Tables\Columns\TextColumn::make('planpago.numero_cuota')
    ->label('Cuota N°')
    ->formatStateUsing(function ($state, Pago $record) {
        // Carga la relación si no está cargada
        if (!$record->relationLoaded('planpago')) {
            $record->load('planpago');
        }
        
        return $record->planpago->numero_cuota ?? 'N/A';
    })
    ->sortable()
    ->searchable(),

                    Tables\Columns\TextColumn::make('monto')
                    ->label('Monto')
                    ->formatStateUsing(function ($state, Model $record) {
                        // Carga la relación si no está cargada
                        $record->loadMissing(['planpago.prestamo']);
                        
                        // Obtiene la moneda de forma segura
                        $moneda = optional($record->planpago)->prestamo->moneda ?? 'CRC';
                        
                        // Formatea el monto
                        $formatted = number_format((float)$state, 2, '.', ',');
                        
                        // Devuelve con símbolo
                        return match($moneda) {
                            'USD' => '$' . $formatted,
                            'CRC' => '₡' . $formatted,
                            'EUR' => '€' . $formatted,
                            default => $moneda . ' ' . $formatted
                        };
                    }),
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
<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Empresa;
use App\Models\Prestamo;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\PrestamosResource\Pages;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\Action;

class PrestamosResource extends Resource
{
    protected static ?string $model = Prestamo::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Gestión Financiera';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Préstamo')
                    ->description('Condiciones del Préstamo')
                    ->schema([
                        Select::make('empresa_id')
                            ->relationship('empresa', 'nombre_empresa')
                            ->label('Empresa')
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                        Forms\Components\TextInput::make('numero_prestamo')
                            ->label('Número')
                            ->required()
                            ->maxLength(255),
                            
                            Forms\Components\Select::make('banco_id')
                            ->relationship(name: 'banco', titleAttribute: 'nombre_banco')
                            ->label('Origen Fondos')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $banco = \App\Models\Banco::find($state);
                                    // Usamos cuenta_desembolsoB que es el nuevo nombre
                                    $set('cuenta_desembolso', $banco->cuenta_desembolsoB);
                                }
                            }),
                        
                        Forms\Components\TextInput::make('cuenta_desembolso')
                            ->label('Cuenta de Desembolso')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(255),
                            
                        Select::make('linea_id')
                            ->relationship('linea', 'nombre_linea')
                            ->label('Línea de Crédito')
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                        Select::make('forma_pago')
                            ->options([
                                'V' => 'Vencimiento',
                                'A' => 'Adelantado',
                            ])
                            ->required(),
                            
                        Select::make('moneda')
                            ->options([
                                'USD' => 'USD (Dólar)',
                                'CRC' => 'CRC (Colón)',
                                'EUR' => 'EUR (Euro)',
                            ])
                            ->required()
                            ->live(),
                            
                            Forms\Components\DatePicker::make('formalizacion')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                // Actualizar fechas cuando se cambia la fecha de formalización
                                $formalizacion = $get('formalizacion');
                                $plazoMeses = $get('plazo_meses');
                                $periodicidad = (int)$get('periodicidad_pago');
                                
                                if ($formalizacion) {
                                    $fechaFormalizacion = \Carbon\Carbon::parse($formalizacion);
                                    
                                    // Calcular próximo pago según periodicidad
                                    if ($periodicidad) {
                                        $mesesAgregar = 12 / $periodicidad;
                                        $proximoPago = $fechaFormalizacion->copy()->addMonths($mesesAgregar);
                                        $set('proximo_pago', $proximoPago->format('Y-m-d'));
                                    }
                                    
                                    // Calcular vencimiento según plazo
                                    if ($plazoMeses) {
                                        $vencimiento = $fechaFormalizacion->copy()->addMonths($plazoMeses);
                                        $set('vencimiento', $vencimiento->format('Y-m-d'));
                                    }
                                }
                            }),
                            
                        Forms\Components\TextInput::make('monto_prestamo')
                            ->label('Monto del Préstamo')
                            ->numeric()
                            ->required()
                            ->maxValue(99999999999.99)
                            ->step(0.01),
                            
                        Select::make('estado')
                            ->options([
                                'A' => 'Activo',
                                'L' => 'Liquidado',
                                'I' => 'Incluido',
                            ])
                            ->required(),
                    ])
                    ->columns(3)
                    ->collapsible(),
                    
                Section::make('Tasas')
                    ->description('Condiciones de Tasas')
                    ->schema([
                        Select::make('tipotasa_id')
                            ->relationship('tipotasa', 'nombre_tipo_tasa')
                            ->label('Tipo de Tasa')
                            ->searchable()
                            ->preload()
                            ->required(),
                            
                Select::make('periodicidad_pago')
                    ->options([
                        '12' => 'Mensual',
                        '6' => 'Bimestral',
                        '4' => 'Trimestral',
                        '3' => 'Cuatrimestral',
                        '2' => 'Semestral',
                        '1' => 'Anual',
])
                        ->label('Periodicidad de Pago')
                        ->required()
                        ->live() // Hacerlo live para poder reaccionar cuando cambie
    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
    // Esto se ejecutará cuando se cambie la periodicidad
    $formalizacion = $get('formalizacion');
    $plazoMeses = $get('plazo_meses');
    $periodicidad = (int)$get('periodicidad_pago');
    
    // Si tenemos fecha de formalización, actualizamos la fecha de próximo pago
    if ($formalizacion) {
        $fechaFormalizacion = \Carbon\Carbon::parse($formalizacion);
        
        // Calcular la fecha del próximo pago según la periodicidad
        $mesesAgregar = 12 / $periodicidad; // Convertir la periodicidad a meses
        $proximoPago = $fechaFormalizacion->copy()->addMonths($mesesAgregar);
        
        // Actualizar la fecha del próximo pago
        $set('proximo_pago', $proximoPago->format('Y-m-d'));
        
        // Calcular también la fecha de vencimiento basada en el plazo
        if ($plazoMeses) {
            $vencimiento = $fechaFormalizacion->copy()->addMonths($plazoMeses);
            $set('vencimiento', $vencimiento->format('Y-m-d'));
        }
    }
}),
                            
                        Forms\Components\TextInput::make('plazo_meses')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(999)
                            ->live()
    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
        $formalizacion = $get('formalizacion');
        $plazoMeses = $get('plazo_meses');
    
    if ($formalizacion && $plazoMeses) {
        $fechaFormalizacion = \Carbon\Carbon::parse($formalizacion);
        $vencimiento = $fechaFormalizacion->copy()->addMonths($plazoMeses);
        $set('vencimiento', $vencimiento->format('Y-m-d'));
    }
}),
                            
                        Forms\Components\TextInput::make('tasa_interes')
                            ->numeric()
                            ->required()
                            ->maxValue(100)
                            ->suffix('%')
                            ->step(0.01),
                            
                        Forms\Components\TextInput::make('tasa_spreed')
                            ->label('Spread de Interés')
                            ->numeric()
                            ->required()
                            ->maxValue(100)
                            ->suffix('%')
                            ->step(0.01),
                    ])
                    ->columns(3)
                    ->collapsible(),
                    
                Section::make('Detalles')
                    ->description('Desembolsos / Saldos / Estados')
                    ->schema([
                        Forms\Components\DatePicker::make('vencimiento')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                            
                        Forms\Components\DatePicker::make('proximo_pago')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                            
                        Forms\Components\TextInput::make('saldo_prestamo')
                            ->label('Saldo Actual')
                            ->numeric()
                            ->maxValue(99999999999.99)
                            ->step(0.01),
                            
                            
                            
                        Forms\Components\Textarea::make('observacion')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible(),
                    
            
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero_prestamo')
                    ->searchable()
                    ->sortable()
                    ->label('N° Préstamo'),
                    
                TextColumn::make('formalizacion')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Fecha Formalización'),
                    
                TextColumn::make('monto_prestamo')
                    ->label('Monto')
                    ->formatStateUsing(function ($state, $record) {
                        $simbolo = match($record->moneda) {
                            'CRC' => '₡',
                            'USD' => '$',
                            'EUR' => '€',
                            default => $record->moneda
                        };
                        return $simbolo . ' ' . number_format($state, 2);
                        
                    })
                    ->sortable(),
                    
                TextColumn::make('banco.nombre_banco')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                    TextColumn::make('saldo_prestamo')
                    ->formatStateUsing(function ($state, Prestamo $record) {
                        $formatted = number_format($state, 2, ',', '.');
                        return match($record->moneda) {
                            'USD' => '$' . $formatted,
                            'CRC' => '₡' . $formatted,
                            'EUR' => '€' . $formatted,
                            default => $formatted . ' ' . $record->moneda
                        };
                    })
                    ->sortable()
                    ->label('S° Préstamo'),
                    
                TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'A' => 'success',
                        'L' => 'danger',
                        'I' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'A' => 'Activo',
                        'L' => 'Liquidado',
                        'I' => 'Incluido',
                        default => $state,
                    }),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'A' => 'Activos',
                        'L' => 'Liquidados',
                        'I' => 'Incluidos',
                    ]),
                    
                SelectFilter::make('empresa_id')
                    ->relationship('empresa', 'nombre_empresa')
                    ->searchable()
                    ->preload()
                    ->label('Empresa'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    
                        
                ]),
            ])
            ->bulkActions([
                    Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrestamos::route('/'),
            'create' => Pages\CreatePrestamos::route('/create'),
            'edit' => Pages\EditPrestamos::route('/{record}/edit'),
        ];
    }

}
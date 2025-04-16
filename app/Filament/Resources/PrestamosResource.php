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
use Carbon\Carbon;
use Illuminate\Http\Request;
use Filament\Notifications\Notification;
use Filament\Forms\Get;
use Filament\Forms\Set;



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
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $set('saldo_prestamo', $state);
                            }),

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
                                '1' => 'Anual (1 pago/año)',
                                '2' => 'Semestral (2 pagos/año)',
                                '3' => 'Cuatrimestral (3 pagos/año)',
                                '4' => 'Trimestral (4 pagos/año)',
                                '6' => 'Bimestral (6 pagos/año)',
                                '12' => 'Mensual (12 pagos/año)',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                // Resetear campos dependientes
                                $set('plazo_meses', null);
                                $set('vencimiento', null);
                                $set('proximo_pago', null);
                            }),

                        Select::make('plazo_meses')
                            ->label(function (Get $get) {
                                $periodicidad = $get('periodicidad_pago');
                                return match ((int)$periodicidad) {
                                    1 => 'Plazo (en años)',
                                    2 => 'Plazo (en semestres)',
                                    3 => 'Plazo (en cuatrimestres)',
                                    4 => 'Plazo (en trimestres)',
                                    6 => 'Plazo (en bimestres)',
                                    12 => 'Plazo (en meses)',
                                    default => 'Plazo (periodos)'
                                };
                            })
                            ->options(function (Get $get) {
                                $periodicidad = (int)$get('periodicidad_pago');

                                $getPeriodoNombre = function ($cantidad, $periodicidad) {
                                    $singular = match ($periodicidad) {
                                        1 => 'año',
                                        2 => 'semestre',
                                        3 => 'cuatrimestre',
                                        4 => 'trimestre',
                                        6 => 'bimestre',
                                        12 => 'mes',
                                        default => 'periodo'
                                    };

                                    $plural = match ($periodicidad) {
                                        1 => 'años',
                                        2 => 'semestres',
                                        3 => 'cuatrimestres',
                                        4 => 'trimestres',
                                        6 => 'bimestres',
                                        12 => 'meses',
                                        default => 'periodos'
                                    };

                                    return $cantidad === 1 ? $singular : $plural;
                                };

                                $options = [];
                                for ($i = 1; $i <= 64; $i++) {
                                    $options[$i] = $i . ' ' . $getPeriodoNombre($i, $periodicidad);
                                }
                                return $options;
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $periodicidad = (int)$get('periodicidad_pago');
                                $plazoPeriodos = (int)$get('plazo_meses');
                                $formalizacion = $get('formalizacion');

                                if ($periodicidad && $plazoPeriodos && $formalizacion) {
                                    $mesesTotales = $plazoPeriodos * (12 / $periodicidad);
                                    $fechaFormalizacion = \Carbon\Carbon::parse($formalizacion);

                                    // Calcular vencimiento
                                    $vencimiento = $fechaFormalizacion->copy()->addMonths($mesesTotales);
                                    $set('vencimiento', $vencimiento->format('Y-m-d'));

                                    // Calcular próximo pago
                                    $proximoPago = $fechaFormalizacion->copy()->addMonths(12 / $periodicidad);
                                    $set('proximo_pago', $proximoPago->format('Y-m-d'));
                                }
                            })
                            ->native(false),

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
                            ->default(0)
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Textarea::make('observacion')
                            ->label('Observaciones')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible(),


            ]);
    }

    protected function getPlazoLabel(?int $periodicidad): string
    {
        if (!$periodicidad) return 'Plazo (periodos)';

        return match ($periodicidad) {
            1 => 'Plazo (en años)',
            2 => 'Plazo (en semestres)',
            3 => 'Plazo (en cuatrimestres)',
            4 => 'Plazo (en trimestres)',
            6 => 'Plazo (en bimestres)',
            12 => 'Plazo (en meses)',
            default => 'Plazo (periodos)'
        };
    }

    protected function getPeriodoNombre(int $cantidad, ?int $periodicidad): string
    {
        if (!$periodicidad) return 'periodos';

        $singular = match ($periodicidad) {
            1 => 'año',
            2 => 'semestre',
            3 => 'cuatrimestre',
            4 => 'trimestre',
            6 => 'bimestre',
            12 => 'mes',
            default => 'periodo'
        };

        $plural = match ($periodicidad) {
            1 => 'años',
            2 => 'semestres',
            3 => 'cuatrimestres',
            4 => 'trimestres',
            6 => 'bimestres',
            12 => 'meses',
            default => 'periodos'
        };

        return $cantidad === 1 ? $singular : $plural;
    }

    protected function validatePrestamoData(Request $request)
    {
        return $request->validate([
            'empresa_id' => 'required|integer',
            'numero_prestamo' => 'required|string|unique:prestamos',
            'monto_prestamo' => 'required|numeric|min:0.01',
            'tasa_interes' => 'required|numeric|min:0',
            'plazo_meses' => 'required|integer|min:1',
            'banco_id' => 'required|integer',
            'linea_id' => 'required|integer',
            'formalizacion' => 'required|date',
            'periodicidad_pago' => 'required|in:1,2,3,4,6,12', // Asegura valores válidos
            // otros campos necesarios
        ], [
            'periodicidad_pago.in' => 'La periodicidad seleccionada no es válida',
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
                        $simbolo = match ($record->moneda) {
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
                        return match ($record->moneda) {
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
                    ->color(fn(string $state): string => match ($state) {
                        'A' => 'success',
                        'L' => 'danger',
                        'I' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
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

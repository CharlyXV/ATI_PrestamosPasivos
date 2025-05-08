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
    protected static bool $canCreate = false;
    protected static bool $shouldRegisterNavigation = false;


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
                            ->required()
                            ->validationMessages([
                                'required' => 'Debe seleccionar una empresa',
                            ]),

                        Forms\Components\TextInput::make('numero_prestamo')
                            ->label('Número')
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'El número de préstamo es obligatorio',
                                'maxLength' => 'El número no puede exceder 255 caracteres',
                            ]),

                        Forms\Components\Select::make('banco_id')
                            ->relationship(name: 'banco', titleAttribute: 'nombre_banco')
                            ->label('Origen Fondos')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->validationMessages([
                                'required' => 'Debe seleccionar un banco de origen',
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $banco = \App\Models\Banco::find($state);
                                    $set('cuenta_desembolso', $banco->cuenta_desembolsoB);
                                }
                            }),

                        Forms\Components\TextInput::make('cuenta_desembolso')
                            ->label('Cuenta de Desembolso')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'La cuenta de desembolso es obligatoria',
                            ]),

                        Select::make('linea_id')
                            ->relationship('linea', 'nombre_linea')
                            ->label('Línea de Crédito')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->validationMessages([
                                'required' => 'Debe seleccionar una línea de crédito',
                            ]),

                        Select::make('forma_pago')
                            ->options([
                                'V' => 'Vencimiento',
                                'A' => 'Adelantado',
                            ])
                            ->required()
                            ->validationMessages([
                                'required' => 'Debe seleccionar una forma de pago',
                            ]),

                        Select::make('moneda')
                            ->options([
                                'USD' => 'USD (Dólar)',
                                'CRC' => 'CRC (Colón)',
                                'EUR' => 'EUR (Euro)',
                            ])
                            ->required()
                            ->validationMessages([
                                'required' => 'Debe seleccionar una moneda',
                            ])
                            ->live(),

                        Forms\Components\DatePicker::make('formalizacion')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->validationMessages([
                                'required' => 'La fecha de formalización es obligatoria',
                            ])
                            ->live()
                            ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                $formalizacion = $get('formalizacion');
                                $plazoMeses = $get('plazo_meses');
                                $periodicidad = (int)$get('periodicidad_pago');

                                if ($formalizacion) {
                                    $fechaFormalizacion = \Carbon\Carbon::parse($formalizacion);

                                    if ($periodicidad) {
                                        $mesesAgregar = 12 / $periodicidad;
                                        $proximoPago = $fechaFormalizacion->copy()->addMonths($mesesAgregar);
                                        $set('proximo_pago', $proximoPago->format('Y-m-d'));
                                    }

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
                            ->validationMessages([
                                'required' => 'El monto del préstamo es obligatorio',
                                'numeric' => 'El monto debe ser un valor numérico',
                            ])
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
                            ->required()
                            ->validationMessages([
                                'required' => 'Debe seleccionar un estado',
                            ]),
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
                            ->required()
                            ->validationMessages([
                                'required' => 'Debe seleccionar un tipo de tasa',
                            ]),

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
                            ->validationMessages([
                                'required' => 'Debe seleccionar una periodicidad de pago',
                            ])
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
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
                            ->required()
                            ->validationMessages([
                                'required' => 'Debe especificar el plazo del préstamo',
                            ])
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
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                $periodicidad = (int)$get('periodicidad_pago');
                                $plazoPeriodos = (int)$get('plazo_meses');
                                $formalizacion = $get('formalizacion');

                                if ($periodicidad && $plazoPeriodos && $formalizacion) {
                                    $mesesTotales = $plazoPeriodos * (12 / $periodicidad);
                                    $fechaFormalizacion = \Carbon\Carbon::parse($formalizacion);

                                    $vencimiento = $fechaFormalizacion->copy()->addMonths($mesesTotales);
                                    $set('vencimiento', $vencimiento->format('Y-m-d'));

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
                            ->step(0.01)
                            ->validationMessages([
                                'required' => 'La tasa de interés es obligatoria',
                                'numeric' => 'La tasa debe ser un valor numérico',
                                'maxValue' => 'La tasa no puede ser mayor a 100%',
                            ]),

                        Forms\Components\TextInput::make('tasa_spreed')
                            ->label('Spread de Interés')
                            ->numeric()
                            ->required()
                            ->maxValue(100)
                            ->suffix('%')
                            ->step(0.01)
                            ->validationMessages([
                                'required' => 'El spread de interés es obligatorio',
                                'numeric' => 'El spread debe ser un valor numérico',
                                'maxValue' => 'El spread no puede ser mayor a 100%',
                            ]),
                    ])
                    ->columns(3)
                    ->collapsible(),

                Section::make('Detalles')
                    ->description('Desembolsos / Saldos / Estados')
                    ->schema([
                        Forms\Components\DatePicker::make('vencimiento')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->validationMessages([
                                'required' => 'La fecha de vencimiento es obligatoria',
                            ]),

                        Forms\Components\DatePicker::make('proximo_pago')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->validationMessages([
                                'required' => 'La fecha del próximo pago es obligatoria',
                            ]),

                        Forms\Components\TextInput::make('saldo_prestamo')
                            ->label('Saldo Actual')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->validationMessages([
                                'required' => 'El saldo actual es obligatorio',
                                'numeric' => 'El saldo debe ser un valor numérico',
                            ]),

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
            'periodicidad_pago' => 'required|in:1,2,3,4,6,12',
        ], [
            'empresa_id.required' => 'Debe seleccionar una empresa',
            'empresa_id.integer' => 'El ID de empresa debe ser un número entero',
            'numero_prestamo.required' => 'El número de préstamo es obligatorio',
            'numero_prestamo.unique' => 'Este número de préstamo ya existe',
            'monto_prestamo.required' => 'El monto del préstamo es obligatorio',
            'monto_prestamo.numeric' => 'El monto debe ser un valor numérico',
            'monto_prestamo.min' => 'El monto debe ser al menos 0.01',
            'tasa_interes.required' => 'La tasa de interés es obligatoria',
            'tasa_interes.numeric' => 'La tasa debe ser un valor numérico',
            'tasa_interes.min' => 'La tasa no puede ser negativa',
            'plazo_meses.required' => 'El plazo es obligatorio',
            'plazo_meses.integer' => 'El plazo debe ser un número entero',
            'plazo_meses.min' => 'El plazo debe ser al menos 1',
            'banco_id.required' => 'Debe seleccionar un banco',
            'banco_id.integer' => 'El ID de banco debe ser un número entero',
            'linea_id.required' => 'Debe seleccionar una línea de crédito',
            'linea_id.integer' => 'El ID de línea debe ser un número entero',
            'formalizacion.required' => 'La fecha de formalización es obligatoria',
            'formalizacion.date' => 'Debe ser una fecha válida',
            'periodicidad_pago.required' => 'La periodicidad de pago es obligatoria',
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
                //Nueva columna para mostrar el número de cuotas en el plan de pagos
                TextColumn::make('plan_pagos_count')
                    ->label('Cuotas')
                    ->counts('planPagos')
                    ->badge()
                    ->color('primary'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options([
                        'A' => 'Activos',
                        'L' => 'Liquidados',
                        'I' => 'Incluidos',
                    ])
                    ->default('A'),

                SelectFilter::make('empresa_id')
                    ->relationship('empresa', 'nombre_empresa')
                    ->searchable()
                    ->preload()
                    ->label('Empresa'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver')
                    ->icon('heroicon-o-document-text')
                    ->url(fn(Prestamo $record): string => static::getUrl('view', ['record' => $record])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record) {
                        try {
                            $record->delete();
                            Notification::make()
                                ->title('Préstamo eliminado con todas sus relaciones')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al eliminar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([]);
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
            'view' => Pages\ViewPrestamo::route('/{record}/view'),  // Asegúrate de que este apunte a la clase correcta
        ];
    }
}

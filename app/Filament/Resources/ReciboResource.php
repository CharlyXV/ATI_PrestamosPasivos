<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReciboResource\Pages;
use App\Filament\Resources\ReciboResource\RelationManagers;
use App\Models\Recibo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Closure;

class ReciboResource extends Resource
{
    protected static ?string $model = Recibo::class;
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Recibo')
                    ->schema([
                        Forms\Components\Select::make('prestamo_id')
                            ->relationship('prestamo', 'numero_prestamo')
                            ->required()
                            ->live()
                            ->searchable()
                            ->preload()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $prestamo = \App\Models\Prestamo::find($state);
                                    $set('moneda_prestamo', $prestamo->moneda ?? 'CRC');
                                }
                            }),

                        Forms\Components\Select::make('tipo_pago')
                            ->label('Tipo de Pago')
                            ->options([
                                'normal' => 'Pago Normal',
                                'parcial' => 'Pago Parcial'
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state === 'normal') {
                                    $set('monto_recibo', null);
                                }
                            }),

                        Forms\Components\TextInput::make('numero_recibo')
                            ->default('REC-' . now()->format('YmdHis'))
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('tipo_recibo')
                            ->options([
                                'CN' => 'Cuota Normal',
                                'CA' => 'Cuota Anticipada',
                                'LI' => 'Liquidación'
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('detalle')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Select::make('banco_id')
                            ->relationship('banco', 'nombre_banco')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($set, $state) {
                                $banco = \App\Models\Banco::find($state);
                                $set('cuenta_desembolso', $banco?->cuenta_desembolsoB ?? '');
                            }),

                        Forms\Components\TextInput::make('cuenta_desembolso')
                            ->label('Cuenta de Desembolso')
                            ->required()
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\TextInput::make('monto_recibo')
                            ->label('Monto del Recibo')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->disabled(fn(Forms\Get $get) => $get('tipo_pago') === 'normal')
                            ->dehydrated(),

                            Forms\Components\TextInput::make('moneda_prestamo')
                            ->label('Moneda del Préstamo')
                            ->disabled()
                            ->dehydrated(false) // Esto evita que se guarde en la base de datos
                            ->formatStateUsing(fn($state) => match ($state) {
                                'CRC' => 'Colones (₡)',
                                'USD' => 'Dólares ($)',
                                'EUR' => 'Euros (€)',
                                default => $state
                            }),

                        Forms\Components\DatePicker::make('fecha_pago')
                            ->default(now())
                            ->required(),

                        Forms\Components\DatePicker::make('fecha_deposito')
                            ->default(now())
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Detalle de Cuotas')
                    ->schema([
                        Forms\Components\Repeater::make('detalles')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('planpago_id')
                                    ->label('Cuota')
                                    ->options(function (Forms\Get $get) {
                                        if (!$get('../../prestamo_id')) {
                                            return [];
                                        }

                                        $query = \App\Models\Planpago::where('prestamo_id', $get('../../prestamo_id'))
                                            ->where('plp_estados', 'pendiente')
                                            ->orderBy('numero_cuota');

                                        // Excluir cuotas que ya tienen recibo normal (sin importar el tipo de pago actual)
                                        $query->whereNotIn('id', function ($q) {
                                            $q->select('planpago_id')
                                                ->from('detalle_recibo')
                                                ->join('recibos', 'detalle_recibo.recibo_id', '=', 'recibos.id')
                                                ->where('recibos.tipo_pago', 'normal')
                                                ->where('recibos.estado', '!=', 'A'); // Excluir anulados
                                        });

                                        return $query->pluck('numero_cuota', 'id');
                                    })
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        if ($planpago = \App\Models\Planpago::find($state)) {
                                            $set('numero_cuota', $planpago->numero_cuota);
                                            $set('monto_principal', $planpago->saldo_principal);
                                            $set('monto_intereses', $planpago->saldo_interes);

                                            // Si es pago normal, establecer monto_recibo como el total
                                            if ($get('../../tipo_pago') === 'normal') {
                                                $set(
                                                    '../../monto_recibo',
                                                    $planpago->saldo_principal +
                                                        $planpago->saldo_interes
                                                );
                                            }

                                            $set(
                                                'monto_cuota',
                                                $planpago->saldo_principal +
                                                    $planpago->saldo_interes
                                            );
                                        }
                                    })
                                    ->rules([
                                        function (Forms\Get $get) {
                                            return function (string $attribute, $value, Closure $fail) use ($get) {
                                                if ($get('../../tipo_pago') === 'normal' && $value) {
                                                    $existeReciboNormal = \App\Models\DetalleRecibo::where('planpago_id', $value)
                                                        ->whereHas('recibo', function ($q) {
                                                            $q->where('tipo_pago', 'normal')
                                                                ->where('estado', '!=', 'A'); // Excluir anulados
                                                        })->exists();

                                                    if ($existeReciboNormal) {
                                                        $fail('Esta cuota ya tiene un recibo normal asociado. Use pago parcial si desea agregar otro pago.');
                                                    }
                                                }
                                            };
                                        }
                                    ]),

                                Forms\Components\TextInput::make('monto_principal')
                                    ->label('Principal')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(function (Forms\Get $get) {
                                        if ($planpago = \App\Models\Planpago::find($get('planpago_id'))) {
                                            return $planpago->saldo_principal;
                                        }
                                        return 0;
                                    }),

                                Forms\Components\TextInput::make('monto_intereses')
                                    ->label('Interés')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(function (Forms\Get $get) {
                                        if ($planpago = \App\Models\Planpago::find($get('planpago_id'))) {
                                            return $planpago->saldo_interes;
                                        }
                                        return 0;
                                    }),

                                Forms\Components\TextInput::make('monto_cuota')
                                    ->label('Total Cuota')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->disabled(),
                            ])
                            ->columns(3)
                            ->minItems(1)
                            ->maxItems(1) // Solo permitir una cuota por recibo
                            ->columnSpanFull()
                    ])
            ]);
    }

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

                Tables\Columns\TextColumn::make('tipo_pago')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'normal' => 'Normal',
                        'parcial' => 'Parcial',
                        default => $state
                    })
                    ->color(fn($state) => match ($state) {
                        'normal' => 'primary',
                        'parcial' => 'warning',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('banco.nombre_banco')
                    ->label('Banco')
                    ->searchable()
                    ->toggleable(),

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
                    })
            ])
            ->actions([
                Tables\Actions\Action::make('imprimir')
                    ->label('Imprimir')
                    ->icon('heroicon-o-printer')
                    ->url(fn($record) => route('recibos.download', $record))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecibos::route('/'),
            'create' => Pages\CreateRecibo::route('/create'),
        ];
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['detalles']) && is_array($data['detalles'])) {
            // Validar que no exista recibo normal para esta cuota
            if ($data['tipo_pago'] === 'normal') {
                foreach ($data['detalles'] as $detalle) {
                    $existeReciboNormal = \App\Models\DetalleRecibo::where('planpago_id', $detalle['planpago_id'])
                        ->whereHas('recibo', function ($q) {
                            $q->where('tipo_pago', 'normal')
                                ->where('estado', '!=', 'A'); // Excluir anulados
                        })->exists();

                    if ($existeReciboNormal) {
                        throw new \Exception('Esta cuota ya tiene un recibo normal asociado. No se puede crear otro recibo normal para la misma cuota.');
                    }
                }
            }

            $data['detalles'] = array_map(function ($detalle) {
                $montoCuota = (
                    ($detalle['monto_principal'] ?? 0) +
                    ($detalle['monto_intereses'] ?? 0)
                );

                return [
                    'planpago_id' => $detalle['planpago_id'],
                    'numero_cuota' => $detalle['numero_cuota'] ?? 1,
                    'monto_principal' => $detalle['monto_principal'] ?? 0,
                    'monto_intereses' => $detalle['monto_intereses'] ?? 0,
                    'monto_cuota' => $montoCuota,
                    'recibo_id' => $data['id'] ?? null
                ];
            }, $data['detalles']);
        }

        // Si es pago normal, el monto del recibo debe ser igual al total de la cuota
        if ($data['tipo_pago'] === 'normal' && isset($data['detalles'][0])) {
            $data['monto_recibo'] = $data['detalles'][0]['monto_cuota'];
        }

        return $data;
    }
}

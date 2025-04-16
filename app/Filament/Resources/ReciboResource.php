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

class ReciboResource extends Resource
{
    protected static ?string $model = Recibo::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Gestión Financiera';
    protected static ?int $navigationSort = 3;

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
                            ->preload(),
                            
                        Forms\Components\Select::make('tipo_recibo')
                            ->options([
                                'CN' => 'Cuota Normal',
                                'CA' => 'Cuota Anticipada',
                                'LI' => 'Liquidación'
                            ])
                            ->required(),
                            
                        Forms\Components\TextInput::make('numero_recibo')
                            ->default('REC-' . now()->format('YmdHis'))
                            ->required()
                            ->unique(ignoreRecord: true),
                            
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
                            ->numeric()
                            ->required()
                            ->minValue(0.01),
                            
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
                                        return \App\Models\Planpago::where('prestamo_id', $get('../../prestamo_id'))
                                            ->where('plp_estados', 'pendiente')
                                            ->orderBy('numero_cuota')
                                            ->pluck('numero_cuota', 'id');
                                    })
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($planpago = \App\Models\Planpago::find($state)) {
                                            $set('numero_cuota', $planpago->numero_cuota);
                                            $set('monto_principal', $planpago->saldo_principal);
                                            $set('monto_intereses', $planpago->saldo_interes);
                                            $set('monto_seguro', $planpago->saldo_seguro);
                                            $set('monto_otros', $planpago->saldo_otros);
                                            $set('monto_cuota', 
                                                $planpago->saldo_principal + 
                                                $planpago->saldo_interes + 
                                                $planpago->saldo_seguro + 
                                                $planpago->saldo_otros
                                            );
                                        }
                                    }),
                                    
                                Forms\Components\TextInput::make('monto_principal')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0),
                                    
                                Forms\Components\TextInput::make('monto_intereses')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0),
                                    
                                Forms\Components\TextInput::make('monto_seguro')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0),
                                    
                                Forms\Components\TextInput::make('monto_otros')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0),
                                    
                                Forms\Components\TextInput::make('monto_cuota')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->disabled(),
                            ])
                            ->columns(5)
                            ->minItems(1)
                            ->columnSpanFull()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_recibo')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('prestamo.numero_prestamo')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('banco.nombre_banco')
                    ->label('Banco')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('tipo_recibo')
                    ->formatStateUsing(fn ($state) => match($state) {
                        'CN' => 'Normal',
                        'CA' => 'Anticipado',
                        'LI' => 'Liquidación',
                        default => $state
                    }),
                    
                Tables\Columns\TextColumn::make('monto_recibo')
                    ->money(fn ($record) => $record->prestamo->moneda ?? 'CRC')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('fecha_pago')
                    ->date()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'I' => 'info',
                        'C' => 'success',
                        'A' => 'danger',
                        default => 'gray'
                    })
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_recibo')
                    ->options([
                        'CN' => 'Normal',
                        'CA' => 'Anticipado',
                        'LI' => 'Liquidación'
                    ]),
                    
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'I' => 'Incluido',
                        'C' => 'Contabilizado',
                        'A' => 'Anulado'
                    ])
            ])
            ->actions([
                Tables\Actions\Action::make('procesar')
                    ->label('Procesar Pago')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (Recibo $record) {
                        $record->procesarPago();
                    })
                    ->visible(fn ($record) => $record->estado == 'I')
                    ->color('success'),
                    
                Tables\Actions\Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-x-circle')
                    ->action(function (Recibo $record) {
                        $record->anularRecibo();
                    })
                    ->visible(fn ($record) => $record->estado != 'A')
                    ->color('danger'),
                    
                Tables\Actions\Action::make('imprimir')
                    ->label('Imprimir')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('recibos.download', $record))
                    ->openUrlInNewTab(),
                    
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecibos::route('/'),
            'create' => Pages\CreateRecibo::route('/create'),
            
        ];
    }

   // app/Filament/Resources/ReciboResource.php
public static function mutateFormDataBeforeSave(array $data): array
{
    if (isset($data['detalles']) && is_array($data['detalles'])) {
        $data['detalles'] = array_map(function ($detalle) {
            return [
                'planpago_id' => $detalle['planpago_id'],
                'numero_cuota' => $detalle['numero_cuota'] ?? 1,
                'monto_principal' => $detalle['monto_principal'] ?? 0,
                'monto_intereses' => $detalle['monto_intereses'] ?? 0,
                'monto_seguro' => $detalle['monto_seguro'] ?? 0,
                'monto_otros' => $detalle['monto_otros'] ?? 0,
                'monto_cuota' => (
                    ($detalle['monto_principal'] ?? 0) +
                    ($detalle['monto_intereses'] ?? 0) +
                    ($detalle['monto_seguro'] ?? 0) +
                    ($detalle['monto_otros'] ?? 0)
                ),
                'recibo_id' => $data['id'] ?? null
            ];
        }, $data['detalles']);
    }

    return $data;
}


}
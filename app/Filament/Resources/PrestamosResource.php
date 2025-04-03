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
                            ->displayFormat('d/m/Y'),
                            
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
                            ->required(),
                            
                        Forms\Components\TextInput::make('plazo_meses')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(999),
                            
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
                    
                Section::make('Plan de Pagos')
                    ->description('Cuotas del préstamo')
                    ->schema([
                        Forms\Components\Repeater::make('planpagos')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('numero_cuota')
                                    ->label('N° Cuota')
                                    ->numeric()
                                    ->required(),
                                    
                                Forms\Components\DatePicker::make('fecha_pago')
                                    ->label('Fecha Pago')
                                    ->required()
                                    ->native(false),
                                    
                                Forms\Components\TextInput::make('monto_total')
                                    ->label('Total')
                                    ->numeric()
                                    ->disabled()
                                    
                                    ->step(0.01),
                                    
                                Forms\Components\TextInput::make('monto_principal')
                                    ->label('Principal')
                                    ->numeric()
                                    ->step(0.01),
                                    
                                Forms\Components\TextInput::make('monto_interes')
                                    ->label('Interés')
                                    ->numeric()
                                    ->step(0.01),
                                    
                                Forms\Components\TextInput::make('monto_seguro')
                                    ->label('Seguro')
                                    ->numeric()
                                    ->step(0.01),
                                    
                                Forms\Components\TextInput::make('monto_otros')
                                    ->label('Otros')
                                    ->numeric()
                                    ->step(0.01),
                                    
                                Select::make('plp_estados')
                                    ->options([
                                        'pendiente' => 'Pendiente',
                                        'completado' => 'Completado',
                                    ])
                                    ->default('pendiente')
                                    ->required(),
                            ])
                            ->columns(8)
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => 'Cuota '.($state['numero_cuota'] ?? 'nueva'))
                    ])
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
                    ->formatStateUsing(function ($state, Prestamo $record) {
                        $formatted = number_format($state, 2, ',', '.');
                        return match($record->moneda) {
                            'USD' => '$' . $formatted,
                            'CRC' => '₡' . $formatted,
                            'EUR' => '€' . $formatted,
                            default => $formatted . ' ' . $record->moneda
                        };
                    })
                    ->sortable(),
                    
                TextColumn::make('banco.nombre_banco')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                    
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
                    Action::make('reporte_pdf')
                        ->icon('heroicon-o-document-text')
                        ->action(function (Prestamo $record) {
                            $pdf = Pdf::loadView('pdf.prestamo', [
                                'prestamo' => $record,
                                'planPagos' => $record->planpagos()->orderBy('numero_cuota')->get()
                            ]);
                            
                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                "reporte-prestamo-{$record->numero_prestamo}.pdf"
                            );
                        })
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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

    public static function mutateFormDataBeforeSave(array $data): array
    {
        // Asegurar valores decimales correctos
        $formatDecimal = function ($value) {
            return is_numeric($value) ? round((float)$value, 2) : 0;
        };
    
        if (isset($data['planpagos'])) {
            foreach ($data['planpagos'] as &$cuota) {
                // Solo procesar nuevas cuotas (las que no tienen ID)
                if (empty($cuota['id'])) {
                    $cuota['saldo_prestamo'] = $formatDecimal($data['monto_prestamo']);
                    $cuota['saldo_principal'] = $formatDecimal($cuota['monto_principal'] ?? 0);
                    $cuota['saldo_interes'] = $formatDecimal($cuota['monto_interes'] ?? 0);
                    $cuota['saldo_seguro'] = $formatDecimal($cuota['monto_seguro'] ?? 0);
                    $cuota['saldo_otros'] = $formatDecimal($cuota['monto_otros'] ?? 0);
                    $cuota['tasa_interes'] = $formatDecimal($data['tasa_interes']);
                    $cuota['observaciones'] = $cuota['observaciones'] ?? 'Cuota programada';
                    $cuota['plp_estados'] = 'pendiente';
                }
            }
        }
        
        return $data;
    }
}
<?php

namespace App\Filament\Resources;

use Log;
use Filament\Forms;
use Filament\Tables;
use App\Models\Empresa;
use App\Models\Prestamo;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PrestamosResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PrestamosResource\RelationManagers;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Actions\Action; 
use Filament\Tables\Columns\TextColumn; 
use Maatwebsite\Excel\Facades\Excel; 
use App\Imports\PrestamosImport;
use App\Models\Planpago;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;



// cambio 1 


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
                // schema 01
                Section::make('Préstamo')
                ->description('Condiciones del Prestamo')
                ->schema([
                    

                    // schema 02
                Forms\Components\Select::make('empresa_id')
                ->relationship(name: 'empresa', 
                                titleAttribute: 'nombre_empresa', 
                                )
                ->label('Empresa')                  
                ->searchable()                 
                ->preload() 
                  //->default('ATI')
                ->required(),
                   //
                Forms\Components\TextInput::make('numero_prestamo')
                ->label('Numero')
                ->required()                   
                ->maxLength(255),
                  //
                Forms\Components\Select::make('banco_id')
                ->relationship(name: 'banco', 
                                titleAttribute: 'nombre_banco', 
                                )
                ->label('Origen Fondos')
                ->searchable()                 
                ->preload() 
                ->required(),
                    //
                    Forms\Components\Select::make('linea_id')
                    ->relationship(name: 'linea', 
                                    titleAttribute: 'nombre_linea', 
                                    )
                    ->label('Nombre Linea')
                    ->searchable()                 
                    ->preload() 
                    ->required(),
                    //
                    Forms\Components\Select::make('forma_pago')
                    ->options([                  
                    'V' => 'Vencimiento',
                    'A' => 'Adelantado',
                    ])
                    ->required(),
                    //
                    Forms\Components\Select::make('moneda')
                            ->options([
                            'USD' => 'USD',
                            'CRC' => 'CRC',
                            ])
                            ->required() ,

                    //
                    Forms\Components\DatePicker::make('formalizacion')
                    ->required()
                    ->format('Y-m-d'), // Agregar esto
                    //->maxDate(now()),
                     //                   
                    Forms\Components\TextInput::make('monto_prestamo')
                    ->label('Monto Linea Prestamo')    
                    ->numeric()
                    ->maxValue(99999999999.99),

                    Forms\Components\Select::make('estado')
                    ->options([
                    'A' => 'Activo',
                    'L' => 'Liquidado',
                    'I' => 'Incluido',
                    ])
                    ->required(),


                    //
                    //schema 02
                    ]) 
                    
                ->collapsed()
                ->columns(3),

                Section::make('Tasas')
                ->description('Condiciones del Tasas')
                ->schema([
                    // schema 03

                            Forms\Components\Select::make('tipotasa_id')
                            ->relationship(name: 'tipotasa', 
                                        titleAttribute: 'nombre_tipo_tasa', 
                                        )
                            ->label('Tipo Tasa')   
                            ->searchable()                 
                            ->preload() 
                            ->required(),

                            Forms\Components\Select::make('periodicidad_pago')
                            ->options([
                                '12' => 'Mensual',
                                '6' => 'Biestralmente',
                                '4' => 'Trimestralmente',    
                                '3' => 'Cuatrimestralmente',
                                '2' => 'Semestralmente',
                                '1' => 'Anualmente',
                            
                            
                            ])
                            ->label('Periodicidad de Pago')  
                            ->required(),

                            Forms\Components\TextInput::make('plazo_meses')
                            ->numeric()
                            ->required()
                            ->maxValue(999),
                            
                            Forms\Components\TextInput::make('tasa_interes')
                            ->numeric()
                            ->default(fn ($record) => $record ? number_format($record->monto_prestamo, 2) : 0)
                            ->inputMode('decimal')
                            ->maxValue(999999.99)
                            ->required(),
                            
                            Forms\Components\TextInput::make('tasa_spreed')
                            ->label('Spreed Interes')     
                            ->numeric()
                            ->default(fn ($record) => $record ? number_format($record->monto_prestamo, 2) : 0)
                            ->required()
                            ->maxValue(9999.99),

                            //
                    //schema 03
                    ]) 
                    ->collapsed()
                    ->columns(5),

                    Section::make('Detalles')
                    ->description('Desembolsos / Saldos / Estados')
                    ->schema([
                        // schema 04
                       //
                            Forms\Components\DatePicker::make('vencimiento')
                            ->required()
                            ->format('Y-m-d'),
                            //->maxDate(now()),
                       //             

                        Forms\Components\DatePicker::make('proximo_pago')
                        ->required()
                        ->format('Y-m-d'),
                       //->maxDate(now()),

                        Forms\Components\TextInput::make('saldo_prestamo')
                        ->label('Saldo Linea')    
                        ->numeric()
                        ->default(fn ($record) => $record ? number_format($record->monto_prestamo, 2) : 0)
                        ->maxValue(99999999999.99),


                        //
                        Forms\Components\TextInput::make('cuenta_desembolso')
                        ->label('cuenta desembolso')
                        //->required()                   
                        ->maxLength(255),
                        //

                        //
                        Forms\Components\TextInput::make('observacion')
                        ->label('Observaciones')
                        //->required()                   
                        ->maxLength(255),

                          //schema 04
                    ]) 
                    ->collapsed()
                    ->columns(3),

                    
                        // schema 05
                        Section::make('Plan de pagos')
                        ->description('Importar plan de pagos')
                        ->schema([
                            Forms\Components\Repeater::make('planpago') // Debe coincidir con el nombre de la relación
                                ->relationship()                                
                                ->schema([
                                    Forms\Components\TextInput::make('numero_cuota')
                                        ->label('Número de Cuota')
                                        //->label(false)
                                        ->numeric()
                                      //  ->weight('thin')
                                        ->nullable(),
                                    Forms\Components\DatePicker::make('fecha_pago')
                                    ->label('Fecha de Pago')
                                    ->nullable()
                                    ->format('Y-m-d'),
                                    //  ->weight('thin')
                                    Forms\Components\TextInput::make('monto_total')
                                    ->label('Monto Total')
                                    ->default(fn ($record) => $record ? number_format($record->monto_prestamo, 2) : 0)
                                  //  ->weight('thin')
                                    ->maxValue(99999999999.99)
                                    ->step(0.01)
                                    ->numeric()
                                    ->nullable(),
                                    Forms\Components\TextInput::make('monto_principal')
                                        ->label('Monto Principal')
                                        ->default(fn ($record) => $record ? number_format($record->monto_prestamo, 2) : 0)
                                      //  ->weight('thin')
                                        ->maxValue(99999999999.99)
                                        ->step(0.01)
                                        ->numeric()
                                        ->nullable(),
                                    Forms\Components\TextInput::make('monto_interes')
                                        ->label('Monto Interés')
                                        ->default(fn ($record) => $record ? number_format($record->monto_prestamo, 2) : 0)
                                        //->weight('thin')
                                        ->maxValue(99999999999.99)
                                        ->step(0.01)
                                        ->numeric()
                                        ->nullable(),
                                    Forms\Components\TextInput::make('monto_seguro')
                                        ->label('Monto Seguro')
                                        ->default(fn ($record) => $record ? number_format($record->monto_prestamo, 2) : 0)
                                        //->weight('thin')
                                        ->maxValue(99999999999.99)
                                        ->step(0.01)
                                        ->numeric()                                        
                                        ->nullable(),
                                    Forms\Components\TextInput::make('monto_otros')
                                        ->label('Otros Montos')
                                        ->default(fn ($record) => $record ? number_format($record->monto_prestamo, 2) : 0)
                                      //  ->weight('thin')
                                        ->maxValue(99999999999.99)
                                        ->step(0.01)
                                        ->numeric()                                        
                                        ->nullable(), // Puede ser opcional                        
                                ])                               
                                ->columns(7)
                            ])
                ->collapsed()
            ]); 
    }


public function mutateFormDataBeforeSave(array $data): array
{
    // Inicializar campos que pueden estar vacíos
    $data['observaciones'] = $data['observaciones'] ?? 'Sin observaciones';
    $data['monto_seguro'] = $data['monto_seguro'] ?? 0;
    $data['monto_otros'] = $data['monto_otros'] ?? 0;
    $data['monto_interes'] = $data['monto_interes'] ?? 0;
    $data['monto_principal'] = $data['monto_principal'] ?? 0;
    $data['saldo_seguro'] = $data['saldo_seguro'] ?? 0;
    $data['saldo_interes'] = $data['saldo_interes'] ?? 0;
    $data['saldo_principal'] = $data['saldo_principal'] ?? 0;
    $data['saldo_otros'] = $data['saldo_otros'] ?? 0;
    $data['saldo_prestamo'] = $data['saldo_prestamo'] ?? 0;
    $data['monto_prestamo'] = $data['monto_prestamo'] ?? 0;
    $data['monto_total'] = $data['monto_total'] ?? 0;
     // Redondear valores numéricos a 2 decimales
    $data['saldo_seguro'] = round($data['monto_seguro'], 2);
    $data['saldo_interes'] = round($data['monto_interes'], 2);
    $data['saldo_principal'] = round($data['monto_principal'], 2);
    $data['saldo_otros'] = round($data['monto_otros'], 2);
    
     // Obtener el saldo_prestamo de la tabla de 'prestamos' y redondearlo
    $prestamo = Prestamo::find($data['id']);
    $data['saldo_prestamo'] = $prestamo ? round($prestamo->saldo_prestamo, 2) : 0;

     // Asegurarse de que saldo_prestamo no sea un valor extremadamente pequeño
    if (abs($data['saldo_prestamo']) < 0.01) {
        $data['saldo_prestamo'] = 0;
    }
    return $data;
}

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);
    
        return $record;
    }

    public static function table(Table $table): Table
    {

        return $table
        ->columns([
            //
            Tables\Columns\TextColumn::make('numero_prestamo')
            ->searchable()   
            ->sortable(),
            Tables\Columns\TextColumn::make('formalizacion')
            ->searchable()   
            ->sortable() ,           
            Tables\Columns\TextColumn::make('monto_prestamo')
            ->searchable() 
            ->money('CRC')  
            ->sortable(),            
            Tables\Columns\TextColumn::make('banco.nombre_banco')
            ->searchable()   
            ->sortable()
            ->toggleable(),                         
            
        ])
            ->filters([
                //
                Tables\Filters\SelectFilter::make('estado')
                ->options([
                    'A' => 'Activos',
                    'P' => 'Pendientes',
                    'L' => 'Liquidados',
                ]),

            ])

            
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),                
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public function importExcel(array $data)
    {
        try {
            // Importar el archivo Excel usando Laravel Excel
            Excel::import(new PrestamosImport, $data['excel_file']->getRealPath());

            // Notificación de éxito
            Notification::make()
                ->title('Importación exitosa')
                ->body('Los datos del archivo Excel se han importado correctamente.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            // Manejo de errores y notificación de fallo
            Notification::make()
                ->title('Error en la importación')
                ->body('Ocurrió un error durante la importación: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }
    
    public static function generateReport(Planpago $loan)
    {
        $payments = $loan->payments;

        $pdf = Pdf::loadView('reports.loan_report', compact('loan', 'payments'));
        return $pdf->download('loan_report.pdf');
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

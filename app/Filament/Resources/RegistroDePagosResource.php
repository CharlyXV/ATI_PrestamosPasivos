<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistroDePagosResource\Pages;
use App\Filament\Resources\RegistroDePagosResource\RelationManagers;
use App\Models\Recibo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegistroDePagosResource extends Resource
{
    protected static ?string $model = Recibo::class; // Ligado al modelo Recibo

    protected static ?string $navigationLabel = 'Registro de Pagos';
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar'; // Ícono para el menú
    protected static ?string $pluralLabel = 'Registro de Pagos';
    protected static ?string $slug = 'registro-de-pagos'; // Ruta en el menú
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Gestión Financiera'; // Agrupar en el menú

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            // schema 01
            Section::make('Registro de Pagos')
          //  ->description('Condiciones del Prestamo')
            ->schema([
                // schema 02
                Forms\Components\Select::make('empresa_id')
                ->relationship(name: 'empresa', 
                               titleAttribute: 'nombre_empresa', 
                               )
                ->label('Empresa')                  
                ->searchable()                 
                ->preload() 
                ->required(),

                Forms\Components\Select::make('prestamo_id')
                ->relationship(name: 'prestamo', 
                               titleAttribute: 'numero_prestamo', 
                               )
                ->label('Numero Prestamo')
                ->searchable()                 
                ->preload() 
                ->required(),

              Forms\Components\Select::make('tipo_recibo')
                    ->options([                  
                    'CN' => 'Cuota Normal',
                    'CA' => 'Cuota Anticipada',
                    'LI' => 'Liquidación',
                    ])
                    ->required(),
 
                Forms\Components\TextInput::make('detalle')
                    ->required(),

                 Forms\Components\Select::make('cuenta_id')
                    ->relationship(name: 'cuenta', 
                                   titleAttribute: 'numero_cuenta', 
                                   )
                    ->label('Cuenta Deposita')                  
                    ->searchable()                 
                    ->preload() 
                    ->required(),

                    Forms\Components\TextInput::make('monto_recibo')
                    ->label('Monto depositado')    
                    ->numeric()
                    ->maxValue(42949672.92),

                    Forms\Components\DatePicker::make('fecha_pago')
                    ->label('Fecha pago')    
                    ->required(),

                    Forms\Components\DatePicker::make('fecha_deposito')
                    ->label('Fecha Deposita')    
                    ->required(),

                    Forms\Components\Select::make('estado')
                    ->options([                  
                    'I' => 'Incluido',
                    'C' => 'Contabilizado'
                    ])
                    ->required()
            
              
                    /*
                Forms\Components\TextInput::make('moneda_prestamo')
                    ->required(),
                Forms\Components\TextInput::make('monto_recibo')
                    ->required(),
                Forms\Components\DatePicker::make('fecha_pago')
                    ->required(),
                Forms\Components\Repeater::make('detalles') // Relación con DetalleRecibo
                    ->relationship('detalles')
                    ->schema([
                        Forms\Components\TextInput::make('numero_cuota')
                            ->required(),
                        Forms\Components\TextInput::make('monto_principal')
                            ->required(),
                        Forms\Components\TextInput::make('monto_intereses')
                            ->required(),
                        Forms\Components\TextInput::make('monto_seguro')
                            ->required(),
                        Forms\Components\TextInput::make('monto_otros')
                            ->required(),
                        Forms\Components\TextInput::make('monto_cuota')
                            ->required(),
                    ]),
                    */
                    //
                    //schema 02
                    ]) 
                    
                ->collapsed()
                ->columns(3),

                      //schema 02
            
        ]); //schema 01


    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
                //
                Tables\Columns\TextColumn::make('id')
                ->label('Numero Recibo')   
                ->searchable()   
                ->sortable(),
                Tables\Columns\TextColumn::make('prestamo.numero_prestamo')
                ->label('Numero Prestamo')
                ->searchable()   
                ->sortable() ,           
                Tables\Columns\TextColumn::make('fecha_pago')
                ->label('Fecha Pago')
                ->searchable()   
                ->sortable(), 
                Tables\Columns\TextColumn::make('detalle')
                ->searchable()   
                ->sortable(), 
   
             
                       
                
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                ->options([
                    'I' => 'Incluido',
                    'C' => 'Contabilizado',
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Puedes definir relaciones aquí si deseas mostrar más detalles
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegistroDePagos::route('/'),
            'create' => Pages\CreateRegistroDePagos::route('/create'),
            'edit' => Pages\EditRegistroDePagos::route('/{record}/edit'),
        ];
    }
}

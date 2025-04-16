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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use App\Http\Controllers\ReportPayController;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;

class PagoResource extends Resource
{
    protected static ?string $model = Planpago::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Gestión de Cuotas';
    protected static ?string $navigationGroup = 'Gestión Financiera';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('prestamo_id')
                ->label('Préstamo')
                ->options(Prestamo::all()->pluck('numero_prestamo', 'id'))
                ->searchable()
                ->required(),
                
            Forms\Components\TextInput::make('numero_cuota')
                ->numeric()
                ->required(),
                
            Forms\Components\DatePicker::make('fecha_pago')
                ->required(),
                
            Forms\Components\TextInput::make('monto_principal')
                ->numeric()
                ->required(),
                
            Forms\Components\TextInput::make('monto_interes')
                ->numeric()
                ->required(),
                
            Forms\Components\Select::make('plp_estados')
                ->options([
                    'pendiente' => 'Pendiente',
                    'completado' => 'Completado',
                ])
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('prestamo.numero_prestamo')
                ->label('N° Préstamo')
                ->searchable()
                ->sortable(),
                
            TextColumn::make('numero_cuota')
                ->label('Cuota')
                ->sortable(),
                
            TextColumn::make('fecha_pago')
                ->label('Fecha Pago')
                ->date('d/m/Y')
                ->sortable(),
                
            TextColumn::make('monto_principal')
                ->label('Principal')
                ->formatStateUsing(function ($state, $record) {
                    $simbolo = match($record->prestamo->moneda) {
                        'CRC' => '₡',
                        'USD' => '$',
                        'EUR' => '€',
                        default => $record->prestamo->moneda
                    };
                    return $simbolo . ' ' . number_format($state, 2);
                }),
            
            TextColumn::make('monto_interes')
                ->label('Interés')
                ->formatStateUsing(function ($state, $record) {
                    $simbolo = match($record->prestamo->moneda) {
                        'CRC' => '₡',
                        'USD' => '$',
                        'EUR' => '€',
                        default => $record->prestamo->moneda
                    };
                    return $simbolo . ' ' . number_format($state, 2);
                }),
                
            TextColumn::make('monto_total')
                ->label('Total Cuota')
                ->formatStateUsing(function ($state, $record) {
                    $simbolo = match($record->prestamo->moneda) {
                        'CRC' => '₡',
                        'USD' => '$',
                        'EUR' => '€',
                        default => $record->prestamo->moneda
                    };
                    return $simbolo . ' ' . number_format($state, 2);
                }),
                
            TextColumn::make('plp_estados')
                ->label('Estado')
                ->badge()
                ->extraAttributes(fn ($record) => [
                    'data-completed' => $record->plp_estados === 'completado' ? 'true' : 'false'
                ])
                ->color(fn (string $state): string => match ($state) {
                    'completado' => 'success',
                    'pendiente' => 'warning',
                })
                ->formatStateUsing(fn(string $state): string => match ($state) {
                    'completado' => 'Completado',
                    'pendiente' => 'Pendiente',
                })
                
        ])
        ->actions([
            Tables\Actions\EditAction::make()
                ->visible(fn ($record) => $record->plp_estados !== 'completado'),
            // ... otras acciones
        ])

            ->filters([
                SelectFilter::make('prestamo_id')
                ->label('Seleccionar Préstamo')
                ->relationship('prestamo', 'numero_prestamo')
                ->default(request()->get('prestamo_id') )
                ->searchable()
                ->preload()
                ->indicator('Préstamo'),
                ])

            ->deferFilters() // Mantiene el filtro activo
            ->persistFiltersInSession() // Recuerda la selección
            
            ->headerActions([
                Action::make('generar_reporte')
                    ->label('Generar Reporte')
                    ->icon('heroicon-o-document-text')
                    ->form([
                        Select::make('prestamo_id')
                            ->label('Seleccionar Préstamo')
                            ->options(Prestamo::query()
                                ->where('estado', 'A') // Solo préstamos activos
                                ->pluck('numero_prestamo', 'id'))
                            ->searchable()
                            ->required()
                    ])
                    ->action(function (array $data) {
                        $prestamoId = $data['prestamo_id'];
                        // En lugar de retornar directamente, redirigimos a una ruta
                        return redirect()->route('report.pay', ['prestamoId' => $prestamoId]);
                    })
            ])
            ->actions([
                ViewAction::make(),
                //EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Si necesitas agregar relaciones posteriormente
        ];
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
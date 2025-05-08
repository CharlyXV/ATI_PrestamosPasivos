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
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use App\Imports\PlanpagoImport;
use Maatwebsite\Excel\Facades\Excel;

class PagoResource extends Resource
{
    protected static ?string $model = Planpago::class;
    // En ambos Resources (PrestamosResource y PagoResource) añade:
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $modelLabel = 'Plan de Pagos ';
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
                        $simbolo = match ($record->prestamo->moneda) {
                            'CRC' => '₡',
                            'USD' => '$',
                            'EUR' => '€',
                            default => $record->prestamo->moneda
                        };
                        $pagado = $record->monto_pagado_principal; // Usar el atributo calculado
                        return $simbolo . ' ' . number_format($record->monto_principal, 2);
                    }),

                TextColumn::make('monto_interes')
                    ->label('Interés')
                    ->formatStateUsing(function ($state, $record) {
                        $simbolo = match ($record->prestamo->moneda) {
                            'CRC' => '₡',
                            'USD' => '$',
                            'EUR' => '€',
                            default => $record->prestamo->moneda
                        };
                        $pagado = $record->monto_pagado_interes; // Usar el atributo calculado
                        return $simbolo .  '  ' . number_format($record->monto_interes, 2);
                    }),

                TextColumn::make('saldo_pendiente')
                    ->label('Saldo Pendiente')
                    ->formatStateUsing(function ($state, $record) {
                        $simbolo = match ($record->prestamo->moneda) {
                            'CRC' => '₡',
                            'USD' => '$',
                            'EUR' => '€',
                            default => $record->prestamo->moneda
                        };
                        return $simbolo . ' ' . number_format($state, 2);
                    })
                    ->color(function ($state) {
                        return $state > 0 ? 'danger' : 'success';
                    }),


                TextColumn::make('saldo_prestamo')
                    ->label('S* Prestamo')
                    ->formatStateUsing(function ($state, $record) {
                        $simbolo = match ($record->prestamo->moneda) {
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
                    ->extraAttributes(fn($record) => [
                        'data-completed' => $record->plp_estados === 'completado' ? 'true' : 'false'
                    ])
                    ->color(fn(string $state): string => match ($state) {
                        'completado' => 'success',
                        'pendiente' => 'warning',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'completado' => 'Completado',
                        'pendiente' => 'Pendiente',
                    })

            ])


            ->filters([
                SelectFilter::make('prestamo_id')
                    ->label('Seleccionar Préstamo')
                    ->relationship('prestamo', 'numero_prestamo')
                    ->default(request()->get('prestamo_id'))
                    ->searchable()
                    ->preload()
                    ->indicator('Préstamo'),
            ])

            ->deferFilters() // Mantiene el filtro activo
            ->persistFiltersInSession() // Recuerda la selección

            ->headerActions([
                Action::make('generar_plan')
                    ->label('Generar Plan de Pagos')
                    ->icon('heroicon-o-plus-circle')
                    ->form([
                        Select::make('prestamo_id')
                            ->label('Préstamo')
                            ->options(Prestamo::where('estado', 'A')->pluck('numero_prestamo', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $prestamo = Prestamo::find($data['prestamo_id']);
                        $reportPayController = app(\App\Http\Controllers\ReportPayController::class);
                        $reportPayController->createPaymentPlan($prestamo);

                        Notification::make()
                            ->title('Plan de pagos generado exitosamente')
                            ->success()
                            ->send();
                    }),

                Action::make('importar_plan')
                    ->label('Importar Plan')
                    ->icon('heroicon-o-document-arrow-down')
                    ->form([
                        FileUpload::make('archivo')
                            ->required()
                            ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']),
                    ])
                    ->action(function (array $data) {
                        try {
                            Excel::import(new PlanpagoImport, $data['archivo']);
                            Notification::make()
                                ->title('Plan de pagos importado exitosamente')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al importar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('generar_reporte')
                    ->label('Generar Reporte')
                    ->icon('heroicon-o-document-text')
                    ->form([
                        Select::make('prestamo_id')
                            ->label('Seleccionar Préstamo')
                            ->options(Prestamo::where('estado', 'A')->pluck('numero_prestamo', 'id'))
                            ->required()
                            ->searchable()
                    ])
                    ->action(function (array $data) {
                        if (!isset($data['prestamo_id'])) {
                            throw new \Exception('Debe seleccionar un préstamo');
                        }

                        return redirect()->route('report.pay', [
                            'prestamoId' => $data['prestamo_id'] // Asegúrate que coincida con la ruta
                        ]);
                    })
            ])
            ->bulkActions([]);
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

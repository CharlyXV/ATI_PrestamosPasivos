<?php

namespace App\Filament\Resources\PrestamosResource\Pages;

use App\Filament\Resources\PrestamosResource;
use Filament\Resources\Pages\ViewRecord;
use App\Models\Planpago;
use App\Models\Prestamo;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Support\Enums\IconPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Filament\Resources\Pages\ViewRecord\Tab;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use App\Models\Empresa;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\PrestamosResource\Pages;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Filament\Forms\Get;
use Filament\Forms\Set;

class ViewPrestamo extends ViewRecord implements HasTable
{
    use InteractsWithTable;
    
    protected static string $resource = PrestamosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Volver a préstamos')
                ->url(PrestamosResource::getUrl('index'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),
        ];
    }

    public function getTitle(): string
    {
        return ' Préstamo #'.$this->record->numero_prestamo;
    }

    // En lugar de usar getTabs() que requiere la clase Tab, usamos estos métodos:
    public function getContentTabLabel(): ?string
    {
        return 'Plan de Pagos';
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-o-document-text';
    }

    // Método para determinar cómo se muestra la página
    protected function hasFullWidthContent(): bool
    {
        return true; // Hace que la tabla use todo el ancho disponible
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Planpago::where('prestamo_id', $this->record->id)
                    ->orderBy('numero_cuota')
            )
            ->heading('Detalle del Plan de Pagos')
            ->description('Préstamo: '.$this->record->numero_prestamo)
            ->columns([
                TextColumn::make('numero_cuota')
                    ->label('Cuota')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('fecha_pago')
                    ->label('Fecha Pago')
                    ->date('d/m/Y')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('monto_principal')
                    ->label('Principal')
                    ->money(fn ($record) => $record->prestamo->moneda)
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),

                TextColumn::make('monto_interes')
                    ->label('Interés')
                    ->money(fn ($record) => $record->prestamo->moneda)
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),

                TextColumn::make('monto_total')
                    ->label('Total Cuota')
                    ->money(fn ($record) => $record->prestamo->moneda)
                    ->numeric(decimalPlaces: 2)
                    ->alignRight()
                    ->color('primary')
                    ->weight('bold'),

                TextColumn::make('saldo_prestamo')
                    ->label('Saldo Pendiente')
                    ->money(fn ($record) => $record->prestamo->moneda)
                    ->numeric(decimalPlaces: 2)
                    ->alignRight(),

                TextColumn::make('plp_estados')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'completado' => 'Pagado',
                        'pendiente' => 'Pendiente',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'completado' => 'success',
                        'pendiente' => 'warning',
                    })
                    ->alignCenter(),
            ])
            ->filters([
                // Filtros opcionales si los necesitas
            ])
            ->actions([
                // Sin acciones para mantener la vista limpia
            ])
            ->bulkActions([
                // Sin acciones masivas
            ])
            ->emptyStateHeading('Plan de pagos no generado')
            ->emptyStateDescription('Este préstamo no tiene un plan de pagos registrado.')
            ->emptyStateIcon('heroicon-o-document-text')
            ->deferLoading()
            ->paginated([10, 25, 50, 'all'])
            ->defaultPaginationPageOption(10);
    }
}
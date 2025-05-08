<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductoResource\Pages;
use App\Filament\Resources\ProductoResource\RelationManagers;
use App\Models\Producto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class ProductoResource extends Resource
{
    protected static ?string $model = Producto::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Gestión Recursos';
    protected static ?string $modelLabel = 'Producto';
    protected static ?string $pluralModelLabel = 'Productos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre_producto')
                    ->label('Nombre del Producto')
                    ->required()
                    ->validationMessages([
                        'required' => 'El nombre del producto es obligatorio',
                    ])
                    ->maxLength(255)
                    ->validationMessages([
                        'maxLength' => 'El nombre no puede exceder los 255 caracteres',
                    ])
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Este producto ya existe',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_producto')
                    ->label('Nombre del Producto')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('created_at')
                    ->label('Creado el')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->successNotificationTitle('Producto actualizado correctamente'),
                
                DeleteAction::make()
                    ->successNotificationTitle('Producto eliminado correctamente')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Producto')
                    ->modalDescription('¿Estás seguro de que deseas eliminar este producto? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar productos seleccionados')
                        ->modalDescription('¿Estás seguro de que deseas eliminar los productos seleccionados? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->emptyStateHeading('No hay productos registrados')
            ->emptyStateDescription('Crea tu primer producto haciendo clic en el botón de abajo');
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
            'index' => Pages\ListProductos::route('/'),
            'create' => Pages\CreateProducto::route('/create'),
            'edit' => Pages\EditProducto::route('/{record}/edit'),
        ];
    }
}

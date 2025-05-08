<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LineaResource\Pages;
use App\Filament\Resources\LineaResource\RelationManagers;
use App\Models\Linea;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class LineaResource extends Resource
{
    protected static ?string $model = Linea::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Gestión Recursos';
    protected static ?string $modelLabel = 'Línea de Crédito';
    protected static ?string $pluralModelLabel = 'Líneas de Crédito';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre_linea')
                    ->label('Nombre de la Línea')
                    ->required()
                    ->validationMessages([
                        'required' => 'El nombre de la línea es obligatorio',
                    ])
                    ->maxLength(255)
                    ->validationMessages([
                        'maxLength' => 'El nombre no puede exceder los 255 caracteres',
                    ])
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Esta línea de crédito ya existe',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_linea')
                    ->label('Nombre de la Línea')
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
                    ->successNotificationTitle('Línea de crédito actualizada correctamente'),
                
                DeleteAction::make()
                    ->successNotificationTitle('Línea de crédito eliminada correctamente')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Línea de Crédito')
                    ->modalDescription('¿Estás seguro de que deseas eliminar esta línea de crédito? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar líneas seleccionadas')
                        ->modalDescription('¿Estás seguro de que deseas eliminar las líneas de crédito seleccionadas? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->emptyStateHeading('No hay líneas de crédito registradas')
            ->emptyStateDescription('Crea tu primera línea de crédito haciendo clic en el botón de abajo');
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
            'index' => Pages\ListLineas::route('/'),
            'create' => Pages\CreateLinea::route('/create'),
            'edit' => Pages\EditLinea::route('/{record}/edit'),
        ];
    }
}
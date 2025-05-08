<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipoTasaResource\Pages;
use App\Filament\Resources\TipoTasaResource\RelationManagers;
use App\Models\TipoTasa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class TipoTasaResource extends Resource
{
    protected static ?string $model = TipoTasa::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Gestión Recursos';
    protected static ?string $modelLabel = 'Tipo de Tasa';
    protected static ?string $pluralModelLabel = 'Tipos de Tasas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre_tipo_tasa')
                    ->label('Nombre del Tipo de Tasa')
                    ->required()
                    ->validationMessages([
                        'required' => 'El nombre del tipo de tasa es obligatorio',
                    ])
                    ->maxLength(255)
                    ->validationMessages([
                        'maxLength' => 'El nombre no puede exceder los 255 caracteres',
                    ])
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Este tipo de tasa ya existe',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_tipo_tasa')
                    ->label('Nombre del Tipo de Tasa')
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
                    ->successNotificationTitle('Tipo de tasa actualizado correctamente'),
                
                DeleteAction::make()
                    ->successNotificationTitle('Tipo de tasa eliminado correctamente')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Tipo de Tasa')
                    ->modalDescription('¿Estás seguro de que deseas eliminar este tipo de tasa? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar tipos de tasas seleccionados')
                        ->modalDescription('¿Estás seguro de que deseas eliminar los tipos de tasas seleccionados? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->emptyStateHeading('No hay tipos de tasas registrados')
            ->emptyStateDescription('Crea tu primer tipo de tasa haciendo clic en el botón de abajo');
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
            'index' => Pages\ListTipoTasas::route('/'),
            'create' => Pages\CreateTipoTasa::route('/create'),
            'edit' => Pages\EditTipoTasa::route('/{record}/edit'),
        ];
    }
}

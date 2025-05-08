<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmpresaResource\Pages;
use App\Models\Empresa;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class EmpresaResource extends Resource
{
    protected static ?string $model = Empresa::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Gestión Recursos';
    protected static ?string $modelLabel = 'Empresa';
    protected static ?string $pluralModelLabel = 'Empresas';
    protected static ?string $navigationLabel = 'Gestión de Empresas';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre_empresa')
                    ->label('Nombre de la Empresa')
                    ->required()
                    ->validationMessages([
                        'required' => 'El nombre de la empresa es obligatorio',
                    ])
                    ->maxLength(255)
                    ->validationMessages([
                        'maxLength' => 'El nombre no puede exceder los 255 caracteres',
                    ])
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'unique' => 'Esta empresa ya está registrada',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_empresa')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('created_at')
                    ->label('Fecha de Registro')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make()
                    ->successNotificationTitle('Empresa actualizada correctamente'),
                
                DeleteAction::make()
                    ->successNotificationTitle('Empresa eliminada correctamente')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Empresa')
                    ->modalDescription('¿Estás seguro de que deseas eliminar esta empresa? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar empresas seleccionadas')
                        ->modalDescription('¿Estás seguro de que deseas eliminar las empresas seleccionadas? Esta acción no se puede deshacer.')
                        ->modalSubmitActionLabel('Sí, eliminar'),
                ]),
            ])
            ->emptyStateHeading('No hay empresas registradas')
            ->emptyStateDescription('Registra tu primera empresa haciendo clic en el botón de abajo');
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
            'index' => Pages\ListEmpresas::route('/'),
            'create' => Pages\CreateEmpresa::route('/create'),
            'edit' => Pages\EditEmpresa::route('/{record}/edit'),
        ];
    }
}
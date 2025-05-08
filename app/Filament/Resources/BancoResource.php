<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BancoResource\Pages;
use App\Models\Banco;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class BancoResource extends Resource
{
    protected static ?string $model = Banco::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Gestión Recursos';
    protected static ?string $modelLabel = 'Banco';
    protected static ?string $pluralModelLabel = 'Bancos';
    protected static ?string $navigationLabel = 'Gestión de Bancos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nombre_banco')
                    ->label('Nombre del Banco')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->validationMessages([
                        'required' => 'El nombre del banco es obligatorio',
                        'maxLength' => 'El nombre no puede exceder 255 caracteres',
                        'unique' => 'Este banco ya está registrado'
                    ]),

                TextInput::make('cuenta_desembolsoB')
                    ->label('Cuenta de Desembolso')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->mask('9999-9999-9999-9999')
                    ->placeholder('Ej: 1234-5678-9012-3456')
                    ->validationMessages([
                        'required' => 'La cuenta de desembolso es obligatoria',
                        'maxLength' => 'La cuenta no puede exceder 255 caracteres',
                        'unique' => 'Esta cuenta ya está registrada'
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre_banco')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('cuenta_desembolsoB')
                    ->label('Cuenta')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => implode('-', str_split($state, 4))),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->date('d/m/Y')
                    ->sortable()
            ])
            ->filters([
                // Filters can be added here
            ])
            ->actions([
                EditAction::make()
                    ->successNotificationTitle('Banco actualizado correctamente'),
                    
                DeleteAction::make()
                    ->successNotificationTitle('Banco eliminado correctamente')
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar eliminación')
                    ->modalDescription('¿Está seguro de eliminar este banco? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                ])
            ])
            ->emptyStateHeading('No hay bancos registrados')
            ->emptyStateDescription('Crea tu primer banco haciendo clic en el botón');
    }

    public static function getRelations(): array
    {
        return [
            // Relations can be added here
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBancos::route('/'),
            'create' => Pages\CreateBanco::route('/create'),
            'edit' => Pages\EditBanco::route('/{record}/edit'),
        ];
    }
}
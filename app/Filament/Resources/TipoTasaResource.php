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
    public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('nombre_tipo_tasa')
                ->label('Nombre del Tipo de Tasa')
                ->required()
                ->maxLength(255),
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
                ->date()
                ->sortable(),
        ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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

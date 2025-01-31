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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
    public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('nombre_linea')
                ->label('Nombre de la Línea')
                ->required()
                ->maxLength(255),
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
            'index' => Pages\ListLineas::route('/'),
            'create' => Pages\CreateLinea::route('/create'),
            'edit' => Pages\EditLinea::route('/{record}/edit'),
        ];
    }
}

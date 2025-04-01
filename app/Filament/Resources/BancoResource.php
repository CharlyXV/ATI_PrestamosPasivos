<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BancoResource\Pages;
use App\Filament\Resources\BancoResource\RelationManagers;
use App\Models\Banco;
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
class BancoResource extends Resource
{
    protected static ?string $model = Banco::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Gestión Recursos';
    public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('nombre_banco')
                ->label('Nombre del Banco')
                ->required()
                ->maxLength(255),

                Forms\Components\TextInput::make('cuenta_desembolsoB') // Cambiar 'cuenta' por 'cuenta_desembolso'
                ->label('Cuenta de Desembolso')
                ->required() // Añadir required si es necesario
                ->maxLength(255),
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('nombre_banco')
                ->label('Nombre del Banco')
                ->sortable()
                ->searchable(),
                TextColumn::make('cuenta_desembolsoB')
                ->label('Numero de Cuenta')
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
            'index' => Pages\ListBancos::route('/'),
            'create' => Pages\CreateBanco::route('/create'),
            'edit' => Pages\EditBanco::route('/{record}/edit'),
        ];
    }
}

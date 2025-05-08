<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConsultaAsientoResource\Pages;
use App\Filament\Resources\ConsultaAsientoResource\RelationManagers;
use App\Models\ConsultaAsiento;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConsultaAsientoResource extends Resource
{
    protected static ?string $model = ConsultaAsiento::class;
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?string $navigationGroup = 'Gestión Contable';
    protected static ?int $navigationSort = 1; // Orden dentro del grupo

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageConsultaAsientos::route('/'),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Filament\Resources\Recipes\Tables;

use App\Models\Recipe;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecipesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->state(fn (Recipe $record): ?string => $record->coverMedia()?->path),

                TextColumn::make('title')
                    ->label('Titlu')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('subcategories.name')
                    ->label('Subcategorii')
                    ->badge(),

                TextColumn::make('time_label')
                    ->label('Timp'),

                TextColumn::make('servings')
                    ->label('Porții')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('difficulty')
                    ->label('Dificultate')
                    ->badge()
                    ->sortable(),

                TextColumn::make('calories')
                    ->label('Calorii')
                    ->numeric()
                    ->suffix(' kcal')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Creat')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->modifyQueryUsing(fn ($query) => $query->with('media'))
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

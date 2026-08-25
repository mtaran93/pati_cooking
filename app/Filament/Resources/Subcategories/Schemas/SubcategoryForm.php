<?php

declare(strict_types=1);

namespace App\Filament\Resources\Subcategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubcategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label('Categorie')
                    ->relationship('category', 'name')
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->preload(),

                TextInput::make('name')
                    ->label('Nume')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Recipes\Schemas;

use App\Models\Recipe;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RecipeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titlu')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, callable $get, callable $set): void {
                        // Only auto-fill the slug on create, and only while it's untouched.
                        if ($operation === 'create' && blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),

                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->helperText('Se generează automat din titlu dacă e lăsat gol.')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),

                Select::make('category_id')
                    ->label('Categorie')
                    ->relationship('category', 'name')
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nume')
                            ->required()
                            ->maxLength(255)
                            ->unique('categories', 'name'),
                    ]),

                Textarea::make('blurb')
                    ->label('Descriere scurtă')
                    ->required()
                    ->maxLength(300)
                    ->rows(3)
                    ->columnSpanFull(),

                TextInput::make('note')
                    ->label('Notă personală (opțional)')
                    ->helperText('Apare ca citat semnat „— Pati". Se omite dacă e gol.')
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('time_label')
                    ->label('Timp')
                    ->required()
                    ->helperText('Etichetă afișată, ex. „40 min", „3 ore", „30 min + 4 ore repaus".')
                    ->maxLength(255),

                TextInput::make('servings')
                    ->label('Porții')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(32767),

                Select::make('difficulty')
                    ->label('Dificultate')
                    ->required()
                    ->options(array_combine(Recipe::DIFFICULTIES, Recipe::DIFFICULTIES))
                    ->native(false),

                TextInput::make('calories')
                    ->label('Calorii (kcal)')
                    ->required()
                    ->numeric()
                    ->minValue(0),

                FileUpload::make('photo')
                    ->label('Fotografie')
                    ->image()
                    ->disk('public')
                    ->directory('recipes')
                    ->imageEditor()
                    ->columnSpanFull(),

                Repeater::make('ingredients')
                    ->label('Ingrediente')
                    ->simple(
                        TextInput::make('item')
                            ->required()
                            ->placeholder('ex. 320 g orez Carnaroli')
                    )
                    ->addActionLabel('Adaugă ingredient')
                    ->reorderable()
                    ->columnSpanFull(),

                RichEditor::make('description')
                    ->label('Preparare (metodă)')
                    ->helperText('Scrie pașii ca listă numerotată (ordered list). Se afișează ca pașii numerotați de pe pagina rețetei.')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}

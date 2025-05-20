<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApplicationTemplateResource\Pages;
use App\Models\ApplicationTemplate;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ApplicationTemplateResource extends Resource
{
    protected static ?string $model = ApplicationTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('type')
                    ->required()
                    ->maxLength(255),
                Textarea::make('body')
                    ->label('Template Body')
                    ->rows(15)
                    ->required()
                    ->helperText('Use placeholders like %name%, %roll%, etc.')
                    ->maxLength(10000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('title')->searchable()->limit(50),
                TextColumn::make('type')->badge(),
                TextColumn::make('created_at')->label('Created')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListApplicationTemplates::route('/'),
            'create' => Pages\CreateApplicationTemplate::route('/create'),
            'edit' => Pages\EditApplicationTemplate::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationGroup = 'Course Management';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getSchema():array
    {
        return [
            Forms\Components\TextInput::make('code')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\Textarea::make('description')
                ->maxLength(255)
                ->default(null),
            Forms\Components\TextInput::make('credit')
                ->required()
                ->numeric(),
            //show year option 1 2 3 4 select
            Forms\Components\Select::make('year')
                ->label('Year')
                ->required()
                ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
            Forms\Components\Select::make('semester')
                ->label('Semester')
                ->required()
                ->options([1 => '1', 2 => '2']),
            Forms\Components\Select::make('department_id')
                ->label('Department')
                ->required()
                ->options(Department::pluck('name', 'id')->toArray()),
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::getSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('credit')
                    ->numeric()
                    ->sortable(),
                //show year option 1 2 3 4 select
                Tables\Columns\TextColumn::make('year')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('semester')
                    ->numeric()
                    ->sortable(),
                //showing department name based on department_id
                Tables\Columns\TextColumn::make('department.name')
                    ->searchable()
                    ->label('Department'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
            'view' => Pages\ViewCourse::route('/{record}'),
        ];
    }
}

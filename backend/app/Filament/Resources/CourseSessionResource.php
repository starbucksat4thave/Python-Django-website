<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseSessionResource\Pages;
use App\Filament\Resources\CourseSessionResource\RelationManagers\CourseMaterialsRelationManager;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CourseSessionResource extends Resource
{
    protected static ?string $model = CourseSession::class;

    protected static ?string $navigationGroup = 'Course Management';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('course_id')
                    ->label('Course')
                    ->options(
                        Course::all()->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('teacher_id')
                    ->label('Teacher')
                    ->options(
                        User::whereHas('roles', function (Builder $query) {
                            $query->where('name', 'teacher');
                        })->get()->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('session')
                    ->default(request()->query('session')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('course.code')
                    ->label('Course Code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.year')
                    ->label('Year')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('course.semester')
                    ->label('Semester')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('teacher.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('session')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('session')
                    ->label('Session Year')
                    ->options(
                        CourseSession::query()
                            ->select('session')
                            ->distinct()
                            ->orderBy('session', 'desc')
                            ->pluck('session', 'session')
                    )
                    ->searchable()
                    ->query(function (Builder $query, array $state) {
                        $query->when($state['value'],
                            fn($q) => $q->where('session', $state['value'])
                        );
                    }),
                SelectFilter::make('teacher_id')
                    ->label('Teacher')
                    ->options(
                        User::whereHas('roles', function (Builder $query) {
                            $query->where('name', 'teacher');
                        })->get()
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $state) {
                        $query->when($state['value'],
                            fn($q) => $q->where('teacher_id', $state['value'])
                        );
                    }),
                SelectFilter::make('course_id')
                    ->label('Course')
                    ->options(
                        Course::query()
                            ->select('name', 'id')
                            ->distinct()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $state) {
                        $query->when($state['value'],
                            fn($q) => $q->where('course_id', $state['value'])
                        );
                    }),
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
            // Change from ::make() to ::class
            CourseMaterialsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseSessions::route('/'),
            'create' => Pages\CreateCourseSession::route('/create'),
            'view' => Pages\ViewCourseSession::route('/{record}'),
            'edit' => Pages\EditCourseSession::route('/{record}/edit'),
        ];
    }
}

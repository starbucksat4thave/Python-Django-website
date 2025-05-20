<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\User;
use Exception;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;
    protected static ?string $navigationGroup = 'Course Management';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    // Use Filament's built-in pill tabs with active state styling
    protected static string $tabsStyle = 'pill';

// Optional: Add these to make active tabs more prominent
    protected static string $tabsColor = 'primary';
    protected static string $tabsSize = 'md'; // sm | md | lg

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Placeholder::make('course_code')
                    ->label('Course Code')
                    ->content(fn ($record) => $record->courseSession?->course?->code ?? 'N/A'),
                Placeholder::make('course_session')
                    ->label('Course Title')
                    ->content(fn ($record) => $record->courseSession?->course?->name ?? 'N/A'),
                Placeholder::make('course_year')
                    ->label('Year')
                    ->content(fn ($record) => $record->courseSession?->course?->year ?? 'N/A'),
                Placeholder::make('course_semester')
                    ->label('Semester')
                    ->content(fn ($record) => $record->courseSession?->course?->semester ?? 'N/A'),
                Placeholder::make('course_session')
                    ->label('Session')
                    ->content(function ($record) {
                        return $record?->courseSession?->session ?? 'N/A';
                    }),
                Placeholder::make('teacher')
                    ->label('Teacher')
                    ->content(fn ($record) => $record->courseSession?->teacher?->name ?? 'N/A'),
                Placeholder::make('student')
                    ->label('Student')
                    ->content(fn ($record) => $record->student?->name ?? 'N/A'),
                Placeholder::make('student_session')
                    ->label('Student Session')
                    ->content(fn ($record) => $record->student?->session ?? 'N/A'),
                TextInput::make('class_assessment_marks')
                    ->numeric()
                    ->maxValue(30)
                    ->required(),
                TextInput::make('final_term_marks')
                    ->numeric()
                    ->maxValue(70)
                    ->required(),
                Toggle::make('is_enrolled')
                    ->default(false)
                    ->disabled(),
            ]);
    }

    /**
     * @throws Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('courseSession.course.name')
                    ->label('Course Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('courseSession.course.code')
                    ->label('Course Code')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('courseSession.session')
                    ->label('Course Session')
                    ->searchable(),

                Tables\Columns\TextColumn::make('courseSession.course.year')
                    ->label('Year')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('courseSession.course.semester')
                    ->label('Semester')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.name')
                    ->label('Student Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('student.session')
                    ->label('Student Session')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_enrolled')
                    ->label('Status')
                    ->searchable()
                    ->sortable()
                    ->boolean(),
                Tables\Columns\TextColumn::make('class_assessment_marks')
                    ->label('CA Marks')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('final_term_marks')
                    ->label('Final Term Marks')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Enrolled At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('session_year')
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
                        $query->when($state['value'], fn($q) => $q->whereHas(
                            'courseSession',
                            fn($sub) => $sub->where('session', $state['value'])
                        ));
                    }),

                // Student filter
                SelectFilter::make('student_id')
                    ->label('Student')
                    ->options(
                        User::whereHas('roles', fn($q) => $q->where('name', 'student'))
                            ->get()
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $state) {
                        $query->when($state['value'],
                            fn($q) => $q->where('student_id', $state['value'])
                        );
                    }),

                // Course filter
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
                        $query->when($state['value'], fn($q) => $q->whereHas(
                            'courseSession',
                            fn($sub) => $sub->where('course_id', $state['value'])
                        ));
                    }),
                TernaryFilter::make('is_enrolled')
                    ->label('Enrollment Status')
                    ->placeholder('All')
                    ->trueLabel('Enrolled')
                    ->falseLabel('Not Enrolled')
                    ->queries(
                        true: fn (Builder $query) => $query->where('is_enrolled', true),
                        false: fn (Builder $query) => $query->where('is_enrolled', false),
                        blank: fn (Builder $query) => $query // Shows all when no selection
                    )
                    ->default(null)
                    ->indicator('Enrollment Status')
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
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'view' => Pages\ViewEnrollment::route('/{record}'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}

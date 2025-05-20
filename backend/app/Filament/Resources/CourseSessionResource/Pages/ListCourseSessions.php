<?php

namespace App\Filament\Resources\CourseSessionResource\Pages;

use App\Filament\Resources\CourseSessionResource;
use App\Helpers\SemesterTabHelper;
use App\Http\Controllers\api\EnrollmentController;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use App\Models\Course;
use App\Models\User;
use App\Models\CourseSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Get;
use Filament\Forms\Set;

class ListCourseSessions extends ListRecords
{
    protected static string $resource = CourseSessionResource::class;

    // Convert this to a static method so it can be reused
    public static function getCoursesFor($year, $semester): array
    {
        if (!$year || !$semester) {
            return [];
        }

        return Course::where('year', $year)
            ->where('semester', $semester)
            ->get()
            ->map(function ($course) {
                return [
                    'course_id' => $course->id,
                    'course_name' => $course->name,
                    'teacher_id' => null,
                ];
            })
            ->toArray();
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('Bulk Create')
                ->label('Bulk Create Course Sessions')
                ->icon('heroicon-o-plus')
                ->form([
                    Forms\Components\TextInput::make('session')
                        ->label('Session')
                        ->required(),

                    Forms\Components\Select::make('year')
                        ->label('Year')
                        ->options([
                            1 => 'Year 1',
                            2 => 'Year 2',
                            3 => 'Year 3',
                            4 => 'Year 4',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            $set('courseAssignments', self::getCoursesFor($state, $get('semester')));
                        }),

                    Forms\Components\Select::make('semester')
                        ->label('Semester')
                        ->options([
                            1 => 'Semester 1',
                            2 => 'Semester 2',
                        ])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, Set $set, Get $get) {
                            $set('courseAssignments', self::getCoursesFor($get('year'), $state));
                        }),

                    Forms\Components\Repeater::make('courseAssignments')
                        ->label('Courses')
                        ->schema([
                            Forms\Components\Hidden::make('course_id'),
                            Forms\Components\TextInput::make('course_name')->disabled(),
                            Forms\Components\Select::make('teacher_id')
                                ->label('Assign Teacher')
                                ->options(User::role('teacher')->pluck('name', 'id'))
                                ->searchable()
                                ->required(),
                        ])
                        ->addable(false)
                        ->deletable(false),
                ])
                ->action(function (array $data) {
                    foreach ($data['courseAssignments'] as $assignment) {
                        $existingSession = CourseSession::where('course_id', $assignment['course_id'])
                            ->where('session', $data['session'])
                            ->first();

                        if (!$existingSession) {
                            $newSession = CourseSession::create([
                                'course_id' => $assignment['course_id'],
                                'session' => $data['session'],
                                'teacher_id' => $assignment['teacher_id'],
                            ]);
                            app(EnrollmentController::class)->storeAll($newSession);
                        } else {
                            $existingSession->update(['teacher_id' => $assignment['teacher_id']]);
                        }
                    }
                }),
        ];
    }
    public function getTabs(): array
    {
        return SemesterTabHelper::makeTabs(
            modelClass: CourseSession::class,
            relationshipPath: 'course',
            allLabel: 'All Course Sessions'
        );
    }
}

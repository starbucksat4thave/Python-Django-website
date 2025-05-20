<?php

namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use App\Helpers\SemesterTabHelper;
use App\Models\Course;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return SemesterTabHelper::makeTabs(
            modelClass: Course::class,
            relationshipPath: 'self', // Special keyword for direct filtering
            allLabel: 'All Courses'
        );
    }
}

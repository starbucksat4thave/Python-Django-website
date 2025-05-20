<?php

namespace App\Filament\Resources\CourseMaterialResource\Pages;

use App\Filament\Resources\CourseMaterialResource;
use App\Helpers\SemesterTabHelper;
use App\Models\CourseResource;
use Filament\Resources\Pages\ListRecords;

class ListCourseMaterials extends ListRecords
{
    protected static string $resource = CourseMaterialResource::class;

    public function getTabs(): array
    {
        return SemesterTabHelper::makeTabs(
            modelClass: CourseResource::class,
            relationshipPath: 'courseSession.course',
            allLabel: 'All Materials'
        );
    }
}

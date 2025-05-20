<?php

namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Filament\Resources\EnrollmentResource;
use App\Helpers\SemesterTabHelper;
use App\Models\Course;
use App\Models\Enrollment;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        return SemesterTabHelper::makeTabs(
            modelClass: Enrollment::class,
            relationshipPath: 'courseSession.course',
            allLabel: 'All Enrollments'
        );
    }
}

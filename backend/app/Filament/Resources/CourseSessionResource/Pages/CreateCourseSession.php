<?php

namespace App\Filament\Resources\CourseSessionResource\Pages;

use App\Filament\Resources\CourseSessionResource;
use App\Http\Controllers\api\EnrollmentController;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCourseSession extends CreateRecord
{
    protected static string $resource = CourseSessionResource::class;

    public function afterCreate(): void
    {
        // Call your controller method
        app(EnrollmentController::class)->storeAll($this->record);
    }
}

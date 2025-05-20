<?php

namespace App\Filament\Resources\NoticeResource\Pages;

use App\Filament\Resources\NoticeResource;
use App\Http\Controllers\api\NoticeController;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNotice extends CreateRecord
{
    protected static string $resource = NoticeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['published_by'] = auth()->id();
        return $data;
    }
    protected function afterCreate(): void
    {
        // Call your controller method
        app(NoticeController::class)->sendNoticeForApproval($this->record);
    }
}

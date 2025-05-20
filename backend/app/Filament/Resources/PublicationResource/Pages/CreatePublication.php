<?php

namespace App\Filament\Resources\PublicationResource\Pages;

use App\Filament\Resources\PublicationResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Http;

class CreatePublication extends CreateRecord
{
    protected static string $resource = PublicationResource::class;

//    protected function mutateFormDataBeforeCreate(array $data): array
//    {
//        $doi = $data['doi'];
//
//        try {
//            $response = Http::get("https://api.crossref.org/works/{$doi}");
//
//            if ($response->failed()) {
//                throw new \Exception('Failed to fetch publication data from Crossref.');
//            }
//
//            $publicationData = $response->json()['message'];
//
//            $data['title'] = $publicationData['title'][0] ?? null;
//            $data['abstract'] = $publicationData['abstract'] ?? null;
//            $data['journal'] = $publicationData['container-title'][0] ?? null;
//            $data['volume'] = $publicationData['volume'] ?? null;
//            $data['issue'] = $publicationData['issue'] ?? null;
//            $data['pages'] = $publicationData['page'] ?? null;
//            if (isset($publicationData['published-print']['date-parts'][0])) {
//                $dateParts = $publicationData['published-print']['date-parts'][0];
//                $year = $dateParts[0] ?? null;
//                $month = $dateParts[1] ?? 1;
//                $day = $dateParts[2] ?? 1;
//
//                if ($year) {
//                    $data['published_date'] = sprintf('%04d-%02d-%02d', $year, $month, $day);
//                } else {
//                    $data['published_date'] = null;
//                }
//            } else {
//                $data['published_date'] = null;
//            }
//            $data['url'] = $publicationData['URL'] ?? null;
//            $data['pdf_link'] = null; // Crossref may not provide a direct PDF link
//        } catch (\Exception $e) {
//            Notification::make()
//                ->title('Error')
//                ->body($e->getMessage())
//                ->danger()
//                ->send();
//
//            $this->halt(); // Stop the form submission
//        }
//
//        return $data;
//    }

    protected function afterCreate(): void
    {
        $users = $this->form->getState()['users'] ?? [];

        if (!empty($users)) {
            $this->record->users()->sync($users);
        }
    }
}

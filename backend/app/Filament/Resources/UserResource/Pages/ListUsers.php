<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Imports\UsersImport;
use Exception;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Validation\ValidationException;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Action::make('ImportStudents ')
                ->label('Import Students')
                ->color('danger')
                ->icon('heroicon-s-document-arrow-up')
            ->form([
                FileUpload::make('attachment')
                    ->disk('local')
                    ->directory('user_imports')
                    ->visibility('private')
                    ->label('Attachment')
                    ->rules('required', 'mimes:csv,xlsx'),
            ])
            ->action(function (array $data) {
                $file = Storage::disk('local')->path($data['attachment']);

                try {
                    Excel::import(new UsersImport, $file);

                    // Delete the file after successful import
                    Storage::disk('local')->delete( $data['attachment']);

                    Notification::make()
                        ->title('User Imported successfully')
                        ->success()
                        ->send();
                }
                catch (ValidationException $e) {
                    // Retrieve all error messages
                    $errors = $e->validator->errors()->all();

                    // Format errors as an unordered list
                    $errorList = '<ul>';
                    foreach ($errors as $error) {
                        $errorList .= "<li>{$error}</li>";
                    }
                    $errorList .= '</ul>';

                    // Send a Filament notification with the formatted error messages
                    Notification::make()
                        ->title('Error Importing File')
                        ->body($errorList)
                        ->danger()
                        ->persistent()
                        ->send();
                }
                catch (Exception $e) {
                    Notification::make()
                        ->title('Error importing file')
                        ->body($e->getMessage())
                        ->duration(10000)
                        ->danger()
                        ->send();
                    return redirect()->back()->with('error', 'Error importing file');
                }
                return redirect()->back()->with('success', 'File imported successfully');
            }),
            Action::make('DownloadSample')
                ->label('Download Sample')
                ->color('primary')
                ->icon('heroicon-s-arrow-down')
                ->action(function () {
                    // Create a new Spreadsheet object
                    $spreadsheet = new Spreadsheet();
                    $sheet = $spreadsheet->getActiveSheet();

                    // Set header row
                    $headers = [
                        'A1' => 'name',
                        'B1' => 'image',
                        'C1' => 'email',
                        'D1' => 'department_code',
                        'E1' => 'university_id',
                        'F1' => 'session',
                        'G1' => 'dob',
                        'H1' => 'phone',
                        'I1' => 'address',
                        'J1' => 'city',
                    ];

                    foreach ($headers as $cell => $header) {
                        $sheet->setCellValue($cell, $header);
                    }

                    // Set sample data row
                    $sampleData = [
                        'A2' => 'John Doe',
                        'B2' => 'images/image_path.jpg',
                        'C2' => 'john.doe@example.com',
                        'D2' => 'CSE',
                        'E2' => '210136',
                        'F2' => '2021',
                        'G2' => '1999-01-15',
                        'H2' => '01812345678',
                        'I2' => '123 Main St',
                        'J2' => 'Pabna',
                    ];

                    foreach ($sampleData as $cell => $data) {
                        $sheet->setCellValue($cell, $data);
                    }

                    // Write the spreadsheet to a temporary file
                    $writer = new Xlsx($spreadsheet);
                    $tempFilePath = tempnam(sys_get_temp_dir(), 'sample') . '.xlsx';
                    $writer->save($tempFilePath);

                    // Return the file as a download response
                    return Response::download($tempFilePath, 'sample.xlsx')->deleteFileAfterSend(true);
                }),

        ];
    }
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Users')
                ->modifyQueryUsing(fn (Builder $query) => $query),
            'students' => Tab::make('Students')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'student'))),
            'teachers' => Tab::make('Teachers')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))),
            'admins' => Tab::make('Admins')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'admin'))),
            'superadmins' => Tab::make('Super Admins')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'super-admin'))),
        ];
    }
}

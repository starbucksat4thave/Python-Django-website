<?php

namespace App\Imports;

use App\Mail\WelcomeUserMail;
use App\Models\Department;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;

class UsersImport implements ToModel, WithHeadingRow, WithValidation, WithEvents, WithUpserts
{
    use Importable;

    // Array to store imported users
    protected array $importedUsers = [];

    public function uniqueBy(): string
    {
        return 'email';
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $department_id = self::getDepartmentId($row['department_code']);
        $password = Str::random(12); // Generate a random 12-character password
        $dob = Carbon::createFromTimestamp(($row['dob'] - 25569) * 86400)->format('Y-m-d');

        // Default image path
        $imagePath = 'images/users/default.png';

        $user = new User([
            'name' => $row['name'],
            'image' => $imagePath,
            'email' => $row['email'],
            'password' => Hash::make($password),
            'university_id' => $row['university_id'],
            'department_id' => $department_id,
            'session' => $row['session'],
            'year' => 1,
            'semester' => 1,
            'dob' => $dob,
            'phone' => $row['phone'],
            'address' => $row['address'],
            'city' => $row['city'],
            'designation' => 'student',
            'publication_count' => 0,
            'status' => 'active',
        ]);

        // Store the user in the importedUsers array
        $this->importedUsers[] = $user;

        return $user;
    }

    public function rules(): array
    {
        return [
            '*.name'            => 'required|string|max:255',
            '*.email'           => 'required|email|unique:users,email',
            '*.university_id'   => 'required|unique:users,university_id',
            '*.department_code' => 'required|string|exists:departments,short_name',
            '*.session'         => 'required',
            '*.dob'             => 'required',
            '*.phone'           => 'nullable|string|max:11|min:11',
            '*.address'         => 'nullable|string',
            '*.city'            => 'nullable|string',
        ];
    }

    public static function getDepartmentId(string $department)
    {
        return Department::where('short_name', $department)->first()->id;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                // Actions to perform after the import
                foreach ($this->importedUsers as $user) {
                    $student = User::where('email', $user->email)->first();
                    $student->assignRole('student');
                    $token = Password::getRepository()->create($user);
                    Mail::to($user->email)->send(new WelcomeUserMail($user, $token));
                }
            },
        ];
    }

}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApplicationTemplate;

class ApplicationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApplicationTemplate::create([
            'type' => 'leave',
            'title' => 'Application for Leave',
            'body' => <<<EOT
I, %name% (Student ID: %id%), a student of %program%, request a leave of absence from %start_date% to %end_date% due to %reason%.

Kindly grant me the leave for the mentioned period.
EOT
        ]);

        ApplicationTemplate::create([
            'type' => 'transcript',
            'title' => 'Request for Official Transcript',
            'body' => <<<EOT
I, %name% (Student ID: %id%), have successfully completed my %program% from your institution. I require an official transcript for %purpose%.

I kindly request you to process my transcript at the earliest.
EOT
        ]);
    }
}

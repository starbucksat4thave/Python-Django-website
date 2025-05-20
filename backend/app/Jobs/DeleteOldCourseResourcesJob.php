<?php

namespace App\Jobs;

use App\Models\CourseResource;
use App\Models\CourseSession;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteOldCourseResourcesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $threshold = Carbon::now()->subDays(60);
        // Delete all resources from completed sessions older than 60 days

        $sessions = CourseSession::where('status', 'completed')
            ->where('updated_at', '<', $threshold)
            ->get();

        foreach ($sessions as $session) {
            $resources = CourseResource::where('course_session_id', $session->id)->get();

            foreach ($resources as $resource) {
                Storage::delete($resource->file_path);
                $resource->delete();
            }
        }
    }
}

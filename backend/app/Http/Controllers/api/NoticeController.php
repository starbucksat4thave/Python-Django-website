<?php

namespace App\Http\Controllers\api;

use id;
use App\Models\User;
use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Notifications\NoticeNotification;

class NoticeController extends Controller
{
    public function sendNoticeForApproval($record): array
    {
        try {

            $noticeId = $record->id;
            $title = $record->title;
            $content = $record->content;
            $file = $record->file;

            $notices = DB::table('notice_user')
                ->where('notice_id', $noticeId)
                ->get('user_id');

            $userIds = $notices->pluck('user_id')->toArray();

            $users = User::whereIn('id', $userIds)->get();
            foreach ($users as $user) {
                $user->notify(new NoticeNotification($noticeId, $title, $content, $file));
            }

            return ['success' => true, 'message' => 'Notice sent for approval successfully.'];
        } catch (\Exception $e) {


            return ['success' => false, 'message' => 'Failed to send notice for approval.'];
        }
    }


    public function approveNotice($noticeId): JsonResponse
    {

        $notice = Notice::find($noticeId);


        $exists = DB::table('notice_user')
            ->where('notice_id', $noticeId)
            ->get();

        if ($exists->isEmpty()) {
            return response()->json(['message' => 'User is not assigned to this notice'], 403);
        }
        foreach ($exists as $record) {
            $userId = $record->user_id;

            $pivotRecord = $notice->approvedBy()->wherePivot('user_id', $userId)->first();
            if (!$pivotRecord) {
                return response()->json(['message' => 'Pivot record not found for user ' . $userId], 400);
            }

            $notice->approvedBy()->updateExistingPivot($userId, ['is_approved' => 1]);
        }

        return response()->json(['message' => 'Notice approved successfully.']);
    }
}

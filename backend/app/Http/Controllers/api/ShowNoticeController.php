<?php

namespace App\Http\Controllers\api;

use App\Models\Notice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class ShowNoticeController extends Controller
{


    public function showAll(Request $request): JsonResponse
    {
        Log::info('show the notice');


        $departmentName = $request->query('department');
        $days = $request->query('days');


        $approvedNotices = DB::table('notice_user')
            ->select('notice_id')
            ->groupBy('notice_id')
            ->havingRaw('COUNT(user_id) = SUM(is_approved)')
            ->pluck('notice_id');

        Log::info('Approved Notices:', $approvedNotices->toArray());


        $query = Notice::with('department:id,name')
            ->whereIn('id', $approvedNotices);


        if ($departmentName) {
            $query->whereHas('department', function ($q) use ($departmentName) {
                $q->where('name', $departmentName);
            });
        }


        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }


        $notices = $query->select(['id', 'title', 'content', 'department_id', 'created_at'])
            ->paginate(10)
            ->map(function ($notice) {
                return [
                    'id' => $notice->id,
                    'title' => $notice->title,
                    'content' => $notice->content,
                    'department_name' => $notice->department->name ?? null,
                    'created_at' => $notice->created_at,
                ];
            });

        return response()->json([
            'message' => 'Filtered notices retrieved successfully.',
            'notices' => $notices,
        ], 200);
    }


    public function show($id): JsonResponse
    {
        $notice = Notice::with('department:id,name')->find($id);


        if (!$notice) {
            return response()->json([
                'message' => 'Notice not found.',
            ], 404);
        }

        return response()->json([
            'message' => 'Notice retrieved successfully.',
            'notice' => [
                'notice' => $notice,
                'department_name' => $notice->department->name ?? null,

            ],
        ], 200);
    }
}

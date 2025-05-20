<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CourseResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CourseResourceController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'course_session_id' => 'required|exists:course_sessions,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|max:2048', // Max 2MB
        ]);

        $user = Auth::user();
        if ($user->courseSessions()->where('id', $request->course_session_id)->doesntExist()) {
            return response()->json(['message' => 'You are not authorized to upload resources for this course'], 403);
        }

        $file = $request->file('file');
        $filePath = $file->store('course_materials', 'local'); // Saved in `storage/app/private/course_materials`

        $resource = CourseResource::create([
            'course_session_id' => $request->course_session_id,
            'uploaded_by' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'resource' => $resource
        ], 201);
    }

    /**
     * List all course resources for a given course session.
     */
    public function index($course_session_id)
    {
        $user = Auth::user();

        if ($user->hasRole('teacher') && $user->courseSessions()->where('id', $course_session_id)->doesntExist()) {
            return response()->json(['message' => 'You are not authorized to view these resources'], 403);
        }
        if ($user->hasRole('student') && $user->enrollments()->where('courseSession_id', $course_session_id)->doesntExist()) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }
        $resources = CourseResource::where('course_session_id', $course_session_id)->get()->makeHidden('file_path');

        return response()->json([
            'resources' => $resources
        ]);
    }

    /**
     * Download a private course resource.
     */
    public function download($id)
    {
        $resource = CourseResource::findOrFail($id);
        $user = Auth::user();
        if ($user->hasRole('teacher') && ($resource->uploaded_by !== $user->id)) {
            return response()->json(['message' => 'You are not authorized to download this file'], 403);
        }

        if ($user->hasRole('student') && $resource->courseSession->enrollments()->where('student_id', $user->id)->doesntExist()) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }
        return Storage::download($resource->file_path, $resource->file_name);
    }

    public function update(Request $request, $id)
    {
        $resource = CourseResource::findOrFail($id);
        $user = Auth::user();

        if ($user->id !== $resource->uploaded_by) {
            return response()->json(['message' => 'You are not authorized to edit this resource'], 403);
        }

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:2048', // Max 2MB
        ]);

        if ($request->hasFile('file')) {
            // Delete old file
            Storage::delete($resource->file_path);

            $file = $request->file('file');
            $filePath = $file->store('course_materials', 'local');

            $resource->file_name = $file->getClientOriginalName();
            $resource->file_path = $filePath;
            $resource->file_type = $file->getClientMimeType();
            $resource->file_size = $file->getSize();
        }

        if ($request->filled('title')) {
            $resource->title = $request->title;
        }

        if ($request->filled('description')) {
            $resource->description = $request->description;
        }

        $resource->save();

        return response()->json([
            'message' => 'Resource updated successfully',
            'resource' => $resource
        ]);
    }
    public function destroy($id)
    {
        $resource = CourseResource::findOrFail($id);
        $user = Auth::user();

        if ($user->id !== $resource->uploaded_by) {
            return response()->json(['message' => 'You are not authorized to delete this resource'], 403);
        }

        // Delete file from storage
        Storage::delete($resource->file_path);

        $resource->delete();

        return response()->json(['message' => 'Resource deleted successfully']);
    }
}

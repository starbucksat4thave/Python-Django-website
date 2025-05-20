<?php

namespace App\Http\Controllers\api;

use App\Exceptions\Application\ApplicationNotApprovedException;
use App\Exceptions\Application\AttachmentNotFoundException;
use App\Exceptions\Application\AuthorizedCopyNotFoundException;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Mpdf\Mpdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApplicationController extends Controller
{
    public function submitApplication(Request $request): JsonResponse
    {
        try {
            $request->merge([
                'placeholders' => json_decode($request->input('placeholders'), true),
            ]);
            $validated = $request->validate([
                'application_template_id' => 'required|exists:application_templates,id',
                'placeholders' => 'required|array',
                'attachment' => 'nullable|file|max:2048', // max 2MB
            ]);

            $template = ApplicationTemplate::findOrFail($validated['application_template_id']);

            // Normalize placeholder keys: lowercase and trim
            $placeholders = collect($validated['placeholders'])
                ->mapWithKeys(fn($value, $key) => [strtolower(trim($key)) => $value])
                ->toArray();

            // Replace placeholders in template body
            $body = str_replace(
                array_map(fn($k) => "%$k%", array_keys($placeholders)),
                array_values($placeholders),
                $template->body
            );

            $attachmentPath = null;

            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store(
                    'application_attachments', // folder
                    ['disk' => 'local']        // use the local disk
                );
            }

            $application = Application::create([
                'user_id' => auth()->id(),
                'application_template_id' => $template->id,
                'body' => $body,
                'attachment' => $attachmentPath,
                'status' => 'pending',
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Application submitted successfully.',
                'data' => $application,
            ], 201);
        } catch( ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], $e->getCode()?: 500);
        }
    }


    public function authorizeApplication(Request $request, $id): JsonResponse
    {
        try {
            $application = Application::with(['applicationTemplate', 'user', 'authorizedBy.department'])
                ->where('id', $id)
                ->where('authorized_by', auth()->id())
                ->where('status', 'pending')
                ->firstOrFail();

            $action = $request->input('action'); // 'approve' or 'reject'

            if ($action === 'reject') {
                $application->update([
                    'status' => 'rejected',
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Application rejected.',
                ]);
            }

            // Generate PDF using Blade view
            $html = view('application.application', ['application' => $application])->render();

            $mpdf = new Mpdf();
            $mpdf->WriteHTML($html);

            $filename = 'authorized_applications/' . Str::uuid() . '.pdf';
            Storage::disk('local')->put($filename, $mpdf->Output('', 'S'));

            $application->update([
                'status' => 'approved',
                'authorized_copy' => $filename,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Application approved and PDF generated.',
                'data' => $application,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to process application.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPendingApplications(): JsonResponse
    {
        try {
            $applications = Application::with('user', 'applicationTemplate')
                ->where('authorized_by', auth()->id())
                ->where('status', 'pending')
                ->latest()
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $applications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch pending applications.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function downloadAuthorizedCopy($id): BinaryFileResponse|JsonResponse
    {
        try {
            $application = Application::findOrFail($id);
            // authorize the user though the policy
            $this->authorize('canDownloadApplication', $application);

            if ($application->status !== 'approved' || !$application->authorized_copy) {
                throw new ApplicationNotApprovedException('Application is not yet approved or missing file.',403);
            }

            $filePath = Storage::disk('local')->path($application->authorized_copy);

            if (!file_exists($filePath)) {
                throw new AuthorizedCopyNotFoundException('Authorized copy not found on server.',404);
            }

            return response()->download($filePath);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to download file.',
                'error' => $e->getMessage(),
            ], $e->getCode()?:500);
        }
    }
    public function downloadAttachment($id): BinaryFileResponse|JsonResponse
    {
        try {
            $application = Application::findOrFail($id);

            // Authorize using policy (e.g., user can download their own application)
            $this->authorize('canDownloadAttachment', $application);

            if (!$application->attachment || !Storage::exists($application->attachment)) {
                throw new AttachmentNotFoundException('Attachment not found.',404);
            }

            $filePath = Storage::disk('local')->path($application->attachment);

            return response()->download($filePath);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to download attachment.',
                'error' => $e->getMessage(),
            ], $e->getCode()?:500);
        }
    }

    //For students
    public function getMyApplications(): JsonResponse
    {
        try {
            $applications = Application::with(['applicationTemplate', 'authorizedBy'])
                ->where('user_id', auth()->id())
                ->latest()
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $applications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch applications.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //For Teachers
    public function authorizedApplications(): JsonResponse
    {
        try {
            $applications = Application::with(['applicationTemplate', 'user'])
                ->where('authorized_by', auth()->id())
                ->whereIn('status', ['approved', 'rejected'])
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $applications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch authorized applications.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}

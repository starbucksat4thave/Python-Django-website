<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use JetBrains\PhpStorm\NoReturn;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Mpdf\Mpdf;

class IdCardController extends Controller
{
    public function generateIdCard()
    {
        try {
            $user = Auth::user();
            $user->load('roles', 'department');

            // Check if the user already has a hashed ID, if not, create and save it
            if (empty($user->hashed_id)) {
                $user->hashed_id = Hash::make($user->id);
                $user->save();
            }

            // Generate a QR Code for verification using the hashed ID
            $verificationUrl = route('verify', ['id' => $user->hashed_id]);  // Use hashed_id for verification
            $qrCode = QrCode::format('svg')->size(90)->generate($verificationUrl);

            // Manually remove unwanted text (e.g., xmlns, XML declarations)
            $qrCode = preg_replace('/<\?xml.*\?>/', '', $qrCode); // Remove XML declaration
            $qrCode = preg_replace('/xmlns.*?=".*?" /', '', $qrCode); // Remove xmlns attribute

            // Render the Blade view as a string with QR Code
            $html = view('id-card.idcard', compact('user', 'qrCode'))->render();

            // Initialize mPDF
            $mpdf = new Mpdf();

            // Write HTML to mPDF
            $mpdf->WriteHTML($html);

            $pdfContent = $mpdf->Output('', 'S'); // 'S' returns as string

            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="ID_Card_'.$user->id.'.pdf"')
                ->header('Access-Control-Expose-Headers', 'Content-Disposition');
        } catch (\Throwable $e) {
            // Handle exceptions (e.g., log the error, return a response)
            return response()->json(['error' => 'Failed to generate ID card.'], 500);
        }

    }

    public function verify($id)
    {
        // Find the user by hashed ID
        $user = User::where('hashed_id', $id)->first();

        if (!$user) {
            return view('id-card.invalid'); // A Blade view for invalid IDs
        }

        return view('id-card.verified', compact('user')); // A Blade view for valid users
    }
}

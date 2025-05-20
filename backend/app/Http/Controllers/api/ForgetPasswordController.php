<?php
namespace App\Http\Controllers\api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;


class ForgetPasswordController extends Controller
{
    public function resetPassword(Request $request)
    {
           try{
             $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);


            $status = Password::sendResetLink($request->only('email'));

            return $status === Password::RESET_LINK_SENT
                        ? response()->json(['message' => __($status)],201)
                        : response()->json(['message' => __($status)], 400);


           }
           catch (ValidationException $e) {
            return response()->json(['message' => 'Invalid email', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Something went wrong', 'error' => $e->getMessage()], 500);
        }
    }


}

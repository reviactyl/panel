<?php

namespace App\Http\Controllers\Admin\Settings;

use Illuminate\Mail\Message;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * Send a test email to the currently authenticated administrator
     * to verify that mail settings are configured correctly.
     */
    public function test(): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            Mail::raw(
                'This is a test email sent from ' . config('app.name') . ' to confirm that your mail configuration is working correctly.',
                function (Message $message) use ($user) {
                    $message->to($user->email, $user->name_first . ' ' . $user->name_last)
                        ->subject('[' . config('app.name') . '] Test Email');
                }
            );
        } catch (\Exception $exception) {
            return new JsonResponse([
                'error' => 'Failed to send test email: ' . $exception->getMessage(),
            ], 500);
        }

        return new JsonResponse([], 204);
    }
}

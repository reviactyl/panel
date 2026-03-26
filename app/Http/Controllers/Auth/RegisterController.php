<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Rules\Username;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Services\Users\UserCreationService;
use Illuminate\Validation\ValidationException;
use App\Exceptions\Model\DataValidationException;

class RegisterController extends AbstractLoginController
{
    /**
     * RegisterController constructor.
     */
    public function __construct(private UserCreationService $creationService)
    {
        parent::__construct();
    }

    /**
     * Handle the registration page request.
     */
    public function index(): View
    {
        return view('templates/auth.core');
    }

    /**
     * Handle a registration request for the application.
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'username' => ['required', 'string', 'min:3', 'unique:users,username', new Username()],
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user = $this->creationService->handle([
                'email' => $request->input('email'),
                'username' => $request->input('username'),
                'name_first' => $request->input('first_name'),
                'name_last' => $request->input('last_name'),
                'password' => $request->input('password'),
                'root_admin' => false,
            ]);
        } catch (DataValidationException $exception) {
            throw new ValidationException($exception->getValidator());
        }

        // Auto-login the user after registration
        return $this->sendLoginResponse($user, $request);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $username = $this->createUsernameFromEmail($request->email);

        $user = User::create([
            'username' => $username,
            'avatar' => 'avatars/default.png',
            'role' => 'user',
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }

    /**
     * Create username from email
     *
     * @param string $email
     * @return string
     */
    private function createUsernameFromEmail(string $email): string
    {
        // Get the username part of the email
        $username = explode('@', $email)[0];

        // Remove all special characters from the username
        $username = preg_replace('/[^a-zA-Z0-9]/', '', $username);

        // Check if the username already exists
        $originalUsername = $username;
        $count = 1;

        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $count;
            $count++;
        }

        return $username;
    }
}

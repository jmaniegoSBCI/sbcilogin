<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProfileSetupController extends Controller
{
    public function edit(): View|RedirectResponse
    {
        $user = auth()->user();
        abort_unless($user->role === User::ROLE_STUDENT, Response::HTTP_FORBIDDEN);

        if ($user->profileIsComplete()) {
            return redirect()->route('student.dashboard');
        }

        return view('student.profile-setup');
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->role === User::ROLE_STUDENT, Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'program' => ['required', 'in:BSIT,CRIM,BSCA,BSTM'],
            'year_level' => ['required', 'integer', 'between:1,6'],
        ]);

        $user = $request->user();
        $user->fill($validated);
        $user->name = trim($validated['first_name'].' '.$validated['last_name']);
        $user->save();

        return redirect()->route('student.dashboard')->with('status', 'Your student profile has been saved.');
    }
}

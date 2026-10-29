<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvitationRequest;
use App\Models\Invitation;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\AdminInvitation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Throwable;

class TeamController extends Controller
{
    public function index(): View
    {
        return view('admin.team', [
            'ownerId' => Profile::value('user_id'),
            'admins' => User::orderBy('id')->get()->filter(fn (User $user): bool => $user->loginMethod() !== null),
            'invitations' => Invitation::latest()->get(),
        ]);
    }

    public function invite(StoreInvitationRequest $request): RedirectResponse
    {
        $invitation = Invitation::create($request->validated());

        try {
            Notification::route('mail', $invitation->email)->notify(new AdminInvitation($request->user()->name));
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', "Invitation saved, but the email could not be sent. Share the login link with {$invitation->email} instead.");
        }

        return back()->with('success', "Invitation sent to {$invitation->email}.");
    }

    public function revoke(Invitation $invitation): RedirectResponse
    {
        $invitation->delete();

        return back()->with('success', 'Invitation revoked.');
    }

    public function remove(User $user): RedirectResponse
    {
        abort_if(Profile::where('user_id', $user->id)->exists(), 403, 'The owner cannot be removed.');

        $user->delete();

        return back()->with('success', "{$user->name} no longer has access.");
    }

    public function transfer(User $user): RedirectResponse
    {
        abort_if($user->loginMethod() === null, 404);

        Profile::firstOrFail()->transferTo($user);

        return redirect()->route('admin.dashboard')->with('success', "{$user->name} now owns this DotBio.");
    }
}

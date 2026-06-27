<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function __construct(private AuditService $auditService) {}

    public function start(Request $request, User $user)
    {
        // Only super-admin can impersonate, and cannot impersonate another admin
        abort_if(! auth()->user()->isSuperAdmin(), 403);
        abort_if($user->isSuperAdmin(), 403, 'Cannot impersonate another admin.');

        $originalId = auth()->id();

        // Store original admin ID in session before switching
        session([
            'impersonating_original_id' => $originalId,
            'impersonating_user_id'     => $user->id,
        ]);

        $this->auditService->log('impersonation.started', $user, [
            'impersonated_user'  => $user->name,
            'impersonated_email' => $user->email,
        ], $user->ownedHotels()->value('id'), $originalId);

        Auth::loginUsingId($user->id);

        return redirect()->route('owner.dashboard')
            ->with('info', "You are now viewing the platform as {$user->name}. Click 'Stop Impersonating' in the top bar to return.");
    }

    public function stop(Request $request)
    {
        $originalId       = session('impersonating_original_id');
        $impersonatedUser = auth()->user();

        abort_if(! $originalId, 403, 'No active impersonation session.');

        $this->auditService->log('impersonation.stopped', $impersonatedUser, [
            'impersonated_user'  => $impersonatedUser->name,
            'impersonated_email' => $impersonatedUser->email,
        ], null, $originalId);

        session()->forget(['impersonating_original_id', 'impersonating_user_id']);

        Auth::loginUsingId($originalId);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Impersonation ended. You are back as the platform admin.');
    }
}

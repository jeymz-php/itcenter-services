<?php
namespace App\Services;

use App\Models\Admin;
use App\Models\AdminNotification;
use App\Models\ComputerSession;
use App\Models\GuestComputerSession;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;

class ComputerSessionMonitor
{
    public function expireDueSessionsForAdmin(Admin $admin): int
    {
        $expired = 0;

        $registered = ComputerSession::query()
            ->whereIn('status', ['active', 'extended'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now());

        $guests = GuestComputerSession::query()
            ->whereIn('status', ['active', 'extended'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now());

        if ($admin->role !== 'super_admin') {
            $registered->whereHas('serviceRequest.user', fn ($query) => $query->where('campus', $admin->campus));
            $guests->whereHas('guestRequest', fn ($query) => $query->where('campus', $admin->campus));
        }

        $registered->pluck('id')->each(function (int $id) use (&$expired) {
            if ($this->expireRegisteredSession($id)) {
                $expired++;
            }
        });

        $guests->pluck('id')->each(function (int $id) use (&$expired) {
            if ($this->expireGuestSession($id)) {
                $expired++;
            }
        });

        return $expired;
    }

    public function expireDueSessionsForUser(User $user): int
    {
        $ids = ComputerSession::where('user_id', $user->id)
            ->whereIn('status', ['active', 'extended'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->pluck('id');

        $expired = 0;
        foreach ($ids as $id) {
            if ($this->expireRegisteredSession((int) $id)) {
                $expired++;
            }
        }

        return $expired;
    }

    public function expireRegisteredSession(int $sessionId): bool
    {
        return DB::transaction(function () use ($sessionId) {
            $session = ComputerSession::with(['computer', 'serviceRequest.user'])
                ->lockForUpdate()
                ->find($sessionId);

            if (!$session
                || !in_array($session->status, ['active', 'extended'], true)
                || !$session->ends_at
                || $session->ends_at->isFuture()) {
                return false;
            }

            $request = $session->serviceRequest;
            $user = $request?->user;
            $computerName = $session->computer?->name ?? 'the assigned PC';

            $session->update([
                'status'   => 'expired',
                'ended_at' => now(),
            ]);

            if ($session->computer_id) {
                $this->releaseComputerIfUnused($session->computer_id);
            }

            if ($request && $request->status !== 'completed') {
                $request->update(['status' => 'completed']);
            }

            if ($user && $request) {
                UserNotification::notify(
                    $user,
                    'session_expired',
                    'PC Session Finished',
                    "Your Research / PC Lab session for {$request->request_number} on {$computerName} has ended because the occupied time ran out.",
                    route('requests.show', $request),
                    'fa-hourglass-end'
                );

                AdminNotification::notify(
                    'session_expired',
                    'PC Occupied Time Finished',
                    "{$user->full_name}'s Research / PC Lab session on {$computerName} has ended automatically. The PC is now available.",
                    $user,
                    null,
                    'fa-hourglass-end'
                );
            }

            return true;
        });
    }

    public function expireGuestSession(int $sessionId): bool
    {
        return DB::transaction(function () use ($sessionId) {
            $session = GuestComputerSession::with(['computer', 'guestRequest'])
                ->lockForUpdate()
                ->find($sessionId);

            if (!$session
                || !in_array($session->status, ['active', 'extended'], true)
                || !$session->ends_at
                || $session->ends_at->isFuture()) {
                return false;
            }

            $request = $session->guestRequest;
            $computerName = $session->computer?->name ?? 'the assigned PC';

            $session->update([
                'status'   => 'expired',
                'ended_at' => now(),
            ]);

            if ($session->computer_id) {
                $this->releaseComputerIfUnused($session->computer_id);
            }

            if ($request && $request->status !== 'completed') {
                $request->update(['status' => 'completed']);
            }

            if ($request) {
                AdminNotification::create([
                    'type'            => 'session_expired',
                    'title'           => 'Guest PC Occupied Time Finished',
                    'message'         => "{$request->full_name}'s Research / PC Lab session on {$computerName} has ended automatically. The PC is now available.",
                    'notifiable_id'   => $request->id,
                    'notifiable_type' => get_class($request),
                    'campus'          => $request->campus,
                    'action_url'      => null,
                    'icon'            => 'fa-hourglass-end',
                ]);
            }

            return true;
        });
    }

    private function releaseComputerIfUnused(int $computerId): void
    {
        $registeredActive = ComputerSession::where('computer_id', $computerId)
            ->whereIn('status', ['active', 'extended'])
            ->exists();

        $guestActive = GuestComputerSession::where('computer_id', $computerId)
            ->whereIn('status', ['active', 'extended'])
            ->exists();

        if (!$registeredActive && !$guestActive) {
            \App\Models\Computer::whereKey($computerId)->update(['status' => 'available']);
        }
    }
}

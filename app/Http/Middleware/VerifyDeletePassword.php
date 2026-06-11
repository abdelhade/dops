<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyDeletePassword
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->requiresDeletePassword($request)) {
            return $next($request);
        }

        if (! AppSetting::isDeletePasswordConfigured()) {
            return $this->reject($request, __('dobs.delete_password_not_configured'));
        }

        if (! AppSetting::verifyDeletePassword($request->input('delete_password'))) {
            return $this->reject($request, __('dobs.delete_password_invalid'));
        }

        return $next($request);
    }

    private function requiresDeletePassword(Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($request->routeIs('clients.bulk-destroy')) {
            return $user->canDeleteRecords();
        }

        if ($request->routeIs('users.destroy')) {
            return $user->canManageUsers();
        }

        if ($request->isMethod('DELETE')) {
            return $user->canDeleteRecords();
        }

        return false;
    }

    private function reject(Request $request, string $message): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        return redirect()
            ->back()
            ->withInput()
            ->with('error', $message);
    }
}

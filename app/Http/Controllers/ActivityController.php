<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\OperationLog;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(): View
    {
        $logs = OperationLog::query()
            ->with(['user', 'operation'])
            ->latest()
            ->paginate(30);

        return view('activities.index', compact('logs'));
    }
}

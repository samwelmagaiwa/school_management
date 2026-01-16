<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(Qs::userIsTeamSA(), 403);

        $logsQuery = ActivityLog::with('user')->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $logsQuery->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('method')) {
            $logsQuery->where('method', strtoupper($request->input('method')));
        }

        if ($request->filled('search')) {
            $term = '%'.$request->input('search').'%';
            $logsQuery->where(function ($query) use ($term) {
                $query->where('action', 'like', $term)
                    ->orWhere('description', 'like', $term)
                    ->orWhere('route', 'like', $term);
            });
        }

        if ($request->filled('date_from')) {
            $logsQuery->whereDate('created_at', '>=', Carbon::parse($request->input('date_from'))->startOfDay());
        }

        if ($request->filled('date_to')) {
            $logsQuery->whereDate('created_at', '<=', Carbon::parse($request->input('date_to'))->endOfDay());
        }

        $logs = $logsQuery->paginate(25)->appends($request->query());

        $users = User::orderBy('name')->get(['id', 'name', 'user_type', 'last_seen_at']);

        $onlineUsers = $users->filter(fn ($user) => Cache::has($this->cacheKey($user->id)));
        $offlineUsers = $users->diff($onlineUsers);

        return view('pages.admin.logs.index', [
            'logs' => $logs,
            'users' => $users,
            'filters' => $request->only(['user_id', 'method', 'search', 'date_from', 'date_to']),
            'onlineUsers' => $onlineUsers,
            'offlineUsers' => $offlineUsers,
        ]);
    }

    protected function cacheKey(int $userId): string
    {
        return sprintf('user-online-%d', $userId);
    }
}

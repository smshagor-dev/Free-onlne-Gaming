<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;

class LogController extends Controller
{
    private function paginateLog($path, $perPage = 20)
    {
        if (!File::exists($path)) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $logContent = File::get($path);
        $lines = explode("\n", trim($logContent));
        $lines = array_reverse($lines); // latest logs first

        $page = request()->get('page', 1);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($lines, $offset, $perPage);

        return new LengthAwarePaginator(
            $items,
            count($lines),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    // Laravel Logs
    public function index()
    {
        $path = storage_path('logs/laravel.log');
        $logs = $this->paginateLog($path);
        return view('admin.logs.index', compact('logs'));
    }

    public function clearLaravelLog()
    {
        $path = storage_path('logs/laravel.log');
        File::put($path, '');
        return redirect()->route('admin.logs.index')->with('status', 'Laravel log cleared successfully!');
    }

    // Scheduler Logs
    public function scheduler()
    {
        $path = storage_path('logs/scheduler.log');
        $logs = $this->paginateLog($path);
        return view('admin.logs.scheduler', compact('logs'));
    }

    public function clearSchedulerLog()
    {
        $path = storage_path('logs/scheduler.log');
        File::put($path, '');
        return redirect()->route('admin.scheduler.logs.index')->with('status', 'Scheduler log cleared!');
    }
}

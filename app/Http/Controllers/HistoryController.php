<?php

namespace App\Http\Controllers;

use App\Models\EditHistory;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;

class HistoryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'module' => ['nullable', 'string', 'max:40'],
            'user_name' => ['nullable', 'string', 'max:255'],
            'edited_part' => ['nullable', 'string', 'max:40'],
            'action' => ['nullable', 'string', 'max:30'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        if (!Schema::hasTable('edit_histories')) {
            $histories = new LengthAwarePaginator(
                items: [],
                total: 0,
                perPage: 25,
                currentPage: max(1, (int) $request->query('page', 1)),
                options: [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );

            $modules = collect();
            $users = collect();
            $editedParts = collect();
            $actions = collect();

            return view('admin.history', compact('histories', 'filters', 'modules', 'users', 'editedParts', 'actions'));
        }

        $historyQuery = EditHistory::query()
            ->when(!empty($filters['module']), function ($query) use ($filters) {
                $query->where('module', $filters['module']);
            })
            ->when(!empty($filters['user_name']), function ($query) use ($filters) {
                $query->where('user_name', $filters['user_name']);
            })
            ->when(!empty($filters['edited_part']), function ($query) use ($filters) {
                $query->where('edited_part', $filters['edited_part']);
            })
            ->when(!empty($filters['action']), function ($query) use ($filters) {
                $query->where('action', $filters['action']);
            })
            ->when(!empty($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('created_at', '>=', $filters['date_from']);
            })
            ->when(!empty($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('created_at', '<=', $filters['date_to']);
            });

        $histories = $historyQuery
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $modules = EditHistory::query()->select('module')->distinct()->orderBy('module')->pluck('module');
        $users = EditHistory::query()->whereNotNull('user_name')->select('user_name')->distinct()->orderBy('user_name')->pluck('user_name');
        $editedParts = EditHistory::query()->select('edited_part')->distinct()->orderBy('edited_part')->pluck('edited_part');
        $actions = EditHistory::query()->select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.history', compact('histories', 'filters', 'modules', 'users', 'editedParts', 'actions'));
    }
}

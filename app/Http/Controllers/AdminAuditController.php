<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAuditController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();
        $action = $request->string('action')->trim()->value();
        $entries = AuditLog::with('actor')->when($search, fn ($q) => $q->where('action', 'like', "%{$search}%"))->when($action, fn ($q) => $q->where('action', $action))->latest()->paginate(25)->withQueryString();
        $actions = AuditLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('admin.audit.index', compact('entries', 'actions', 'search', 'action'));
    }
}

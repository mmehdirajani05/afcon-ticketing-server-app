<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::with('author');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        $announcements = $query->latest()->paginate(config('admin.per_page'))->withQueryString();

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
            'type'         => ['required', 'in:info,warning,success,danger'],
            'status'       => ['required', 'in:draft,published,archived'],
            'is_pinned'    => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        Announcement::create([
            ...$data,
            'is_pinned'  => $request->boolean('is_pinned'),
            'created_by' => Auth::id(),
            'published_at' => $data['status'] === 'published' ? ($data['published_at'] ?? now()) : ($data['published_at'] ?? null),
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'body'         => ['required', 'string'],
            'type'         => ['required', 'in:info,warning,success,danger'],
            'status'       => ['required', 'in:draft,published,archived'],
            'is_pinned'    => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $announcement->update([
            ...$data,
            'is_pinned' => $request->boolean('is_pinned'),
        ]);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted.');
    }
}

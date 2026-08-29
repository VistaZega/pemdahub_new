<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::with('author')->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($request->get('status') === 'published') {
            $query->where('is_published', true);
        } elseif ($request->get('status') === 'draft') {
            $query->where('is_published', false);
        }

        $news = $query->paginate(10)->withQueryString();

        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        return view('admin.news.form', ['news' => new News()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'excerpt'       => 'nullable|string|max:500',
            'content'       => 'nullable|string',
            'category'      => 'required|in:prestasi,kegiatan,kerjasama,pengumuman',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'icon'          => 'nullable|string|max:100',
            'gradient_from' => 'nullable|string|max:20',
            'gradient_to'   => 'nullable|string|max:20',
            'is_published'  => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('news', 'public');
        }

        $validated['author_id'] = auth()->id();
        $validated['is_published'] = $request->boolean('is_published');

        if ($validated['is_published']) {
            $validated['published_at'] = now();
        }

        // Set defaults for icon/gradient based on category
        if (empty($validated['icon'])) {
            $validated['icon'] = match ($validated['category']) {
                'prestasi'   => 'fa-solid fa-trophy',
                'kegiatan'   => 'fa-solid fa-users',
                'kerjasama'  => 'fa-solid fa-handshake',
                'pengumuman' => 'fa-solid fa-bullhorn',
                default      => 'fa-solid fa-newspaper',
            };
        }

        if (empty($validated['gradient_from'])) {
            $validated['gradient_from'] = match ($validated['category']) {
                'prestasi'   => '#2563eb',
                'kegiatan'   => '#059669',
                'kerjasama'  => '#d97706',
                'pengumuman' => '#7c3aed',
                default      => '#2563eb',
            };
            $validated['gradient_to'] = match ($validated['category']) {
                'prestasi'   => '#60a5fa',
                'kegiatan'   => '#34d399',
                'kerjasama'  => '#fbbf24',
                'pengumuman' => '#a78bfa',
                default      => '#60a5fa',
            };
        }

        News::create($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'Berita berhasil ditambahkan!');
    }

    public function edit(News $news)
    {
        return view('admin.news.form', compact('news'));
    }

    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'excerpt'       => 'nullable|string|max:500',
            'content'       => 'nullable|string',
            'category'      => 'required|in:prestasi,kegiatan,kerjasama,pengumuman',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'icon'          => 'nullable|string|max:100',
            'gradient_from' => 'nullable|string|max:20',
            'gradient_to'   => 'nullable|string|max:20',
            'is_published'  => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($news->image) {
                Storage::disk('public')->delete($news->image);
            }
            $validated['image'] = $request->file('image')->store('news', 'public');
        }

        $validated['is_published'] = $request->boolean('is_published');

        if ($validated['is_published'] && !$news->published_at) {
            $validated['published_at'] = now();
        } elseif (!$validated['is_published']) {
            $validated['published_at'] = null;
        }

        $news->update($validated);

        return redirect()->route('admin.news.index')
            ->with('success', 'Berita berhasil diperbarui!');
    }

    public function destroy(News $news)
    {
        if ($news->image) {
            Storage::disk('public')->delete($news->image);
        }

        $news->delete();

        return redirect()->route('admin.news.index')
            ->with('success', 'Berita berhasil dihapus!');
    }

    public function togglePublish(News $news)
    {
        $news->update([
            'is_published' => !$news->is_published,
            'published_at' => !$news->is_published ? now() : null,
        ]);

        $status = $news->is_published ? 'dipublikasikan' : 'dijadikan draft';
        return back()->with('success', "Berita berhasil {$status}!");
    }
}

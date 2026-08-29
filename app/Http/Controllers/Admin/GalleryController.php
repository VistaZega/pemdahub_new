<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = GalleryItem::ordered();

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $galleryItems = $query->paginate(12)->withQueryString();

        return view('admin.gallery.index', compact('galleryItems'));
    }

    public function create()
    {
        return view('admin.gallery.form', ['item' => new GalleryItem()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'caption'     => 'nullable|string|max:500',
            'image'       => 'required|image|mimes:jpg,jpeg,png,webp|max:10240',
            'category'    => 'required|in:upacara,praktikum,olahraga,seni,bengkel,prestasi,komputer,lainnya',
            'sort_order'  => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'is_active'   => 'boolean',
        ]);

        $validated['image'] = $request->file('image')->store('gallery', 'public');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        GalleryItem::create($validated);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Item galeri berhasil ditambahkan!');
    }

    public function edit(GalleryItem $gallery)
    {
        return view('admin.gallery.form', ['item' => $gallery]);
    }

    public function update(Request $request, GalleryItem $gallery)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'caption'     => 'nullable|string|max:500',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
            'category'    => 'required|in:upacara,praktikum,olahraga,seni,bengkel,prestasi,komputer,lainnya',
            'sort_order'  => 'nullable|integer|min:0',
            'is_featured' => 'boolean',
            'is_active'   => 'boolean',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($gallery->image);
            $validated['image'] = $request->file('image')->store('gallery', 'public');
        }

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $gallery->update($validated);

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Item galeri berhasil diperbarui!');
    }

    public function destroy(GalleryItem $gallery)
    {
        Storage::disk('public')->delete($gallery->image);
        $gallery->delete();

        return redirect()->route('admin.gallery.index')
            ->with('success', 'Item galeri berhasil dihapus!');
    }
}

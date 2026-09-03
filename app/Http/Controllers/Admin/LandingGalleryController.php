<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LandingGalleryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $galleries = LandingGallery::orderBy('order')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.landing-galleries.index', compact('galleries', 'perPage'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'caption' => 'nullable|string|max:500',
            'image'   => 'required|image|max:3072',
            'order'   => 'nullable|integer',
        ]);

        $data['order'] = $data['order'] ?? 0;
        $data['image'] = $request->file('image')->store('gallery', 'public');

        LandingGallery::create($data);
        logAktivitas('Kelola Galeri', 'Menambah foto galeri baru: ' . $data['title']);
        return back()->with('success', 'Foto galeri berhasil ditambahkan!');
    }

    public function update(Request $request, LandingGallery $landingGallery)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'caption' => 'nullable|string|max:500',
            'image'   => 'nullable|image|max:3072',
            'order'   => 'nullable|integer',
        ]);

        $data['order'] = $data['order'] ?? 0;

        if ($request->hasFile('image')) {
            // Delete old image
            if ($landingGallery->image) {
                Storage::disk('public')->delete($landingGallery->image);
            }
            $data['image'] = $request->file('image')->store('gallery', 'public');
        }

        $landingGallery->update($data);
        logAktivitas('Kelola Galeri', 'Memperbarui foto galeri: ' . $landingGallery->title);
        return back()->with('success', 'Foto galeri berhasil diperbarui!');
    }

    public function destroy(LandingGallery $landingGallery)
    {
        $title = $landingGallery->title;
        if ($landingGallery->image) {
            Storage::disk('public')->delete($landingGallery->image);
        }
        $landingGallery->delete();
        logAktivitas('Kelola Galeri', 'Menghapus foto galeri: ' . $title);
        return back()->with('success', 'Foto galeri berhasil dihapus!');
    }
}

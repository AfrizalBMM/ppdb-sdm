<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LandingFaq;
use Illuminate\Http\Request;

class LandingFaqController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $faqs = LandingFaq::orderBy('order')
            ->paginate($perPage)
            ->withQueryString();
        return view('admin.landing-faqs.index', compact('faqs', 'perPage'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:500',
            'answer'   => 'required|string',
            'order'    => 'nullable|integer',
        ]);

        $data['order'] = $data['order'] ?? 0;

        LandingFaq::create($data);
        logAktivitas('Kelola FAQ', 'Menambah FAQ baru: ' . $data['question']);
        return back()->with('success', 'FAQ berhasil ditambahkan!');
    }

    public function update(Request $request, LandingFaq $landingFaq)
    {
        $data = $request->validate([
            'question' => 'required|string|max:500',
            'answer'   => 'required|string',
            'order'    => 'nullable|integer',
        ]);

        $data['order'] = $data['order'] ?? 0;

        $landingFaq->update($data);
        logAktivitas('Kelola FAQ', 'Memperbarui FAQ: ' . $landingFaq->question);
        return back()->with('success', 'FAQ berhasil diperbarui!');
    }

    public function destroy(LandingFaq $landingFaq)
    {
        $question = $landingFaq->question;
        $landingFaq->delete();
        logAktivitas('Kelola FAQ', 'Menghapus FAQ: ' . $question);
        return back()->with('success', 'FAQ berhasil dihapus!');
    }
}

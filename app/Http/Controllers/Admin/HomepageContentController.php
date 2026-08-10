<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomepageContentController extends Controller
{
    public function index()
    {
        $settings = [
            // Statistik
            'stat_tahun' => Setting::getValue('stat_tahun', '1970'),
            'stat_siswa' => Setting::getValue('stat_siswa', '1700'),
            'stat_unit' => Setting::getValue('stat_unit', '3'),
            'stat_program' => Setting::getValue('stat_program', '5'),
            
            // Sambutan Ketua
            'ketua_nama' => Setting::getValue('ketua_nama', "Yulianus Zega, S.Kom, M.Pd.T"),
            'ketua_jabatan' => Setting::getValue('ketua_jabatan', "Ketua Yayasan Perguruan PEMBDA Nias"),
            'ketua_quote' => Setting::getValue('ketua_quote', "Selamat datang di PembdaHUB, platform ekosistem pendidikan digital masa depan Yayasan Perguruan PEMBDA Nias. Perguruan PEMBDA berkomitmen penuh melahirkan generasi emas Kepulauan Nias yang tidak hanya tangguh dan cerdas secara akademis, tetapi juga memiliki integritas karakter yang mulia serta menguasai teknologi modern secara profesional.\n\nSelaras dengan motto abadi perjuangan kami: 'Keep Moving Forward / Maju Terus Pantang Mundur', kami terus berinovasi tanpa henti membangun lingkungan belajar berbasis teknologi digital terkini untuk menjawab tantangan era globalisasi.\n\nMari bersama-sama kita bergandengan tangan—pendidik, siswa, orang tua, dan alumni—melangkah pasti mewujudkan masa depan Nias yang gemilang, berdaya saing tinggi, dan berintegritas!"),
            
            // PSB / Pendaftaran
            'psb_tp' => Setting::getValue('psb_tp', '2026/2027'),
            'psb_periode' => Setting::getValue('psb_periode', '1 Feb – 30 Jun 2026'),
            'psb_status' => Setting::getValue('psb_status', 'Dibuka'),
        ];

        return view('admin.homepage.content', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            // Statistik
            'stat_tahun' => 'required|string',
            'stat_siswa' => 'required|string',
            'stat_unit' => 'required|string',
            'stat_program' => 'required|string',
            
            // Sambutan Ketua
            'ketua_nama' => 'required|string|max:255',
            'ketua_jabatan' => 'required|string|max:255',
            'ketua_quote' => 'required|string',
            
            // PSB
            'psb_tp' => 'required|string|max:50',
            'psb_periode' => 'required|string|max:100',
            'psb_status' => 'required|string|max:50',
        ]);

        foreach ($validated as $key => $value) {
            Setting::setValue($key, $value, 'string', 'homepage');
        }

        return redirect()->route('admin.homepage-content.index')
            ->with('success', 'Konten umum homepage berhasil diperbarui!');
    }
}

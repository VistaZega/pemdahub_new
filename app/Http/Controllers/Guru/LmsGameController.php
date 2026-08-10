<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\LmsGame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LmsGameController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:lms_courses,id',
            'module_id' => 'required|exists:lms_modules,id',
            'title' => 'required|string|max:255',
            'game_type' => 'required|in:spin_wheel,flashcard,match,quiz,true_false,word_guess,scramble,sequence,image_hotspot,chem_balancer,math_ninja',
            'reward_points' => 'required|integer|min:0|max:1000',
            'game_data' => 'required|string',
            'time_limit' => 'nullable|integer|min:5|max:300',
            'lives_count' => 'nullable|integer|min:1|max:10',
            'hotspot_image' => 'nullable|image|max:2048',
        ]);

        $gameData = json_decode($request->game_data, true) ?? [];
        if (!$gameData && $request->game_type !== 'image_hotspot') {
            return back()->with('error', 'Data game tidak valid.');
        }

        if ($request->game_type === 'image_hotspot' && $request->hasFile('hotspot_image')) {
            $path = $request->file('hotspot_image')->store('games/hotspots', 'public');
            $gameData['image_url'] = '/storage/' . $path;
        }

        $hasItems = false;
        if ($request->game_type === 'spin_wheel' && !empty($gameData['items'])) $hasItems = true;
        if (in_array($request->game_type, ['flashcard', 'match']) && !empty($gameData['pairs'])) $hasItems = true;
        if ($request->game_type === 'quiz' && !empty($gameData['questions'])) $hasItems = true;
        if ($request->game_type === 'true_false' && !empty($gameData['statements'])) $hasItems = true;
        if (in_array($request->game_type, ['word_guess', 'scramble']) && !empty($gameData['words'])) $hasItems = true;
        if ($request->game_type === 'sequence' && (!empty($gameData['items']) || !empty($gameData['groups']))) $hasItems = true;
        if ($request->game_type === 'image_hotspot' && !empty($gameData['image_url']) && !empty($gameData['hotspots'])) $hasItems = true;
        if ($request->game_type === 'chem_balancer' && !empty($gameData['equations'])) $hasItems = true;
        if ($request->game_type === 'math_ninja' && !empty($gameData['config'])) $hasItems = true;

        if (!$hasItems) {
            return back()->with('error', 'Gagal menyimpan: Anda harus mengisi minimal 1 item (soal / kata / data) yang valid dan tidak boleh kosong!');
        }

        LmsGame::create([
            'created_by' => Auth::id(),
            'course_id' => $request->course_id,
            'module_id' => $request->module_id,
            'title' => $request->title,
            'game_type' => $request->game_type,
            'reward_points' => $request->reward_points,
            'time_limit' => $request->time_limit,
            'lives_count' => $request->lives_count,
            'game_data' => $gameData,
            'is_published' => true,
        ]);

        return back()->with('success', 'Game berhasil ditambahkan ke modul!');
    }

    public function update(Request $request, LmsGame $lms_game)
    {
        if ($lms_game->created_by !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'module_id' => 'required|exists:lms_modules,id',
            'title' => 'required|string|max:255',
            'game_type' => 'required|in:spin_wheel,flashcard,match,quiz,true_false,word_guess,scramble,sequence,image_hotspot,chem_balancer,math_ninja',
            'reward_points' => 'required|integer|min:0|max:1000',
            'game_data' => 'required|string',
            'time_limit' => 'nullable|integer|min:5|max:300',
            'lives_count' => 'nullable|integer|min:1|max:10',
            'hotspot_image' => 'nullable|image|max:2048',
        ]);

        $gameData = json_decode($request->game_data, true) ?? [];
        if (!$gameData && $request->game_type !== 'image_hotspot') {
            return back()->with('error', 'Data game tidak valid.');
        }

        if ($request->game_type === 'image_hotspot' && $request->hasFile('hotspot_image')) {
            $path = $request->file('hotspot_image')->store('games/hotspots', 'public');
            $gameData['image_url'] = '/storage/' . $path;
        } elseif ($request->game_type === 'image_hotspot' && isset($lms_game->game_data['image_url'])) {
            $gameData['image_url'] = $lms_game->game_data['image_url'];
        }

        $hasItems = false;
        if ($request->game_type === 'spin_wheel' && !empty($gameData['items'])) $hasItems = true;
        if (in_array($request->game_type, ['flashcard', 'match']) && !empty($gameData['pairs'])) $hasItems = true;
        if ($request->game_type === 'quiz' && !empty($gameData['questions'])) $hasItems = true;
        if ($request->game_type === 'true_false' && !empty($gameData['statements'])) $hasItems = true;
        if (in_array($request->game_type, ['word_guess', 'scramble']) && !empty($gameData['words'])) $hasItems = true;
        if ($request->game_type === 'sequence' && (!empty($gameData['items']) || !empty($gameData['groups']))) $hasItems = true;
        if ($request->game_type === 'image_hotspot' && !empty($gameData['image_url']) && !empty($gameData['hotspots'])) $hasItems = true;
        if ($request->game_type === 'chem_balancer' && !empty($gameData['equations'])) $hasItems = true;
        if ($request->game_type === 'math_ninja' && !empty($gameData['config'])) $hasItems = true;

        if (!$hasItems) {
            return back()->with('error', 'Gagal memperbarui: Anda harus mengisi minimal 1 item (soal / kata / data) yang valid!');
        }

        $lms_game->update([
            'module_id' => $request->module_id,
            'title' => $request->title,
            'game_type' => $request->game_type,
            'reward_points' => $request->reward_points,
            'time_limit' => $request->time_limit,
            'lives_count' => $request->lives_count,
            'game_data' => $gameData,
        ]);

        return back()->with('success', 'Game berhasil diperbarui!');
    }

    public function destroy(LmsGame $lms_game)
    {
        if ($lms_game->created_by !== Auth::id()) {
            abort(403);
        }
        $lms_game->delete();
        return back()->with('success', 'Game berhasil dihapus dari modul.');
    }
}

<?php
/**
 * Emergency LMS Module Restore & Resequencer Tool
 * 
 * Standalone PHP script to restore soft-deleted LMS modules and reorder module sequences.
 * URL: https://perguruanpembda.com/restore-module.php?secret=pembda99
 */

// Security verification
if (($_GET['secret'] ?? '') !== 'pembda99') {
    http_response_code(403);
    echo '<h3 style="font-family:monospace;color:#ff5252;padding:20px;">403 Akses Ditolak. Gunakan parameter ?secret=pembda99</h3>';
    exit;
}

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LmsCourse;
use App\Models\LmsModule;
use App\Models\LmsMaterial;
use App\Models\LmsAssignment;
use App\Models\LmsQuiz;
use Illuminate\Support\Facades\DB;

$message = null;
$messageType = 'info';

// Resequencing helper
function resequenceModules(int $courseId, ?int $targetModuleId = null, ?int $newSeq = null) {
    $course = LmsCourse::find($courseId);
    if (!$course) return;

    $modules = $course->modules()->where('is_active', true)->orderBy('sequence')->orderBy('id')->get();
    
    if ($targetModuleId && $newSeq !== null) {
        $targetMod = $modules->firstWhere('id', $targetModuleId);
        if ($targetMod) {
            $modules = $modules->reject(fn($m) => $m->id === $targetModuleId)->values();
            $targetIndex = max(0, min($newSeq - 1, $modules->count()));
            $modules->splice($targetIndex, 0, [$targetMod]);
        }
    }

    foreach ($modules as $idx => $m) {
        $seq = $idx + 1;
        if ($m->sequence !== $seq) {
            LmsModule::where('id', $m->id)->update(['sequence' => $seq]);
        }
    }
}

// Handle Actions
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    try {
        if ($action === 'restore') {
            $modId = (int)($_POST['module_id'] ?? 0);
            $targetSeq = isset($_POST['target_sequence']) && $_POST['target_sequence'] !== '' ? (int)$_POST['target_sequence'] : null;
            
            $mod = LmsModule::onlyTrashed()->findOrFail($modId);
            $courseId = $mod->course_id;
            
            $mod->restore();
            LmsMaterial::onlyTrashed()->where('module_id', $mod->id)->restore();
            LmsAssignment::onlyTrashed()->where('module_id', $mod->id)->restore();
            LmsQuiz::onlyTrashed()->where('module_id', $mod->id)->restore();

            $finalSeq = $targetSeq ?? $mod->sequence ?? 1;
            resequenceModules($courseId, $mod->id, $finalSeq);

            $message = "✅ Berhasil memulihkan modul '{$mod->title}' ke posisi urutan {$finalSeq}!";
            $messageType = 'success';
        } 
        elseif ($action === 'restore_and_delete_temp') {
            $modId = (int)($_POST['module_id'] ?? 0);
            $tempModId = (int)($_POST['temp_module_id'] ?? 0);
            $targetSeq = isset($_POST['target_sequence']) ? (int)$_POST['target_sequence'] : 4;

            $mod = LmsModule::onlyTrashed()->findOrFail($modId);
            $courseId = $mod->course_id;

            // Delete temporary module if provided
            if ($tempModId) {
                $tempMod = LmsModule::find($tempModId);
                if ($tempMod) {
                    $tempMod->delete();
                }
            }

            $mod->restore();
            LmsMaterial::onlyTrashed()->where('module_id', $mod->id)->restore();
            LmsAssignment::onlyTrashed()->where('module_id', $mod->id)->restore();
            LmsQuiz::onlyTrashed()->where('module_id', $mod->id)->restore();

            resequenceModules($courseId, $mod->id, $targetSeq);

            $message = "✅ Berhasil memulihkan modul '{$mod->title}' ke urutan {$targetSeq} dan menghapus modul sementara (ID: {$tempModId})!";
            $messageType = 'success';
        }
        elseif ($action === 'change_sequence') {
            $modId = (int)($_POST['module_id'] ?? 0);
            $newSeq = (int)($_POST['new_sequence'] ?? 1);

            $mod = LmsModule::findOrFail($modId);
            resequenceModules($mod->course_id, $mod->id, $newSeq);

            $message = "✅ Urutan modul '{$mod->title}' berhasil diubah ke posisi {$newSeq}!";
            $messageType = 'success';
        }
        elseif ($action === 'resequence_all') {
            $courseId = (int)($_POST['course_id'] ?? 0);
            resequenceModules($courseId);

            $message = "✅ Semua modul pada kursus ini berhasil dirapikan urutannya (1, 2, 3...)!";
            $messageType = 'success';
        }
    } catch (\Throwable $e) {
        $message = "❌ Terjadi kesalahan: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Fetch all trashed modules with relations
$trashedModules = LmsModule::onlyTrashed()
    ->with(['course.teacher.user', 'materials' => fn($q) => $q->withTrashed()])
    ->orderByDesc('deleted_at')
    ->get();

// Unique courses associated with trashed modules or recent activity
$courseIds = $trashedModules->pluck('course_id')->unique();
$coursesWithTrash = LmsCourse::whereIn('id', $courseIds)->with(['modules' => fn($q) => $q->orderBy('sequence'), 'teacher.user'])->get();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pulihkan Modul LMS — PembdaHUB Emergency Tool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .brutal-card { border: 2px solid #000; box-shadow: 4px 4px 0px #000; }
        .brutal-btn { border: 2px solid #000; box-shadow: 3px 3px 0px #000; transition: all 0.1s ease; }
        .brutal-btn:hover { transform: translate(-2px, -2px); box-shadow: 5px 5px 0px #000; }
        .brutal-btn:active { transform: translate(2px, 2px); box-shadow: 1px 1px 0px #000; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-4 sm:p-8">

<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-amber-400 text-black p-6 rounded-3xl brutal-card flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="bg-black text-amber-400 text-xs px-2.5 py-1 rounded-lg font-black uppercase tracking-wider">LMS Emergency</span>
                <span class="text-xs font-black uppercase text-slate-900">Perguruan Pembda Nias</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black tracking-tight mt-1">🔧 Pemulihan & Penataan Modul LMS</h1>
            <p class="text-xs font-bold text-slate-900 mt-1">Gunakan tool ini untuk memulihkan modul yang terhapus (*soft-deleted*) dan menata urutan modul kembali ke posisi semula.</p>
        </div>
        <a href="https://perguruanpembda.com/admin/dashboard" class="bg-black text-white px-4 py-2.5 rounded-2xl text-xs font-black brutal-btn flex items-center gap-2">
            <i class="fas fa-home text-amber-400"></i> Dashboard
        </a>
    </div>

    <!-- Alert Notification -->
    <?php if ($message): ?>
    <div class="p-4 rounded-2xl brutal-card font-black text-sm flex items-center gap-3 <?= $messageType === 'success' ? 'bg-emerald-500 text-black' : ($messageType === 'error' ? 'bg-rose-500 text-white' : 'bg-blue-400 text-black') ?>">
        <span><?= htmlspecialchars($message) ?></span>
    </div>
    <?php endif; ?>

    <!-- SECTION 1: MODUL TERHAPUS (TRASH) -->
    <div class="bg-slate-800 rounded-3xl p-6 brutal-card space-y-4">
        <div class="flex items-center justify-between border-b-2 border-slate-700 pb-3">
            <h2 class="text-lg font-black text-white flex items-center gap-2">
                <i class="fas fa-trash-restore text-rose-400"></i> 1. Daftar Modul Terhapus (Dapat Dipulihkan)
            </h2>
            <span class="bg-rose-600 text-white text-xs font-black px-3 py-1 rounded-xl">
                <?= $trashedModules->count() ?> Modul di Tempat Sampah
            </span>
        </div>

        <?php if ($trashedModules->isEmpty()): ?>
        <div class="text-center py-10 bg-slate-900/50 rounded-2xl border-2 border-dashed border-slate-700">
            <i class="fas fa-check-circle text-4xl text-emerald-400 mb-2"></i>
            <p class="font-black text-sm text-slate-300">Tidak ada modul yang berstatus terhapus saat ini.</p>
            <p class="text-xs text-slate-500 mt-1">Semua modul LMS berada dalam kondisi aktif.</p>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($trashedModules as $tMod): 
                $course = $tMod->course;
                $activeMods = $course ? $course->modules()->orderBy('sequence')->get() : collect();
                $highSeqMod = $activeMods->sortByDesc('sequence')->first();
            ?>
            <div class="bg-slate-900 border-2 border-slate-700 hover:border-amber-400 rounded-2xl p-5 space-y-4 transition">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-800 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="bg-amber-400 text-black font-black text-xs px-2.5 py-0.5 rounded-lg">
                                Posisi Asli: Ke-<?= $tMod->sequence ?>
                            </span>
                            <span class="text-xs font-bold text-slate-400">ID: #<?= $tMod->id ?></span>
                        </div>
                        <h3 class="text-lg font-black text-white mt-1"><?= htmlspecialchars($tMod->title) ?></h3>
                        <p class="text-xs font-bold text-slate-400">
                            Kursus: <span class="text-amber-300"><?= htmlspecialchars($course->course_name ?? 'Kursus #' . $tMod->course_id) ?></span> 
                            (Guru: <?= htmlspecialchars($course->teacher->user->name ?? '-') ?>)
                        </p>
                    </div>
                    <div class="text-right text-xs text-slate-400">
                        <p><i class="fas fa-file-alt text-amber-400"></i> <?= $tMod->materials->count() ?> Materi di dalamnya</p>
                        <p class="text-[11px] text-rose-400 mt-0.5"><i class="fas fa-clock"></i> Dihapus: <?= $tMod->deleted_at?->format('d M Y, H:i') ?></p>
                    </div>
                </div>

                <!-- Restore Actions Form -->
                <div class="bg-slate-800/80 p-4 rounded-xl space-y-3">
                    <p class="text-xs font-black text-amber-300 uppercase tracking-wider">Pilih Opsi Pemulihan:</p>
                    
                    <div class="flex flex-wrap gap-3">
                        <!-- Option A: Restore to Original Sequence -->
                        <form method="POST" action="?secret=pembda99" onsubmit="return confirm('Pulihkan modul ini ke urutan <?= $tMod->sequence ?>?')" class="inline">
                            <input type="hidden" name="action" value="restore">
                            <input type="hidden" name="module_id" value="<?= $tMod->id ?>">
                            <input type="hidden" name="target_sequence" value="<?= $tMod->sequence ?>">
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs px-4 py-2.5 rounded-xl brutal-btn flex items-center gap-2">
                                <i class="fas fa-undo"></i> Pulihkan ke Posisi <?= $tMod->sequence ?>
                            </button>
                        </form>

                        <!-- Option B: If high sequence module exists, offer to replace it -->
                        <?php if ($highSeqMod && $highSeqMod->sequence >= 10): ?>
                        <form method="POST" action="?secret=pembda99" onsubmit="return confirm('Pulihkan modul ini ke posisi <?= $tMod->sequence ?> dan hapus modul sementara #<?= $highSeqMod->id ?> (<?= htmlspecialchars($highSeqMod->title) ?>)?')" class="inline">
                            <input type="hidden" name="action" value="restore_and_delete_temp">
                            <input type="hidden" name="module_id" value="<?= $tMod->id ?>">
                            <input type="hidden" name="temp_module_id" value="<?= $highSeqMod->id ?>">
                            <input type="hidden" name="target_sequence" value="<?= $tMod->sequence ?>">
                            <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-black font-black text-xs px-4 py-2.5 rounded-xl brutal-btn flex items-center gap-2" title="Pulihkan modul lama dan bersihkan modul baru ke-<?= $highSeqMod->sequence ?> yang dibuat sebagai pengganti sementara">
                                <i class="fas fa-exchange-alt"></i> Pulihkan ke Posisi <?= $tMod->sequence ?> & Hapus Modul Baru Ke-<?= $highSeqMod->sequence ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- SECTION 2: SUSUNAN MODUL AKTIF & REORDER -->
    <?php if ($coursesWithTrash->isNotEmpty()): ?>
    <?php foreach ($coursesWithTrash as $c): ?>
    <div class="bg-slate-800 rounded-3xl p-6 brutal-card space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b-2 border-slate-700 pb-3">
            <div>
                <h2 class="text-lg font-black text-white flex items-center gap-2">
                    <i class="fas fa-list-ol text-amber-400"></i> Susunan Modul Aktif: <?= htmlspecialchars($c->course_name) ?>
                </h2>
                <p class="text-xs text-slate-400 font-bold">Pengajar: <?= htmlspecialchars($c->teacher->user->name ?? '-') ?> (Total: <?= $c->modules->count() ?> modul aktif)</p>
            </div>
            <form method="POST" action="?secret=pembda99" onsubmit="return confirm('Rapikan semua urutan modul menjadi 1, 2, 3... secara berurutan?')">
                <input type="hidden" name="action" value="resequence_all">
                <input type="hidden" name="course_id" value="<?= $c->id ?>">
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-black text-xs px-4 py-2 rounded-xl brutal-btn flex items-center gap-2">
                    <i class="fas fa-magic text-amber-300"></i> Rapikan Urutan (1, 2, 3...)
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($c->modules as $m): ?>
            <div class="bg-slate-900 border-2 <?= $m->sequence == 18 ? 'border-amber-400 bg-amber-950/20' : 'border-slate-700' ?> rounded-2xl p-3 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2.5 overflow-hidden">
                    <span class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-xs shrink-0 <?= $m->sequence == 18 ? 'bg-amber-400 text-black' : 'bg-slate-800 text-amber-400 border border-slate-600' ?>">
                        <?= $m->sequence ?>
                    </span>
                    <div class="truncate">
                        <p class="text-xs font-black text-white truncate" title="<?= htmlspecialchars($m->title) ?>"><?= htmlspecialchars($m->title) ?></p>
                        <p class="text-[10px] text-slate-400 font-bold">M<?= $m->sequence ?> • ID #<?= $m->id ?></p>
                    </div>
                </div>

                <!-- Form Ganti Urutan Cepat -->
                <form method="POST" action="?secret=pembda99" class="flex items-center gap-1 shrink-0">
                    <input type="hidden" name="action" value="change_sequence">
                    <input type="hidden" name="module_id" value="<?= $m->id ?>">
                    <input type="number" name="new_sequence" value="<?= $m->sequence ?>" min="1" max="99" class="w-12 bg-slate-800 border border-slate-600 rounded-lg text-xs text-center font-black text-amber-300 py-1 focus:ring-1 focus:ring-amber-400 outline-none">
                    <button type="submit" class="bg-slate-700 hover:bg-amber-400 hover:text-black text-slate-200 text-xs font-black px-2 py-1 rounded-lg transition" title="Simpan urutan">
                        <i class="fas fa-check"></i>
                    </button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- Information Card -->
    <div class="bg-slate-800/60 border-2 border-slate-700 rounded-2xl p-4 text-xs text-slate-400 space-y-1">
        <p class="font-bold text-slate-300"><i class="fas fa-shield-alt text-emerald-400 mr-1"></i> Keamanan Data:</p>
        <p>Proses pemulihan modul menggunakan mekanisme bawaan Laravel (*Eloquent SoftDeletes*). Seluruh materi ajar, file tugas, dan kuis yang terkait dengan modul akan otomatis ikut terpulihkan secara aman.</p>
    </div>

</div>

</body>
</html>

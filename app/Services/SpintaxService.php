<?php

namespace App\Services;

class SpintaxService
{
    /**
     * Institutional & School Mottos
     */
    public const MOTTOS = [
        'Keep Moving Forward',
        'Maju Terus Pantang Mundur',
        'Semangat',
        'Stop Ask Just Action',
        'Progresive In Harmony',
    ];

    /**
     * Spin text containing Spintax format: {option1|option2|option3}
     * Supports nested Spintax, {motto}, {motto_raw}, and preserves single variables like {name}.
     */
    public static function spin(string $text): string
    {
        // 1. Resolve {motto} and {motto_raw}
        if (stripos($text, '{motto}') !== false) {
            $text = preg_replace_callback('/\{motto\}/i', function () {
                return self::spinMottoLine();
            }, $text);
        }
        if (stripos($text, '{motto_raw}') !== false) {
            $text = preg_replace_callback('/\{motto_raw\}/i', function () {
                return self::spinMotto();
            }, $text);
        }
        if (stripos($text, '{reply_request}') !== false) {
            $text = preg_replace_callback('/\{reply_request\}/i', function () {
                return self::spinReplyRequest();
            }, $text);
        }

        // 2. Resolve standard Spintax {opt1|opt2|...}
        $pattern = '/\{([^{}]+?\|[^{}]+?)\}/s';
        while (preg_match($pattern, $text)) {
            $text = preg_replace_callback($pattern, function ($matches) {
                $options = explode('|', $matches[1]);
                return $options[array_rand($options)];
            }, $text, 1);
        }
        return $text;
    }

    /**
     * Get a random raw motto string.
     */
    public static function spinMotto(): string
    {
        return self::MOTTOS[array_rand(self::MOTTOS)];
    }

    /**
     * Generate dynamic formatted motto line with emoji and styling.
     */
    public static function spinMottoLine(): string
    {
        $motto = self::spinMotto();
        $icons = ['🌟', '💪', '🚀', '✨', '🔥', '🎯', '🌿', '💡', '💫', '⭐'];
        $icon = $icons[array_rand($icons)];

        $formats = [
            "{$icon} *Motto:* \"{$motto}\"",
            "{$icon} *Motto:* {$motto}",
            "{$icon} _Motto: \"{$motto}\"_",
            "{$icon} *Motto Hari Ini:* \"{$motto}\"",
            "{$icon} Motto: *\"{$motto}\"*",
            "{$icon} *Motto :* \"{$motto}\"",
        ];

        return $formats[array_rand($formats)];
    }

    /**
     * Generate dynamic polymorphic greeting for WhatsApp.
     */
    public static function spinGreeting(string $name, string $title = 'Bapak/Ibu'): string
    {
        $cleanName = trim($name);
        $firstWord = explode(' ', $cleanName)[0];

        $greetings = [
            "Selamat pagi, {$title} {$cleanName}. 🙏",
            "Salam hormat dan semangat pagi, {$title} {$cleanName}. ✨",
            "Selamat pagi dan salam hangat, {$title} {$cleanName}. 🌟",
            "Yth. {$title} {$cleanName}, selamat pagi.",
            "Semoga hari ini penuh berkah dan kesehatan, {$title} {$cleanName}. 🌿",
            "Salam takzim, {$title} {$cleanName}. Semoga senantiasa sehat dan bersemangat. 🙏",
            "Selamat menyambut aktivitas hari ini, {$title} {$firstWord}. ✨",
            "Semoga senantiasa diberikan kelancaran dan kemudahan, {$title} {$firstWord}. 🌟",
            "Salam semangat pagi untuk {$title} {$cleanName}! Semoga sukses selalu. 💫",
            "Pagi yang cerah, semoga aktivitas {$title} {$firstWord} hari ini dilancarkan. 🌺",
        ];

        return $greetings[array_rand($greetings)];
    }

    /**
     * Generate dynamic polymorphic closing statement.
     */
    public static function spinClosing(): string
    {
        $closings = [
            "Semoga kegiatan belajar mengajar hari ini berjalan lancar, tertib, dan penuh berkah. 🙏",
            "Terima kasih atas dedikasi, keikhlasan, dan bimbingan luar biasa Bapak/Ibu untuk siswa kita. ✨",
            "Semangat mendampingi dan mencerdaskan generasi penerus Pembda hari ini! 🌟",
            "Semoga seluruh agenda dan aktivitas pendidikan hari ini diberikan kemudahan dan kelancaran. 🌿",
            "Dedikasi dan kehadiran tepat waktu Bapak/Ibu adalah teladan terbaik bagi siswa-siswi kita. Selamat bertugas! 👨‍🏫👩‍🏫",
            "Mari bersama-sama mewujudkan lingkungan belajar yang inspiratif dan berprestasi. Sukses selalu! 🌟",
            "Terima kasih atas pengabdian tanpa lelah Bapak/Ibu bagi kemajuan Perguruan Pembda. Salam hormat! 💐",
            "Semoga hari ini membawa keberkahan dan capaian terbaik dalam tugas Bapak/Ibu. Tetap semangat! ✨",
        ];

        return $closings[array_rand($closings)];
    }

    /**
     * Generate dynamic footer signature.
     */
    public static function spinFooter(): string
    {
        $footers = [
            "_Sistem Notifikasi Otomatis SIM PembdaHUB_",
            "_SIM Terpadu Perguruan Pembda Nias_",
            "_Executive Information & Notification System PembdaHUB_",
            "_Layanan Otomatis SIM Terpadu PembdaHUB_",
            "_Sistem Informasi Manajemen Sekolah PembdaHUB_",
        ];

        return $footers[array_rand($footers)];
    }

    /**
     * Generate variative reply-request message for digest recaps.
     * Asks recipients to reply so WhatsApp detects 2-way conversation (anti-spam).
     */
    public static function spinReplyRequest(): string
    {
        $requests = [
            "📢 *Pemberitahuan:* Ini adalah nomor admin baru Perguruan Pembda. Mohon bantuannya untuk *membalas pesan ini* (cukup ketik \"OK\" / \"Terima kasih\") agar sistem WhatsApp mengenali bahwa pesan ini bukan spam. Terima kasih atas kerja samanya! 🙏",
            "📌 *Info Penting:* Nomor ini adalah nomor admin resmi yang baru. Demi kelancaran layanan notifikasi sekolah, mohon *balas pesan ini dengan satu kata* (misal: \"Siap\" / \"Diterima\") agar WhatsApp tidak menandai pesan kami sebagai pesan otomatis. Terima kasih! 🙏",
            "💬 *Mohon Konfirmasi:* Agar rekapitulasi ini terus terkirim lancar, mohon kiranya Bapak/Ibu *berkenan membalas singkat* pesan ini (contoh: \"OK\" / \"Noted\"). Balasan Anda membantu sistem mengenali bahwa pesan ini diterima oleh orang yang tepat dan bukan spam. Salam hormat! 🙏",
            "📲 *Perhatian:* Nomor WhatsApp admin sekolah telah diperbarui. Mohon *balas pesan ini* dengan kata singkat (misal: \"Terima kasih\" / \"Sudah dibaca\") untuk memastikan nomor ini tersimpan dan tidak terblokir oleh sistem anti-spam WhatsApp. Terima kasih banyak! 🙏",
            "🔔 *Catatan Admin:* Ini adalah nomor baru layanan notifikasi Perguruan Pembda. Agar WhatsApp tidak memblokir pengiriman rekapitulasi ini, mohon *balas dengan kata singkat* seperti \"Diterima\" atau \"OK\". Satu balasan Anda sangat membantu kelancaran sistem informasi sekolah kita. 🙏",
            "✉️ *Himbauan:* Demi keberlangsungan layanan informasi kehadiran harian ini, mohon Bapak/Ibu *berkenan mengirim balasan singkat* (contoh: \"Siap\" / \"Terima kasih\"). Hal ini penting agar WhatsApp mendeteksi komunikasi dua arah dan tidak menganggap pesan ini sebagai spam. Salam! 🙏",
        ];

        return $requests[array_rand($requests)];
    }
}

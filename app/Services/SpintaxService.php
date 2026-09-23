<?php

namespace App\Services;

class SpintaxService
{
    /**
     * Spin text containing Spintax format: {option1|option2|option3}
     * Supports nested Spintax and preserves single variables like {name}.
     */
    public static function spin(string $text): string
    {
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
}

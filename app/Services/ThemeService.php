<?php

namespace App\Services;

use App\Models\Setting;

class ThemeService
{
    /**
     * Get all available theme presets.
     */
    public static function getThemes(): array
    {
        return [
            'cerah_ceria' => [
                'name' => 'Cerah & Ceria (Luminous Sunshine)',
                'badge' => 'Rekomendasi',
                'badge_color' => 'bg-amber-100 text-amber-800 border-amber-300',
                'description' => 'Dominan putih bersih, gradasi kuning matahari hangat, biru langit cerah, dan hijau mint segar. Ramah, berenergi positif, dan sangat hidup.',
                'icon' => 'fa-solid fa-sun',
                'swatches' => ['#ffffff', '#fbbf24', '#2563eb', '#10b981'],
                'css' => [
                    '--bg' => '#f8fafc',
                    '--bg-card' => '#ffffff',
                    '--bg-hero' => 'linear-gradient(135deg, #ffffff 0%, #f0fdf4 30%, #eff6ff 65%, #fefce8 100%)',
                    '--text-primary' => '#0f172a',
                    '--text-secondary' => '#334155',
                    '--text-muted' => '#64748b',
                    '--border' => '#e2e8f0',
                    '--border-strong' => '#cbd5e1',
                    '--primary' => '#2563eb',
                    '--primary-gradient' => 'linear-gradient(135deg, #2563eb 0%, #7c3aed 50%, #db2777 100%)',
                    '--accent-gold' => '#f59e0b',
                    '--accent-gold-bright' => '#fbbf24',
                    '--accent-emerald' => '#10b981',
                    '--btn-primary-bg' => 'linear-gradient(135deg, #f59e0b, #fbbf24)',
                    '--btn-primary-text' => '#0f172a',
                    '--btn-primary-shadow' => '0 6px 20px rgba(245, 158, 11, 0.4)',
                    '--navbar-bg' => 'rgba(255, 255, 255, 0.90)',
                    '--navbar-text' => '#0f172a',
                    '--card-shadow' => '0 10px 30px -5px rgba(37, 99, 235, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.04)',
                    '--card-hover-shadow' => '0 20px 40px -10px rgba(37, 99, 235, 0.16)',
                ],
            ],

            'emerald_prestige' => [
                'name' => 'Zamrud & Emas (Emerald Prestige)',
                'badge' => 'Elegan & Berwibawa',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'description' => 'Latar putih marmer bersih dengan aksen hijau zamrud elegan dan sentuhan emas berkilau. Prestisius, bernilai tinggi, dan terpercaya.',
                'icon' => 'fa-solid fa-gem',
                'swatches' => ['#ffffff', '#059669', '#10b981', '#d97706'],
                'css' => [
                    '--bg' => '#f9fafb',
                    '--bg-card' => '#ffffff',
                    '--bg-hero' => 'linear-gradient(135deg, #ffffff 0%, #ecfdf5 40%, #f0fdf4 75%, #fffbeb 100%)',
                    '--text-primary' => '#064e3b',
                    '--text-secondary' => '#1f2937',
                    '--text-muted' => '#4b5563',
                    '--border' => '#d1fae5',
                    '--border-strong' => '#a7f3d0',
                    '--primary' => '#059669',
                    '--primary-gradient' => 'linear-gradient(135deg, #059669 0%, #047857 50%, #d97706 100%)',
                    '--accent-gold' => '#d97706',
                    '--accent-gold-bright' => '#f59e0b',
                    '--accent-emerald' => '#059669',
                    '--btn-primary-bg' => 'linear-gradient(135deg, #059669, #10b981)',
                    '--btn-primary-text' => '#ffffff',
                    '--btn-primary-shadow' => '0 6px 20px rgba(5, 150, 105, 0.35)',
                    '--navbar-bg' => 'rgba(255, 255, 255, 0.92)',
                    '--navbar-text' => '#064e3b',
                    '--card-shadow' => '0 10px 30px -5px rgba(5, 150, 105, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.04)',
                    '--card-hover-shadow' => '0 20px 40px -10px rgba(5, 150, 105, 0.16)',
                ],
            ],

            'tech_indigo' => [
                'name' => 'Tech Indigo Modern (Clean SaaS)',
                'badge' => 'Modern & Canggih',
                'badge_color' => 'bg-indigo-100 text-indigo-800 border-indigo-300',
                'description' => 'Nuansa biru royal modern, slate tajam, dan glassmorphism bersih ala Linear & Stripe. Fokus pada otomatisasi dan teknologi cerdas.',
                'icon' => 'fa-solid fa-microchip',
                'swatches' => ['#ffffff', '#4f46e5', '#2563eb', '#0f172a'],
                'css' => [
                    '--bg' => '#f8fafc',
                    '--bg-card' => '#ffffff',
                    '--bg-hero' => 'linear-gradient(135deg, #ffffff 0%, #eef2ff 40%, #eff6ff 80%, #f1f5f9 100%)',
                    '--text-primary' => '#0f172a',
                    '--text-secondary' => '#334155',
                    '--text-muted' => '#64748b',
                    '--border' => '#e2e8f0',
                    '--border-strong' => '#cbd5e1',
                    '--primary' => '#4f46e5',
                    '--primary-gradient' => 'linear-gradient(135deg, #4f46e5 0%, #2563eb 50%, #06b6d4 100%)',
                    '--accent-gold' => '#f59e0b',
                    '--accent-gold-bright' => '#fbbf24',
                    '--accent-emerald' => '#10b981',
                    '--btn-primary-bg' => 'linear-gradient(135deg, #4f46e5, #6366f1)',
                    '--btn-primary-text' => '#ffffff',
                    '--btn-primary-shadow' => '0 6px 20px rgba(79, 70, 229, 0.35)',
                    '--navbar-bg' => 'rgba(255, 255, 255, 0.90)',
                    '--navbar-text' => '#0f172a',
                    '--card-shadow' => '0 10px 30px -5px rgba(79, 70, 229, 0.08), 0 4px 12px -2px rgba(15, 23, 42, 0.04)',
                    '--card-hover-shadow' => '0 20px 40px -10px rgba(79, 70, 229, 0.18)',
                ],
            ],

            'academic_classic' => [
                'name' => 'Akademik Klasik (Navy & Crimson)',
                'badge' => 'Formal & Terpercaya',
                'badge_color' => 'bg-blue-100 text-blue-800 border-blue-300',
                'description' => 'Nuansa akademik resmi terpercaya dengan biru navy pekat dan merah marun tegas. Tampilan formal kelembagaan yayasan yang kokoh.',
                'icon' => 'fa-solid fa-graduation-cap',
                'swatches' => ['#ffffff', '#1e3a8a', '#dc2626', '#f59e0b'],
                'css' => [
                    '--bg' => '#fafafa',
                    '--bg-card' => '#ffffff',
                    '--bg-hero' => 'linear-gradient(135deg, #ffffff 0%, #eff6ff 40%, #fef2f2 75%, #ffffff 100%)',
                    '--text-primary' => '#1e293b',
                    '--text-secondary' => '#334155',
                    '--text-muted' => '#64748b',
                    '--border' => '#e2e8f0',
                    '--border-strong' => '#cbd5e1',
                    '--primary' => '#1e3a8a',
                    '--primary-gradient' => 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #dc2626 100%)',
                    '--accent-gold' => '#d97706',
                    '--accent-gold-bright' => '#f59e0b',
                    '--accent-emerald' => '#059669',
                    '--btn-primary-bg' => 'linear-gradient(135deg, #1e3a8a, #2563eb)',
                    '--btn-primary-text' => '#ffffff',
                    '--btn-primary-shadow' => '0 6px 20px rgba(30, 58, 138, 0.35)',
                    '--navbar-bg' => 'rgba(255, 255, 255, 0.92)',
                    '--navbar-text' => '#1e293b',
                    '--card-shadow' => '0 10px 30px -5px rgba(30, 58, 138, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.04)',
                    '--card-hover-shadow' => '0 20px 40px -10px rgba(30, 58, 138, 0.16)',
                ],
            ],

            'kemerdekaan' => [
                'name' => 'Hari Kemerdekaan RI (Merah Putih)',
                'badge' => 'Event Khusus',
                'badge_color' => 'bg-red-100 text-red-800 border-red-300',
                'description' => 'Tema perayaan kemerdekaan RI bernuansa merah berani dan putih suci yang membangkitkan jiwa nasionalisme dan patriotisme.',
                'icon' => 'fa-solid fa-flag',
                'swatches' => ['#ffffff', '#dc2626', '#ef4444', '#b91c1c'],
                'css' => [
                    '--bg' => '#fffdfd',
                    '--bg-card' => '#ffffff',
                    '--bg-hero' => 'linear-gradient(135deg, #ffffff 0%, #fef2f2 40%, #fee2e2 75%, #ffffff 100%)',
                    '--text-primary' => '#991b1b',
                    '--text-secondary' => '#374151',
                    '--text-muted' => '#6b7280',
                    '--border' => '#fee2e2',
                    '--border-strong' => '#fca5a5',
                    '--primary' => '#dc2626',
                    '--primary-gradient' => 'linear-gradient(135deg, #dc2626 0%, #ef4444 50%, #b91c1c 100%)',
                    '--accent-gold' => '#f59e0b',
                    '--accent-gold-bright' => '#fbbf24',
                    '--accent-emerald' => '#10b981',
                    '--btn-primary-bg' => 'linear-gradient(135deg, #dc2626, #ef4444)',
                    '--btn-primary-text' => '#ffffff',
                    '--btn-primary-shadow' => '0 6px 20px rgba(220, 38, 38, 0.35)',
                    '--navbar-bg' => 'rgba(255, 255, 255, 0.94)',
                    '--navbar-text' => '#991b1b',
                    '--card-shadow' => '0 10px 30px -5px rgba(220, 38, 38, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.04)',
                    '--card-hover-shadow' => '0 20px 40px -10px rgba(220, 38, 38, 0.16)',
                ],
            ],

            'paskah' => [
                'name' => 'Hari Paskah (Damai & Kasih)',
                'badge' => 'Event Khusus',
                'badge_color' => 'bg-purple-100 text-purple-800 border-purple-300',
                'description' => 'Tema perayaan Paskah bernuansa ungu damai, putih suci, dan kuning keemasan yang menghadirkan suasana penuh sukacita dan harapan.',
                'icon' => 'fa-solid fa-cross',
                'swatches' => ['#ffffff', '#7c3aed', '#a855f7', '#fbbf24'],
                'css' => [
                    '--bg' => '#fdfaff',
                    '--bg-card' => '#ffffff',
                    '--bg-hero' => 'linear-gradient(135deg, #ffffff 0%, #f5f3ff 40%, #faf5ff 75%, #fefce8 100%)',
                    '--text-primary' => '#581c87',
                    '--text-secondary' => '#374151',
                    '--text-muted' => '#6b7280',
                    '--border' => '#ede9fe',
                    '--border-strong' => '#c4b5fd',
                    '--primary' => '#7c3aed',
                    '--primary-gradient' => 'linear-gradient(135deg, #7c3aed 0%, #9333ea 50%, #f59e0b 100%)',
                    '--accent-gold' => '#f59e0b',
                    '--accent-gold-bright' => '#fbbf24',
                    '--accent-emerald' => '#10b981',
                    '--btn-primary-bg' => 'linear-gradient(135deg, #7c3aed, #9333ea)',
                    '--btn-primary-text' => '#ffffff',
                    '--btn-primary-shadow' => '0 6px 20px rgba(124, 58, 237, 0.35)',
                    '--navbar-bg' => 'rgba(255, 255, 255, 0.94)',
                    '--navbar-text' => '#581c87',
                    '--card-shadow' => '0 10px 30px -5px rgba(124, 58, 237, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.04)',
                    '--card-hover-shadow' => '0 20px 40px -10px rgba(124, 58, 237, 0.16)',
                ],
            ],

            'natal' => [
                'name' => 'Hari Natal & Tahun Baru',
                'badge' => 'Event Khusus',
                'badge_color' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'description' => 'Tema perayaan Natal dan Tahun Baru bernuansa hijau cemara, merah hangat, dan emas gemerlap yang membawa kedamaian dan kehangatan.',
                'icon' => 'fa-solid fa-tree',
                'swatches' => ['#ffffff', '#047857', '#dc2626', '#f59e0b'],
                'css' => [
                    '--bg' => '#f8fbf9',
                    '--bg-card' => '#ffffff',
                    '--bg-hero' => 'linear-gradient(135deg, #ffffff 0%, #ecfdf5 40%, #fef2f2 75%, #fffbeb 100%)',
                    '--text-primary' => '#064e3b',
                    '--text-secondary' => '#1f2937',
                    '--text-muted' => '#4b5563',
                    '--border' => '#d1fae5',
                    '--border-strong' => '#a7f3d0',
                    '--primary' => '#047857',
                    '--primary-gradient' => 'linear-gradient(135deg, #047857 0%, #dc2626 50%, #d97706 100%)',
                    '--accent-gold' => '#d97706',
                    '--accent-gold-bright' => '#f59e0b',
                    '--accent-emerald' => '#047857',
                    '--btn-primary-bg' => 'linear-gradient(135deg, #047857, #10b981)',
                    '--btn-primary-text' => '#ffffff',
                    '--btn-primary-shadow' => '0 6px 20px rgba(4, 120, 87, 0.35)',
                    '--navbar-bg' => 'rgba(255, 255, 255, 0.94)',
                    '--navbar-text' => '#064e3b',
                    '--card-shadow' => '0 10px 30px -5px rgba(4, 120, 87, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.04)',
                    '--card-hover-shadow' => '0 20px 40px -10px rgba(4, 120, 87, 0.16)',
                ],
            ],

            'pendidikan' => [
                'name' => 'Hari Pendidikan Nasional (Hardiknas)',
                'badge' => 'Event Khusus',
                'badge_color' => 'bg-blue-100 text-blue-800 border-blue-300',
                'description' => 'Tema perayaan Hari Pendidikan Nasional dengan warna biru pendidikan cerdas dan kuning keemasan Tut Wuri Handayani.',
                'icon' => 'fa-solid fa-book-open-reader',
                'swatches' => ['#ffffff', '#0284c7', '#38bdf8', '#f59e0b'],
                'css' => [
                    '--bg' => '#f8fcff',
                    '--bg-card' => '#ffffff',
                    '--bg-hero' => 'linear-gradient(135deg, #ffffff 0%, #f0f9ff 40%, #e0f2fe 75%, #fefce8 100%)',
                    '--text-primary' => '#0c4a6e',
                    '--text-secondary' => '#1e293b',
                    '--text-muted' => '#475569',
                    '--border' => '#e0f2fe',
                    '--border-strong' => '#bae6fd',
                    '--primary' => '#0284c7',
                    '--primary-gradient' => 'linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #f59e0b 100%)',
                    '--accent-gold' => '#f59e0b',
                    '--accent-gold-bright' => '#fbbf24',
                    '--accent-emerald' => '#10b981',
                    '--btn-primary-bg' => 'linear-gradient(135deg, #0284c7, #38bdf8)',
                    '--btn-primary-text' => '#ffffff',
                    '--btn-primary-shadow' => '0 6px 20px rgba(2, 132, 199, 0.35)',
                    '--navbar-bg' => 'rgba(255, 255, 255, 0.94)',
                    '--navbar-text' => '#0c4a6e',
                    '--card-shadow' => '0 10px 30px -5px rgba(2, 132, 199, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.04)',
                    '--card-hover-shadow' => '0 20px 40px -10px rgba(2, 132, 199, 0.16)',
                ],
            ],

            'pahlawan' => [
                'name' => 'Hari Pahlawan (Semangat Pejuang)',
                'badge' => 'Event Khusus',
                'badge_color' => 'bg-amber-100 text-amber-800 border-amber-300',
                'description' => 'Tema peringatan Hari Pahlawan bernuansa merah keberanian dan cokelat emas keteguhan pejuang bangsa.',
                'icon' => 'fa-solid fa-medal',
                'swatches' => ['#ffffff', '#b45309', '#dc2626', '#f59e0b'],
                'css' => [
                    '--bg' => '#fffdfa',
                    '--bg-card' => '#ffffff',
                    '--bg-hero' => 'linear-gradient(135deg, #ffffff 0%, #fef3c7 40%, #fee2e2 75%, #ffffff 100%)',
                    '--text-primary' => '#78350f',
                    '--text-secondary' => '#1f2937',
                    '--text-muted' => '#4b5563',
                    '--border' => '#fef3c7',
                    '--border-strong' => '#fde68a',
                    '--primary' => '#b45309',
                    '--primary-gradient' => 'linear-gradient(135deg, #b45309 0%, #dc2626 50%, #f59e0b 100%)',
                    '--accent-gold' => '#b45309',
                    '--accent-gold-bright' => '#f59e0b',
                    '--accent-emerald' => '#10b981',
                    '--btn-primary-bg' => 'linear-gradient(135deg, #b45309, #d97706)',
                    '--btn-primary-text' => '#ffffff',
                    '--btn-primary-shadow' => '0 6px 20px rgba(180, 83, 9, 0.35)',
                    '--navbar-bg' => 'rgba(255, 255, 255, 0.94)',
                    '--navbar-text' => '#78350f',
                    '--card-shadow' => '0 10px 30px -5px rgba(180, 83, 9, 0.08), 0 4px 12px -2px rgba(0, 0, 0, 0.04)',
                    '--card-hover-shadow' => '0 20px 40px -10px rgba(180, 83, 9, 0.16)',
                ],
            ],
        ];
    }

    /**
     * Get the active theme key.
     */
    public static function getActiveThemeKey(): string
    {
        $theme = Setting::getValue('homepage_theme', 'cerah_ceria');
        $all = self::getThemes();
        
        // Backward compatibility mapping
        if ($theme === 'regular') {
            return 'cerah_ceria';
        }
        
        return array_key_exists($theme, $all) ? $theme : 'cerah_ceria';
    }

    /**
     * Get the active theme configuration.
     */
    public static function getActiveTheme(): array
    {
        $key = self::getActiveThemeKey();
        $themes = self::getThemes();
        return $themes[$key] ?? $themes['cerah_ceria'];
    }

    /**
     * Generate dynamic :root CSS variables based on active theme.
     */
    public static function generateCssVariables(): string
    {
        $theme = self::getActiveTheme();
        $cssRules = [];

        foreach ($theme['css'] as $varName => $value) {
            $cssRules[] = "        {$varName}: {$value};";
        }

        return ":root {\n" . implode("\n", $cssRules) . "\n    }";
    }
}

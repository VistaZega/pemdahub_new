<?php
require __DIR__ . '/../bootstrap/app.php';
\$app = require_once __DIR__ . '/../bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

\$kepseks = User::where('role', 'kepala_sekolah')->get(['name', 'email', 'school_id']);
foreach (\$kepseks as \$k) {
    echo "Nama: " . \$k->name . " | Email: " . \$k->email . " | School ID: " . \$k->school_id . "\n";
}
EOF

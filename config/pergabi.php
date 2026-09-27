<?php

return [
    'masa_berlaku_tahun' => (int) env('PERGABI_MASA_BERLAKU_TAHUN', 5),
    'dashboard_cache_ttl' => (int) env('PERGABI_DASHBOARD_CACHE_TTL', 3600),
    'website' => env('PERGABI_WEBSITE', 'www.pergabi.or.id'),
    'email' => env('PERGABI_EMAIL', 'sekretariat@pergabi.or.id'),
    'nama_lengkap' => env('PERGABI_NAMA_LENGKAP', 'PERKUMPULAN GURU AGAMA BUDDHA INDONESIA'),
    'singkatan' => env('PERGABI_SINGKATAN', 'PERGABI'),
    'alamat' => env('PERGABI_ALAMAT', 'Jalan Kapuk Raya, Gang Mawar SCB RT 11 RW 01, Cengkareng, Jakarta Barat'),
    'nama_ketua_umum' => env('PERGABI_KETUA_UMUM', 'Sukiman'),
    'nama_sekretaris_jenderal' => env('PERGABI_SEKJEN', 'Roch Aksiadi'),
    'visi' => env('PERGABI_VISI', 'Terwujudnya Pendidikan Agama Buddha Indonesia yang unggul, literat, dan berkarakter'),
    'misi' => env('PERGABI_MISI', '-'),
    'files' => [
        'logo' => 'images/organisasi/logo.png',
        'stempel' => 'images/organisasi/stempel.png',
        'ttd_ketua_umum' => 'images/organisasi/ttd-ketua-umum.png',
        'ttd_sekretaris_jenderal' => 'images/organisasi/ttd-sekretaris-jenderal.png',
    ],
    'kta' => [
        'ornamen' => [
            'kiri_atas' => 'assets/kta/kiri-atas.png',
            'kanan_atas' => 'assets/kta/kanan-atas.png',
            'kanan_bawah' => 'assets/kta/kanan-bawah.png',
            'watermark' => 'assets/kta/logo-for-bg.png',
            'lambang' => 'assets/kta/logo.png',
        ],
    ],
    'wp' => [
        'base_url' => env('PERGABI_WP_API_URL', 'https://pergabi.id/wp-json/wp/v2'),
        'kegiatan_category' => (int) env('PERGABI_WP_KEGIATAN_CATEGORY', 11),
        'per_page' => (int) env('PERGABI_WP_PER_PAGE', 12),
        'cache_ttl' => (int) env('PERGABI_WP_CACHE_TTL', 600),
    ],
];

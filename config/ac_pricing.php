<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Matriks Tarif Servis AC Berdasarkan Kapasitas PK & Kategori Keluhan
    |--------------------------------------------------------------------------
    | Tarif khusus dan bersahabat untuk perawatan masjid dan musholla.
    */

    'pk_options' => [
        '0.5 PK' => '0.5 PK (1/2 PK)',
        '0.75 PK' => '0.75 PK (3/4 PK)',
        '1 PK' => '1 PK',
        '1.5 PK' => '1.5 PK',
        '2 PK' => '2 PK',
        '2.5 PK' => '2.5 PK',
        '3 PK' => '3 PK',
        '5 PK' => '5 PK (Standing / Cassette)',
    ],

    'categories' => [
        'Cuci Rutin' => [
            'name' => 'Cuci Rutin / Pembersihan',
            'desc' => 'Cuci evaporator, filter indoor, dan kondensor outdoor',
            'rates' => [
                '0.5 PK' => 65000,
                '0.75 PK' => 65000,
                '1 PK' => 65000,
                '1.5 PK' => 75000,
                '2 PK' => 75000,
                '2.5 PK' => 90000,
                '3 PK' => 90000,
                '5 PK' => 130000,
            ],
            'default_rate' => 75000,
        ],
        'Kurang Dingin / Bocor Air' => [
            'name' => 'Kurang Dingin / Bocor Air',
            'desc' => 'Pembersihan mendalam, perbaikan talang air bocor, dan cek tekanan freon',
            'rates' => [
                '0.5 PK' => 80000,
                '0.75 PK' => 80000,
                '1 PK' => 80000,
                '1.5 PK' => 90000,
                '2 PK' => 90000,
                '2.5 PK' => 110000,
                '3 PK' => 110000,
                '5 PK' => 160000,
            ],
            'default_rate' => 90000,
        ],
        'Mati Total / Rusak' => [
            'name' => 'Mati Total / Rusak Berat',
            'desc' => 'Troubleshooting kelistrikan, modul/PCB, kapasitor, dan kompresor',
            'rates' => [
                '0.5 PK' => 100000,
                '0.75 PK' => 100000,
                '1 PK' => 100000,
                '1.5 PK' => 120000,
                '2 PK' => 120000,
                '2.5 PK' => 140000,
                '3 PK' => 140000,
                '5 PK' => 200000,
            ],
            'default_rate' => 120000,
        ],
    ],
];

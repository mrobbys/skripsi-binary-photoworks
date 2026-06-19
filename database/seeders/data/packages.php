<?php

return [
  // PERSONAL STUDIO
  [
    'category_code' => 'PSN',
    'name' => 'Personal Studio',
    'features' => [
      'Tidak ada foto cetak',
      'File foto dikirim melalui link Google Drive',
      'Klien memilih foto yang akan diedit',
      'Hasil foto edit dikirim melalui link Google Drive',
    ],
    'variants' => [
      [
        'name' => 'Paket 1',
        'price' => 200000,
        'duration' => 20,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 20 Menit', '1 Background', '1 Outfit', '10 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 2',
        'price' => 300000,
        'duration' => 30,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 30 Menit', '1 Background', '2 Outfit', '20 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 3',
        'price' => 400000,
        'duration' => 45,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 45 Menit', '2 Background', '2 Outfit', '30 Foto Edit', 'All Original File (Google Drive)'],
      ],
    ],
  ],
  // CUSTOM PHOTOSHOOT STUDIO
  [
    'category_code' => 'CPS',
    'name' => 'Custom Photoshoot Studio',
    'features' => [
      'Tidak ada foto cetak',
      'Paket berlaku untuk photoshoot dengan konsep pilihan klien',
      'Harga belum termasuk untuk biaya dekorasi',
      'File foto original dikirim melalui link Google Drive',
      'Klien memilih file foto yang akan di edit',
      'File hasil edit dikirim melalui link Google Drive & Flashdisk',
    ],
    'variants' => [
      [
        'name' => 'Custom',
        'price' => 1000000,
        'duration' => 60,
        'is_whatsapp_only' => true,
        'features' => ['Durasi Maksimal 1 Jam', '1 Outfit', '1 Background', '30 Foto Edit', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
    ],
  ],
  // BIRTHDAY STUDIO
  [
    'category_code' => 'BDY',
    'name' => 'Birthday Studio',
    'features' => [
      'Tidak ada foto cetak',
      'Harga belum termasuk biaya dekorasi dan kue ulang tahun',
      'Tidak menerima vendor dekorasi selain dari rekanan kami',
      'Konsultasi konsep dekorasi dan kue ulang tahun silahkan hubungi admin',
    ],
    'variants' => [
      [
        'name' => 'Studio',
        'price' => 1000000,
        'duration' => 60,
        'is_whatsapp_only' => true,
        'features' => ['Durasi 60 Menit', '30 Foto Edit', 'All Original File (Google Drive)'],
      ],
    ],
  ],
  // BIRTHDAY EVENT
  [
    'category_code' => 'EVT',
    'name' => 'Birthday Event',
    'features' => [
      'Tidak ada foto cetak',
      'Harga belum termasuk biaya transportasi',
      'File hasil edit dikirim melalui link Google Drive & Flashdisk',
    ],
    'variants' => [
      [
        'name' => 'Foto',
        'price' => 2000000,
        'duration' => 240,
        'is_whatsapp_only' => true,
        'features' => ['Durasi 4 Jam', '2 Fotografer', '50 Foto Edit', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
      [
        'name' => 'Foto & Video',
        'price' => 4000000,
        'duration' => 240,
        'is_whatsapp_only' => true,
        'features' => ['Durasi 4 Jam', '2 Fotografer', '50 Foto Edit', '1 Videografer', 'Video 1 Menit', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
    ],
  ],
  // ENGAGEMENT
  [
    'category_code' => 'ENG',
    'name' => 'Engagement',
    'features' => [
      'Tidak ada foto cetak',
      'Harga belum termasuk biaya transportasi ke luar kota',
      'File hasil edit dikirim melalui link Google Drive & Flashdisk',
    ],
    'variants' => [
      [
        'name' => 'Foto',
        'price' => 3500000,
        'duration' => 240,
        'is_whatsapp_only' => true,
        'features' => ['Durasi Maksimal 4 Jam', '2 Fotografer', '1 Album Magazine + Box', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
      [
        'name' => 'Foto & Video',
        'price' => 5500000,
        'duration' => 240,
        'is_whatsapp_only' => true,
        'features' => ['Durasi Maksimal 4 Jam', '2 Fotografer', '1 Videografer', 'Video 1 Menit', '1 Album Magazine + Box', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
    ],
  ],
  // TRADITIONAL CEREMONY
  [
    'category_code' => 'TDC',
    'name' => 'Traditional Ceremony',
    'features' => [
      'Tidak ada foto cetak',
      'Harga belum termasuk biaya transportasi',
      'File hasil edit dikirim melalui link Google Drive & Flashdisk',
    ],
    'variants' => [
      [
        'name' => 'Foto',
        'price' => 3500000,
        'duration' => 240,
        'is_whatsapp_only' => true,
        'features' => ['Durasi Maksimal 4 Jam', '2 Fotografer', '1 Album Magazine + Box', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
      [
        'name' => 'Foto & Video',
        'price' => 5500000,
        'duration' => 240,
        'is_whatsapp_only' => true,
        'features' => ['Durasi Maksimal 4 Jam', '2 Fotografer', '1 Videografer', 'Video 1 Menit', '1 Album Magazine + Box', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
    ],
  ],
  // SYUKURAN
  [
    'category_code' => 'SYU',
    'name' => 'Syukuran',
    'features' => [
      'Tidak ada foto cetak',
      'Harga belum termasuk biaya transportasi',
      'File hasil edit dikirim melalui link Google Drive & Flashdisk',
    ],
    'variants' => [
      [
        'name' => 'Foto',
        'price' => 2000000,
        'duration' => 240,
        'is_whatsapp_only' => true,
        'features' => ['Durasi Maksimal 4 Jam', '2 Fotografer', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
      [
        'name' => 'Video',
        'price' => 2500000,
        'duration' => 240,
        'is_whatsapp_only' => true,
        'features' => ['1 Videografer', 'Video 1 Menit'],
      ],
    ],
  ],
  //  EVENT LAUNCHING 
  [
    'category_code' => 'EVT',
    'name' => 'Event Launching / Grand Opening',
    'features' => [
      'Tidak ada foto cetak',
      'Harga belum termasuk biaya transportasi',
      'File hasil edit dikirim melalui link Google Drive & Flashdisk',
    ],
    'variants' => [
      [
        'name' => 'Foto',
        'price' => 2000000,
        'duration' => 240,
        'is_whatsapp_only' => true,
        'features' => ['Durasi 4 Jam', '2 Fotografer', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
      [
        'name' => 'Video',
        'price' => 2500000,
        'duration' => 240,
        'is_whatsapp_only' => true,
        'features' => ['1 Videografer', 'Video 1 Menit'],
      ],
    ],
  ],
  //  COUPLE SESSION STUDIO 
  [
    'category_code' => 'CPL',
    'name' => 'Couple Session Studio',
    'features' => [
      'Tidak ada foto cetak',
      'Paket hanya untuk foto studio (indoor)',
      'Klien memilih background yang tersedia di studio',
      'Harga belum termasuk untuk biaya dekorasi',
      'File foto original dikirim melalui link Google Drive',
      'Klien memilih file foto yang akan di edit',
      'File hasil edit dikirim melalui link Google Drive & Flashdisk',
    ],
    'variants' => [
      [
        'name' => 'Paket 1',
        'price' => 1000000,
        'duration' => 60,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 1 Jam', 'Indoor Studio', '1 Background', '1 Outfit', '20 Foto Edit', '1 USB Flashdisk (All Files)'],
      ],
    ],
  ],
  //  COUPLE SESSION OUTDOOR
  [
    'category_code' => 'CPL',
    'name' => 'Couple Session Outdoor',
    'features' => [
      'Tidak ada foto cetak',
      'Hanya berlaku untuk lokasi atau daerah sekitar Kota Banjarbaru',
      'Harga belum termasuk untuk biaya dekorasi',
      'Harga belum termasuk biaya tambahan yang diperlukan untuk lokasi photoshoot',
      'File foto original dikirim melalui link Google Drive',
      'Klien memilih file foto yang akan di edit',
      'File hasil edit dikirim melalui link Google Drive & Flashdisk',
    ],
    'variants' => [
      [
        'name' => 'Paket 2',
        'price' => 2000000,
        'duration' => 180,
        'is_whatsapp_only' => true,
        'features' => ['Durasi 3 Jam', '2 Outfit', 'Indoor Studio / Outdoor', 'Maksimal 2 Lokasi Terdekat', '40 Foto Edit', '1 USB Flashdisk (All Files)'],
      ],
      [
        'name' => 'Paket 3',
        'price' => 3500000,
        'duration' => 480,
        'is_whatsapp_only' => true,
        'features' => ['1 Hari, Maksimal 8 Jam', '3 Outfit', 'Indoor Studio / Outdoor', 'Maksimal 3 Lokasi', '60 Foto Edit', '1 USB Flashdisk (All Files)'],
      ],
    ],
  ],
  //  MATERNITY STUDIO
  [
    'category_code' => 'MAT',
    'name' => 'Maternity Studio',
    'features' => [
      'Tidak ada foto cetak',
      'Klien memilih foto yang akan di edit',
      'File hasil edit dikirim melalui link Google Drive & Flashdisk',
    ],
    'variants' => [
      [
        'name' => 'Studio',
        'price' => 1000000,
        'duration' => 60,
        'is_whatsapp_only' => false,
        'features' => ['Durasi Maksimal 1 Jam', '1 Background', '2 Outfit', '30 Foto Edit', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
    ],
  ],
  //  MATERNITY OUTDOOR
  [
    'category_code' => 'MAT',
    'name' => 'Maternity Outdoor / Home Service',
    'features' => [
      'Tidak ada foto cetak',
      'Harga belum termasuk biaya transportasi dan biaya tambahan yang diperlukan untuk lokasi photoshoot',
      'File hasil edit dikirim melalui link Google Drive & Flashdisk',
    ],
    'variants' => [
      [
        'name' => 'Foto',
        'price' => 2000000,
        'duration' => 120,
        'is_whatsapp_only' => true,
        'features' => ['Durasi Maksimal 2 Jam', '1 Fotografer', '2 Outfit', '50 Foto Edit', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
      [
        'name' => 'Foto & Video',
        'price' => 4000000,
        'duration' => 120,
        'is_whatsapp_only' => true,
        'features' => ['Durasi Maksimal 2 Jam', '1 Fotografer', '1 Videografer', '2 Outfit', '50 Foto Edit', 'Video 1 Menit', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
    ],
  ],
  //  GROUP STUDIO
  [
    'category_code' => 'GRP',
    'name' => 'Group Studio',
    'features' => [
      'Tidak ada foto cetak',
      'Klien memilih background yang tersedia di studio',
      'Klien memilih foto yang akan diedit',
      'Hasil foto edit dikirim melalui link Google Drive',
      'Tambahan Rp25.000 / Orang (maksimal 10 orang)',
    ],
    'variants' => [
      [
        'name' => 'Paket 1',
        'price' => 250000,
        'duration' => 20,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 20 Menit', '1 Background', '1 Outfit', 'Maksimal 5 Orang', '10 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 2',
        'price' => 350000,
        'duration' => 30,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 30 Menit', '1 Background', '2 Outfit', 'Maksimal 8 Orang', '20 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 3',
        'price' => 500000,
        'duration' => 60,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 60 Menit', '2 Background', '2 Outfit', 'Maksimal 10 Orang', '30 Foto Edit', 'All Original File (Google Drive)'],
      ],
    ],
  ],
  //  FAMILY STUDIO
  [
    'category_code' => 'FAM',
    'name' => 'Family Studio',
    'features' => [
      'Tidak ada foto cetak',
      'Tidak diperbolehkan membawa kue ulang tahun',
      'Klien memilih background yang tersedia di studio',
      'File foto original dikirim melalui link Google Drive',
      'Klien memilih foto yang akan diedit',
      'Hasil foto edit dikirim melalui link Google Drive',
      'Tambahan Rp25.000 / Orang (maksimal 10 orang)',
    ],
    'variants' => [
      [
        'name' => 'Paket 1',
        'price' => 250000,
        'duration' => 20,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 20 Menit', '1 Background', '1 Outfit', 'Maksimal 5 Orang', '10 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 2',
        'price' => 350000,
        'duration' => 30,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 30 Menit', '1 Background', '2 Outfit', 'Maksimal 8 Orang', '20 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 3',
        'price' => 500000,
        'duration' => 60,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 60 Menit', '2 Background', '2 Outfit', 'Maksimal 10 Orang', '30 Foto Edit', 'All Original File (Google Drive)'],
      ],
    ],
  ],
  // OUTDOOR FAMILY & GROUP
  [
    'category_code' => 'FAM',
    'name' => 'Family & Group Outdoor / Home Service',
    'features' => [
      'Berlaku untuk satu tempat / lokasi photoshoot',
      'Tidak ada foto cetak',
      'Harga belum termasuk untuk biaya charge yang diperlukan untuk lokasi photoshoot',
      'File foto original dikirim melalui link Google Drive',
      'Klien memilih file foto yang akan di edit',
      'File hasil edit dikirim melalui link Google Drive & Flashdisk',
      'Free 1 Set studio lighting',
      'Free 1 Set Background muat untuk 15 orang (by request)',
      'Biaya tambahan durasi per 60 menit Rp500.000',
      'Biaya tambahan edit per foto Rp15.000',
    ],
    'variants' => [
      [
        'name' => 'Small',
        'price' => 1500000,
        'duration' => 60,
        'is_whatsapp_only' => true,
        'features' => ['Durasi 1 Jam', 'Maksimal 5 Orang', '20 Foto Edit'],
      ],
      [
        'name' => 'Medium',
        'price' => 2000000,
        'duration' => 120,
        'is_whatsapp_only' => true,
        'features' => ['Durasi 2 Jam', 'Maksimal 15 Orang', '40 Foto Edit'],
      ],
      [
        'name' => 'Large',
        'price' => 3000000,
        'duration' => 180,
        'is_whatsapp_only' => true,
        'features' => ['Durasi 3 Jam', 'Lebih dari 15 Orang', '60 Foto Edit'],
      ],
    ],
  ],
  // GRADUATION STUDIO
  [
    'category_code' => 'GRD',
    'name' => 'Graduation Studio',
    'features' => [
      'Tidak ada foto cetak',
      'Klien memilih background yang tersedia di studio',
      'File foto original dikirim melalui link Google Drive',
      'Klien memilih foto yang akan diedit',
      'Hasil foto edit dikirim melalui link Google Drive',
      'Tambahan Rp25.000 / Orang (maksimal 10 orang) *studio',
    ],
    'variants' => [
      [
        'name' => 'Paket 1',
        'price' => 250000,
        'duration' => 20,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 20 Menit', '1 Background', '1 Outfit', 'Maksimal 5 Orang', '10 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 2',
        'price' => 350000,
        'duration' => 30,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 30 Menit', '1 Background', '2 Outfit', 'Maksimal 8 Orang', '20 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 3',
        'price' => 500000,
        'duration' => 60,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 60 Menit', '2 Background', '2 Outfit', 'Maksimal 10 Orang', '30 Foto Edit', 'All Original File (Google Drive)'],
      ],
    ],
  ],
  //  GRADUATION ON THE SPOT
  [
    'category_code' => 'GRD',
    'name' => 'Graduation On The Spot',
    'features' => [
      'Tidak ada foto cetak',
      'Klien memilih foto yang akan diedit',
      'Hasil foto edit dikirim melalui link Google Drive',
    ],
    'variants' => [
      [
        'name' => 'On The Spot',
        'price' => 500000,
        'duration' => 30,
        'is_whatsapp_only' => true,
        'features' => ['Durasi 30 Menit', '1 Lokasi', '20 Foto Edit', 'All Original File (Google Drive)'],
      ],
    ],
  ],
  //  WEDDING ALL IN ONE
  [
    'category_code' => 'WDG',
    'name' => 'Wedding All In One',
    'features' => [
      '2 Album Magazine Hard Cover + Box',
      '2 Album Magnetic + 240 Foto Ukuran 4R',
      '3 Foto Cetak ukuran 16RJ + Figura',
      '1 Menit Video Engagement',
      '1 Menit Video Traditional Ceremony',
      '1 Menit & 3 Menit Video Wedding',
      '1 USB Flashdisk (All Files)',
    ],
    'variants' => [
      [
        'name' => 'All In One',
        'price' => 21500000,
        'duration' => 480,
        'is_whatsapp_only' => true,
        'features' => [
          'Engagement: Durasi 4 Jam, 2 Fotografer, 1 Videografer',
          'Traditional Ceremony: Durasi 4 Jam, 1 Acara, 2 Fotografer, 1 Videografer',
          'Wedding: 1 Hari Durasi 8 Jam, 1 Lokasi, 3 Fotografer, 2 Videografer',
          'Couple Session: Durasi 1 Jam, 1 Fotografer, Indoor Studio, 1 Outfit, 20 Foto Edit',
        ],
      ],
    ],
  ],
  //  WEDDING FOTO & VIDEO 
  [
    'category_code' => 'WDG',
    'name' => 'Wedding Foto & Video',
    'features' => [
      'Tidak ada foto cetak',
      'Harga belum termasuk biaya transportasi ke luar kota',
      'File hasil edit dikirim melalui link Google Drive & Flashdisk',
    ],
    'variants' => [
      [
        'name' => 'Paket 1',
        'price' => 5500000,
        'duration' => 480,
        'is_whatsapp_only' => true,
        'features' => ['1 Hari Acara', 'Durasi Maksimal 8 Jam', '1 Lokasi', '2 Fotografer', '1 Videografer', 'Video 1 Menit', '1 Album Magazine + Box', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
      [
        'name' => 'Paket 2',
        'price' => 10000000,
        'duration' => 480,
        'is_whatsapp_only' => true,
        'features' => ['1 Hari Acara', 'Durasi Maksimal 8 Jam', '2 Lokasi', '3 Fotografer', 'Full Dokumentasi', '2 Videografer', 'Video 1 Menit & Video 3 Menit', '1 Album Magazine Hard Cover + Box', '1 Album Magnetic + 120 Foto 4R', '2 Foto Cetak Ukuran 16RJ + Figura', '1 USB Flashdisk (All Files)'],
      ],
    ],
  ],
];

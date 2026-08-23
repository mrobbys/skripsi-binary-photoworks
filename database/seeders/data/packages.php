<?php

return [
  // PERSONAL STUDIO
  [
    'category_code' => 'PSN',
    'name' => 'Personal Studio',
    'image' => 'https://images.unsplash.com/photo-1779400881920-b9f0b5dd0085?w=800&q=80',
    'description' => 'Ekspresikan diri Anda dengan sesi pemotretan studio personal. Cocok untuk profil profesional, portofolio, atau sekadar merayakan momen unik Anda dengan kualitas gambar terbaik.',
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
        'features' => ['Durasi 20 Menit', '1 Outfit', '10 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 2',
        'price' => 300000,
        'duration' => 30,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 30 Menit', '2 Outfit', '20 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 3',
        'price' => 400000,
        'duration' => 45,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 45 Menit', '2 Outfit', '30 Foto Edit', 'All Original File (Google Drive)'],
      ],
    ],
  ],
  // CUSTOM PHOTOSHOOT STUDIO
  [
    'category_code' => 'CPS',
    'name' => 'Custom Photoshoot Studio',
    'image' => 'https://images.unsplash.com/photo-1647427854253-b92bb40c9330?w=800&q=80',
    'description' => 'Wujudkan konsep impian Anda dengan sesi foto kustom. Mulai dari gaya editorial hingga konsep tematik khusus, kami siap mendokumentasikan visi kreatif Anda di studio.',
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
        'is_whatsapp_only' => false,
        'features' => ['Durasi Maksimal 1 Jam', '1 Outfit', '30 Foto Edit', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
    ],
  ],
  // BIRTHDAY STUDIO
  [
    'category_code' => 'BDY',
    'name' => 'Birthday Studio',
    'image' => 'https://images.unsplash.com/photo-1675130227127-26c6e0c4a729?w=800&q=80',
    'description' => 'Abadikan momen perayaan ulang tahun Anda atau orang terkasih dengan gaya yang menyenangkan di studio. Jadikan hari spesial ini tak terlupakan dalam bingkai foto yang ceria.',
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
        'is_whatsapp_only' => false,
        'features' => ['Durasi 60 Menit', '30 Foto Edit', 'All Original File (Google Drive)'],
      ],
    ],
  ],
  // BIRTHDAY EVENT
  [
    'category_code' => 'EVT',
    'name' => 'Birthday Event',
    'image' => 'https://images.unsplash.com/photo-1765947383567-a7be6d558c6b?w=800&q=80',
    'description' => 'Dokumentasi lengkap untuk kemeriahan pesta ulang tahun Anda. Kami hadir langsung ke lokasi acara untuk menangkap setiap tawa, kejutan, dan momen bahagia bersama tamu undangan.',
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
    'image' => 'https://images.unsplash.com/photo-1501901609772-df0848060b33?w=800&q=80',
    'description' => 'Simpan kenangan manis langkah awal menuju pernikahan Anda. Sesi pertunangan ini dirancang untuk merekam janji suci dan kehangatan cinta antara Anda dan pasangan.',
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
    'image' => 'https://images.unsplash.com/photo-1525272149490-82288cb110a0?w=800&q=80',
    'description' => 'Hargai dan abadikan nilai-nilai budaya dalam acara adat Anda. Kami menangkap setiap prosesi sakral dengan detail dan penuh makna untuk diwariskan ke generasi berikutnya.',
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
    'image' => 'https://images.unsplash.com/photo-1688100099236-0bb64e229d7d?w=800&q=80',
    'description' => 'Dokumentasi momen penuh syukur dan kebersamaan keluarga. Kami merekam setiap rangkaian acara syukuran Anda dengan nuansa yang hangat dan khidmat.',
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
    'image' => 'https://images.unsplash.com/photo-1561489413-985b06da5bee?w=800&q=80',
    'description' => 'Liputan profesional untuk peluncuran produk atau peresmian bisnis Anda. Tangkap kemeriahan acara, interaksi tamu, dan momen penting untuk keperluan publikasi dan arsip perusahaan.',
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
    'image' => 'https://images.unsplash.com/photo-1763713512956-1f2f575814e6?w=800&q=80',
    'description' => 'Rayakan romantisme bersama pasangan dengan sesi foto berdua yang intim di studio. Ciptakan kenangan abadi dengan berbagai pilihan latar belakang yang elegan.',
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
        'features' => ['Durasi 1 Jam', 'Indoor Studio', '1 Outfit', '20 Foto Edit', '1 USB Flashdisk (All Files)'],
      ],
    ],
  ],
  //  COUPLE SESSION OUTDOOR
  [
    'category_code' => 'CPL',
    'name' => 'Couple Session Outdoor',
    'image' => 'https://images.unsplash.com/photo-1739312023925-19eca8ca00aa?w=800&q=80',
    'description' => 'Eksplorasi gaya kasual dan romantis di alam terbuka. Sesi foto pasangan di lokasi outdoor pilihan yang memberikan nuansa natural dan kebebasan berekspresi.',
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
    'image' => 'https://images.unsplash.com/photo-1649949474530-51fd3d999837?w=800&q=80',
    'description' => 'Abadikan keindahan masa kehamilan dengan sesi foto maternity yang elegan di studio. Kenang momen penantian sang buah hati dengan pencahayaan dan pose yang artistik.',
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
        'features' => ['Durasi Maksimal 1 Jam', '2 Outfit', '30 Foto Edit', 'All Original File', '1 USB Flashdisk (All Files)'],
      ],
    ],
  ],
  //  MATERNITY OUTDOOR
  [
    'category_code' => 'MAT',
    'name' => 'Maternity Outdoor / Home Service',
    'image' => 'https://images.unsplash.com/photo-1697295147805-c4a8f27ec05f?w=800&q=80',
    'description' => 'Sesi foto kehamilan yang lebih santai dan personal, baik di lokasi outdoor favorit maupun kenyamanan rumah Anda sendiri. Menangkap kehangatan keluarga dalam suasana yang natural.',
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
    'image' => 'https://images.unsplash.com/photo-1772723246503-6d8770130bf2?w=800&q=80',
    'description' => 'Ajak sahabat atau kolega Anda untuk sesi pemotretan grup yang seru di studio. Kenang kebersamaan dan kekompakan kalian dengan hasil foto yang tajam dan profesional.',
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
        'features' => ['Durasi 20 Menit', '1 Outfit', 'Maksimal 5 Orang', '10 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 2',
        'price' => 350000,
        'duration' => 30,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 30 Menit', '2 Outfit', 'Maksimal 8 Orang', '20 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 3',
        'price' => 500000,
        'duration' => 60,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 60 Menit', '2 Outfit', 'Maksimal 10 Orang', '30 Foto Edit', 'All Original File (Google Drive)'],
      ],
    ],
  ],
  //  FAMILY STUDIO
  [
    'category_code' => 'FAM',
    'name' => 'Family Studio',
    'image' => 'https://images.unsplash.com/photo-1758513359379-a1ccce73b09e?w=800&q=80',
    'description' => 'Kumpulkan keluarga tercinta untuk potret keluarga yang hangat dan tak lekang oleh waktu. Sesi foto studio yang nyaman untuk semua anggota keluarga, dari anak-anak hingga kakek-nenek.',
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
        'features' => ['Durasi 20 Menit', '1 Outfit', 'Maksimal 5 Orang', '10 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 2',
        'price' => 350000,
        'duration' => 30,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 30 Menit', '2 Outfit', 'Maksimal 8 Orang', '20 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 3',
        'price' => 500000,
        'duration' => 60,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 60 Menit', '2 Outfit', 'Maksimal 10 Orang', '30 Foto Edit', 'All Original File (Google Drive)'],
      ],
    ],
  ],
  // OUTDOOR FAMILY & GROUP
  [
    'category_code' => 'FAM',
    'name' => 'Family & Group Outdoor / Home Service',
    'image' => 'https://images.unsplash.com/photo-1542037104857-ffbb0b9155fb?w=800&q=80',
    'description' => 'Pemotretan keluarga besar atau grup dalam suasana yang lebih leluasa, baik di luar ruangan maupun di rumah. Cocok untuk acara kumpul keluarga dengan nuansa yang lebih hidup dan dinamis.',
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
    'image' => 'https://images.unsplash.com/photo-1659080907100-23f0dac0fe27?w=800&q=80',
    'description' => 'Rayakan pencapaian akademis Anda dengan potret kelulusan resmi di studio. Tampil membanggakan dengan toga dan ijazah bersama teman atau keluarga terkasih.',
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
        'features' => ['Durasi 20 Menit', '1 Outfit', 'Maksimal 5 Orang', '10 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 2',
        'price' => 350000,
        'duration' => 30,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 30 Menit', '2 Outfit', 'Maksimal 8 Orang', '20 Foto Edit', 'All Original File (Google Drive)'],
      ],
      [
        'name' => 'Paket 3',
        'price' => 500000,
        'duration' => 60,
        'is_whatsapp_only' => false,
        'features' => ['Durasi 60 Menit', '2 Outfit', 'Maksimal 10 Orang', '30 Foto Edit', 'All Original File (Google Drive)'],
      ],
    ],
  ],
  //  GRADUATION ON THE SPOT
  [
    'category_code' => 'GRD',
    'name' => 'Graduation On The Spot',
    'image' => 'https://images.unsplash.com/photo-1722648325285-058946b4487b?w=800&q=80',
    'description' => 'Dokumentasi langsung di lokasi acara wisuda Anda. Kami menangkap momen-momen spontan yang penuh kebanggaan dan haru sesaat setelah Anda resmi diwisuda.',
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
    'image' => 'https://images.unsplash.com/photo-1583939411023-14783179e581?w=800&q=80',
    'description' => 'Paket dokumentasi pernikahan komprehensif dari awal hingga akhir. Mencakup sesi lamaran, acara adat, hingga hari pernikahan untuk memastikan tidak ada satu momen pun yang terlewatkan.',
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
    'image' => 'https://images.unsplash.com/photo-1571753217197-b28b8f889b7a?w=800&q=80',
    'description' => 'Abadikan hari pernikahan Anda dalam bentuk foto dan video sinematik yang memukau. Liputan profesional yang merekam keindahan, emosi, dan janji suci di hari bahagia Anda.',
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

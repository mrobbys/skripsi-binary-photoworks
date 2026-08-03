const faqData = [
  {
    id: "umum",
    label: "Umum",
    items: [
      {
        q: "Apa itu Binary Photoworks?",
        a: "Layanan fotografi untuk mengabadikan momen. Reservasi sesi dilakukan sepenuhnya online: pilih paket, atur jadwal, bayar, dan pantau lewat Dashboard.",
      },
      {
        q: "Apakah saya harus daftar untuk memesan?",
        a: "Ya, Anda wajib mendaftar dan login untuk bisa masuk ke halaman checkout dan melakukan pemesanan.",
      },
      {
        q: "Bagaimana cara daftar akun?",
        a: "Anda membutuhkan: Nama Lengkap, Email, Nomor Telepon, Password (min 8 karakter, kombinasi huruf besar/kecil & angka). Atau bisa masuk pakai Google.",
      },
      {
        q: "Bagaimana format nomor HP saat mendaftar?",
        a: "Wajib diawali 62 (tanpa 0/+), panjang 10-14 digit. Nomor ini dipakai untuk notifikasi WhatsApp.",
      },
      {
        q: "Apakah semua varian paket bisa dipesan secara online?",
        a: "Tidak. Sebagian varian bersifat WhatsApp-only: Anda diarahkan reservasi langsung via WhatsApp dengan pesan otomatis berisi nama paket.",
      },
      {
        q: "Bagaimana saya tahu hari/jam operasional studio?",
        a: "Hari & jam aktif diatur admin, slot yang tersedia ditampilkan di kalender saat memilih jadwal. Slot di luar jam operasional tidak muncul.",
      },
      {
        q: "Siapa yang bisa melakukan pemesanan?",
        a: "Pengguna yang sudah terdaftar bisa melakukan pemesanan.",
      },
    ],
  },
  {
    id: "pemesanan",
    label: "Pemesanan",
    items: [
      {
        q: "Bagaimana alur pemesanan di Binary Photoworks?",
        a: "4 langkah: (1) pilih varian paket & background, (2) pilih tanggal & jam, (3) layanan tambahan (opsional), (4) ringkasan, bayar, sukses.",
      },
      {
        q: "Berapa lama durasi untuk satu sesi foto?",
        a: "Sesuai durasi tiap varian paket, dihitung dari jam mulai hingga selesai (mis. paket 60 menit, slot 60 menit).",
      },
      {
        q: "Kenapa tanggal/jam tertentu tidak bisa dipilih?",
        a: "Hari tersebut tidak masuk jadwal operasional, jam tersebut sudah terisi booking lain, atau waktunya sudah lewat.",
      },
      {
        q: "Apakah saya bisa pesan untuk hari ini?",
        a: "Bisa, selama jam sesi masih tersedia dan belum lewat waktu sekarang.",
      },
      {
        q: "Apa yang dimaksud dengan varian paket?",
        a: "Sub-pilihan dalam satu paket yang membedakan harga, durasi, dan/atau fasilitas (mis. Paket 1 / Paket 2).",
      },
      {
        q: "Apa itu Layanan Tambahan?",
        a: "Tambahan opsional yang diperhitungkan ke total, dipilih langsung saat pemesanan.",
      },
      {
        q: "Apakah saya bisa memilih jumlah (quantity) untuk layanan tambahan?",
        a: "Ya, untuk layanan tambahan tertentu, Anda bisa menyesuaikan jumlah (quantity) yang ingin dipesan secara langsung saat checkout.",
      },
      {
        q: "Apakah bisa mengisi catatan pada pemesanan?",
        a: "Ya, ada kolom catatan saat checkout untuk kebutuhan tambahan.",
      },
      {
        q: "Berapa kali saya bisa mengubah jadwal (reschedule)?",
        a: "Maksimal 3 kali, hanya selama status Menunggu / DP / Lunas, dan minimal H-1 (lebih dari 24 jam) sebelum jadwal lama.",
      },
      {
        q: "Kapan batas waktu blokir perubahan jadwal?",
        a: "Sesi sudah kurang dari 24 jam lagi (H-1), jadwal tidak bisa diubah.",
      },
      {
        q: "Bisakah saya membatalkan pemesanan?",
        a: "Bisa, selama status masih Menunggu Pembayaran. Setelah DP/lunas masuk, sistem menolak pembatalan, silahkan hubungi admin informasi lebih lanjut.",
      },
      {
        q: "Bagaimana jika slot sudah terisi saat saya menekan tombol checkout?",
        a: "Sistem menolaknya real-time, Anda dapat memilih ulang jam lain yang masih tersedia.",
      },
      {
        q: "Bagaimana cara memantau status pesanan saya?",
        a: "Lewat Dashboard, tab Jadwal/Mendatang.",
      },
    ],
  },
  {
    id: "pembayaran",
    label: "Pembayaran",
    items: [
      {
        q: "Metode pembayaran apa saja yang tersedia?",
        a: "Melalui Midtrans: QRIS, E-Wallet, dll. Pilihan muncul saat bayar.",
      },
      {
        q: "Skema pembayaran apa saja yang disediakan?",
        a: "Dua pilihan saat checkout: DP 60% (uang muka) atau Lunas 100%.",
      },
      {
        q: "Kapan sisa 40% (untuk skema DP) harus dilunasi?",
        a: "Dibayarkan di kasir studio pada hari pelaksanaan sesi.",
      },
      {
        q: "Berapa lama batas waktu untuk membayar?",
        a: "1 jam dari checkout. Lewat dari itu, tagihan dibatalkan dan reservasi otomatis batal.",
      },
      {
        q: "Bagaimana jika saya telat membayar atau pembayaran gagal?",
        a: "Status berubah Dibatalkan, silakan buat reservasi baru.",
      },
      {
        q: "Di mana saya bisa melihat bukti pembayaran?",
        a: "Kuitansi PDF, bisa diunduh dari tautan di notifikasi WhatsApp maupun di Dashboard.",
      },
      {
        q: "Apa yang terjadi setelah DP dibayar?",
        a: "Status menjadi DP Terbayar (60%), notifikasi WhatsApp + kuitansi DP terkirim.",
      },
      {
        q: "Bagaimana jika terjadi kelebihan bayar pada pesanan saya?",
        a: "Jika terdapat kelebihan pembayaran, sistem akan mencatatnya dan tim admin kami akan memproses pengembalian dana (refund) tersebut untuk Anda.",
      },
    ],
  },
  {
    id: "hasil-foto",
    label: "Hasil Foto",
    items: [
      {
        q: "Kapan hasil foto saya dikirim?",
        a: "Setelah sesi selesai dan akan dishare via tautan Google Drive.",
      },
      {
        q: "Bagaimana cara saya menerima hasil foto tersebut?",
        a: "Admin mengunggah tautan Google Drive, lalu tautan dikirim via WhatsApp dan tampil di Dashboard Anda.",
      },
      {
        q: "Apakah hasil foto dibagikan ke semua pesanan?",
        a: "Ya, tapi hasil foto hanya dibagikan untuk sesi yang statusnya sudah Lunas/Selesai.",
      },
      {
        q: "Apakah ada perubahan status setelah foto dikirim?",
        a: "Booking berubah menjadi Selesai begitu hasil foto diunggah.",
      },
      {
        q: "Bisakah tautan foto diunduh berulang kali?",
        a: "Anda bisa mengakses tautan Google Drive kapan pun.",
      },
      {
        q: "Berapa lama hasil foto diproses?",
        a: "Tidak ada patokan waktu, umumnya kami akan mengunggah setelah sesi, detail waktu dapat dikonfirmasi ke tim.",
      },
    ],
  },
];

export default faqData;

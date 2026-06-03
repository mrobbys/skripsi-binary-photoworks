# Documentation: 02 - Database Schema & Kamus Data

## 1. Objective & Database Architecture

Dokumen ini berfungsi sebagai panduan mutlak (_single-source-of-truth_) untuk arsitektur database aplikasi **Binary Photoworks**. Database menggunakan **PostgreSQL (Supabase)**. Seluruh tabel wajib mematuhi standar penamaan Eloquent Laravel (snake_case, jamak untuk nama tabel, tunggal + \_id untuk foreign key).

### Aturan Global Migrasi Laravel:

- Semua tabel utama wajib menggunakan `$table->id()` yang menghasilkan tipe data `bigint unsigned auto-increment`.
- Semua tabel utama wajib dilengkapi dengan `$table->timestamps()` untuk mencatat `created_at` dan `updated_at`.
- Penonaktifan data master tidak menggunakan mekanisme Soft Deletes, melainkan murni dikendalikan oleh kolom status aktivitas (`is_active` boolean).

---

## 2. Complete DBML Syntax (For dbdiagram.io)

Gunakan kode DBML di bawah ini untuk melakukan pemetaan ulang atau visualisasi ERD pada platform `dbdiagram.io`:

```text
// ==========================================
// SECTION 1: AUTENTIKASI & HAK AKSES (SPATIE)
// ==========================================

Table users {
  id bigint [primary key, increment]
  uuid uuid [unique]           // digunakan untuk URL admin (anti-enumeration)
  google_id varchar [null, unique]
  google_token text [null]
  name varchar
  email varchar [unique]
  password varchar
  phone varchar(15) [null, unique]
  remember_token varchar [null]
  created_at timestamp
  updated_at timestamp
}

Table roles {
  id bigint [primary key, increment]
  name varchar(255) [not null]
  guard_name varchar(255) [not null]
  created_at timestamp
  updated_at timestamp

  Indexes {
    (name, guard_name) [unique]
  }
}

Table permissions {
  id bigint [primary key, increment]
  name varchar(255) [not null]
  guard_name varchar(255) [not null]
  created_at timestamp
  updated_at timestamp

  Indexes {
    (name, guard_name) [unique]
  }
}

Table model_has_roles {
  role_id bigint [not null]
  model_type varchar(255) [not null]
  model_id bigint [not null]

  Indexes {
    (role_id, model_id, model_type) [pk]
    (model_id, model_type)
  }
}

Table role_has_permissions {
  permission_id bigint [not null]
  role_id bigint [not null]

  Indexes {
    (permission_id, role_id) [pk]
  }
}

Table model_has_permissions {
  permission_id bigint [not null]
  model_type varchar(255) [not null]
  model_id bigint [not null]

  Indexes {
    (permission_id, model_id, model_type) [pk]
    (model_id, model_type)
  }
}

Table activity_log {
  id bigint [primary key, increment]
  log_name varchar
  description text
  causer_type varchar
  causer_id bigint
  created_at timestamp
  updated_at timestamp
}

// ==========================================
// SECTION 2: DATA MASTER & OPERASIONAL
// ==========================================

Table categories {
  id bigint [primary key, increment]
  category_code char(3) [unique]  // 3 huruf kapital, diisi manual admin, digunakan pada formula booking code
  name varchar
  slug varchar [unique]
  is_active boolean [default: true]
  created_at timestamp
  updated_at timestamp
}

Table packages {
  id bigint [primary key, increment]
  category_id bigint
  name varchar
  slug varchar [unique]
  is_active boolean [default: true]
  created_at timestamp
  updated_at timestamp
}

Table package_variants {
  id bigint [primary key, increment]
  package_id bigint
  name varchar
  price int
  duration int
  is_whatsapp_only boolean [default: false]
  is_active boolean [default: true]
  created_at timestamp
  updated_at timestamp
}

Table schedules {
  id bigint [primary key, increment]
  day int                        // representasi ISO 8601: Senin=1, Selasa=2, ..., Minggu=7 (sesuai Carbon::dayOfWeekIso)
  start_time time
  end_time time
  is_active boolean [default: true]
  created_at timestamp
  updated_at timestamp
}

Table features {
  id bigint [primary key, increment]
  description text               // text (bukan varchar) untuk mengakomodasi deskripsi panjang
  featureable_type varchar       // polimorfik — 'App\\Domains\\Package\\Models\\Package' atau 'PackageVariant'
  featureable_id bigint
  created_at timestamp
  updated_at timestamp

  Indexes {
    (featureable_type, featureable_id)  // composite index untuk performa query polimorfik
  }
}

Table backgrounds {
  id bigint [primary key, increment]
  name varchar
  description text [null]
  is_active boolean [default: true]
  created_at timestamp
  updated_at timestamp
}

Table background_package_variant {
  background_id bigint
  package_variant_id bigint

  Indexes {
    (background_id, package_variant_id) [pk]
  }
}

Table addons {
  id bigint [primary key, increment]
  name varchar
  price int
  description text
  has_quantity boolean [default: false]
  is_active boolean [default: true]
  created_at timestamp
  updated_at timestamp
}

Table media {
  id bigint [primary key, increment]
  model_type varchar
  model_id bigint
  uuid uuid [unique]
  collection_name varchar
  name varchar
  file_name varchar
  mime_type varchar
  disk varchar
  conversions_disk varchar [null]
  size bigint
  manipulations json
  custom_properties json
  generated_properties json
  responsive_images json
  order_column int [null]
  created_at timestamp
  updated_at timestamp

  Indexes {
    (model_type, model_id)
  }
}

// ==========================================
// SECTION 3: TRANSAKSI & ULASAN
// ==========================================

Table bookings {
  id bigint [primary key, increment]
  user_id bigint
  package_variant_id bigint
  background_id bigint [null]
  booking_code varchar [unique]
  booking_date date
  start_time time
  end_time time
  total_price int
  payment_scheme varchar
  status varchar
  keterangan text
  gdrive_link varchar [null]
  created_at timestamp
  updated_at timestamp

  Indexes {
    (booking_date, start_time, end_time) [name: "bookings_schedule_composite_index"]
  }
}

Table addon_booking {
  booking_id bigint
  addon_id bigint
  price_at_purchase int
  quantity int [default: 1]

  Indexes {
    (booking_id, addon_id) [pk]
  }
}

Table payments {
  id bigint [primary key, increment]
  booking_id bigint
  order_id varchar [unique]
  payment_type varchar
  payment_purpose varchar
  snap_token varchar [null]
  payment_method varchar
  amount int
  status varchar
  pay_date datetime [null]
  created_at timestamp
  updated_at timestamp
}

Table reviews {
  id bigint [primary key, increment]
  user_id bigint
  rating int
  comment text
  created_at timestamp
  updated_at timestamp
}

// ==========================================
// SECTION 4: MAP RELASI DATA
// ==========================================

Ref: model_has_roles.role_id > roles.id
Ref: role_has_permissions.permission_id > permissions.id
Ref: role_has_permissions.role_id > roles.id
Ref: model_has_permissions.permission_id > permissions.id

Ref: packages.category_id > categories.id
Ref: package_variants.package_id > packages.id

Ref: bookings.user_id > users.id
Ref: bookings.package_variant_id > package_variants.id
Ref: bookings.background_id > backgrounds.id
Ref: payments.booking_id > bookings.id
Ref: reviews.user_id > users.id

Ref: background_package_variant.background_id > backgrounds.id
Ref: background_package_variant.package_variant_id > package_variants.id
Ref: addon_booking.booking_id > bookings.id
Ref: addon_booking.addon_id > addons.id
```

---

## 3. Aturan Bisnis & Algoritma Generator Kode Booking (BPW Standard)

Untuk menjamin keunikan data, keterbacaan tingkat tinggi (_human-readable_), serta mencegah serangan tebakan ID transaksi (_anti-enumeration attack_), sistem menggunakan standarisasi kode transaksi terstruktur.

### A. Struktur Formula Eksklusif

Sistem menyatukan 4 segmen data menjadi satu kesatuan string tunggal menggunakan pembatas tanda hubung (_hyphen_):

$$
\text{Booking Code} = \text{Prefix} - \text{PackageSegment} - \text{DateSegment} - \text{RandomSegment}
$$

### B. Breakdown Komponen Batasan Data

1. **Prefix (3 Karakter)**: String statis `BPW` sebagai identitas mutlak dari _Binary Photoworks_.
2. **Package Segment (5 Karakter)**:
    - 3 Karakter awal diambil dari `categories.category_code` (Contoh: `WSD` untuk Wisuda, `FML` untuk Family).
    - 2 Karakter akhir diambil dari `package_variants.id` yang dibungkus fungsi `str_pad(id, 2, '0', STR_PAD_LEFT)` (Contoh: ID varian 1 menjadi `01`).
3. **Date Segment (6 Karakter)**: Tanggal pelaksanaan sesi foto dari form booking klien yang dikonversi menjadi format `YYMMDD` melalui Carbon (Contoh: 30 Mei 2026 menjadi `260530`).
4. **Random Segment (3 Karakter)**: String acak Alphanumeric berhuruf kapital (Caps Lock) menggunakan `Str::upper(Str::random(3))` untuk memastikan kode tidak dapat ditebak oleh pihak luar (Contoh: `7AX`).

### C. Contoh Output Kompilasi Sistem

- **Sesi Wisuda Varian 1 pada 30 Mei 2026**: `BPW-WSD01-260530-7AX`
- **Sesi Family Varian 4 pada 01 Juni 2026**: `BPW-FML04-260601-9BZ`

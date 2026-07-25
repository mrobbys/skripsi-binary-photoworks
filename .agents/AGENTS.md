# Aturan Lingkup Proyek (Project-Scoped Rules)

## Batasan Sistem Desain (ThoughtStream)
- **TANPA SUDUT MELENGKUNG (NO ROUNDED CORNERS)**: Sudut tajam (radius 0px) untuk semua elemen interaktif, kontainer, placeholder, skeleton, dan layout.
  - Jangan pernah menggunakan class `rounded-*` (seperti `rounded`, `rounded-sm`, `rounded-md`, `rounded-lg`, dll).
  - SATU-SATUNYA pengecualian adalah avatar pengguna, yang harus menggunakan `rounded-full`.
- **TANPA BAYANGAN (NO SHADOWS)**: Jangan pernah menggunakan class `shadow-*`. Pemisahan elemen dicapai secara eksklusif menggunakan border (garis tepi) dan spacing (jarak).
- **PALET WARNA TERBATAS**: Elemen anchor, background, dan gaya teks hanya menggunakan palet warna netral hangat `stone`.
- **TANPA GRADASI (NO GRADIENTS)**: Jangan gunakan class `bg-gradient-to-*`. Gunakan warna latar belakang yang rata (flat).

## Batasan Alur Kerja (Workflow Constraints)
- **TANPA MODIFIKASI OTOMATIS (NO AUTO-MODIFICATION)**: Jangan secara otomatis memodifikasi, membuat, atau menulis ulang kode proyek (menggunakan alat seperti `replace_file_content`, `write_to_file`, dll.) kecuali pengguna SECARA EKSPLISIT menginstruksikan untuk melakukannya (misalnya, "perbaiki", "tulis kode", "implementasikan", "langsung rubah").
- **JELASKAN TERLEBIH DAHULU (EXPLAIN FIRST)**: Saat diminta untuk menyelidiki bug atau error, jelaskan saja akar masalahnya dan berikan solusi di kolom obrolan. Tunggu konfirmasi eksplisit dari pengguna sebelum menerapkan perbaikan apa pun.
- **TANPA COMMIT OTOMATIS (NO AUTO-COMMIT)**: Jangan jalankan `git add` atau `git commit` secara otomatis setelah membuat perubahan. Biarkan pengguna meninjau dan menguji kodenya terlebih dahulu. Lakukan commit hanya ketika diperintahkan secara eksplisit (misalnya, "oke sip mari kita add commit", "commit sekarang").

## Tech Stack
- **Framework:** Laravel ^13.8, PHP ^8.3
- **Database:** PostgreSQL (Supabase)
- **Frontend:** Alpine.js, Tailwind CSS v3, Vite
- **Payment:** Midtrans
- **WA Gateway:** Fonnte
- **Queue:** Database driver (`php artisan queue:listen` runs continuously)

## Arsitektur & Struktur
- **Domain-per-feature** di `app/Domains/{Domain}/{Layers}/`
- Layers per domain: `Models/`, `Services/`, `Http/Controllers/`, `Http/Requests/`, `DTOs/`, `Enums/`
- **Blade:** `resources/views/{frontdoor|backdoor}/`
- **JS (Alpine):** `resources/js/features/{frontdoor|backdoor}/`
- **Component:** `x-backdoor.*` dan `x-frontdoor.*`

## Backend Coding Rules
Wajib gunakan skill berikut saat menulis/mengedit kode backend:
- `@.agents/skills/laravel-13-skills/`
- `@.agents/skills/spatie-laravel-php/`
- `@.agents/skills/eloquent-best-practices/`
- `@.agents/skills/supabase-postgres-best-practices/`

## Frontend Coding Rules
Wajib gunakan skill berikut saat menulis/mengedit kode frontend:
- `@.agents/skills/alpinejs/`
- `@.agents/skills/design-taste-frontend/`
- `@.agents/skills/modern-javascript-patterns/`
- `@.agents/skills/tailwindcss-advanced-layouts/`
- `@.agents/skills/accessibility/`

## Review & Debug Rules

| Skenario | Skill | Keterangan |
|----------|-------|------------|
| Review PR/diff (cepat) | `@.agents/skills/caveman-review/` | One-liner per temuan, langsung paste ke PR |
| Review PR/diff (formal) | `@.agents/skills/code-review/` | Dua-axis: coding standards + spec compliance |
| Audit keamanan | `@.agents/skills/find-bugs/` | Checklist 11 item: injection, XSS, auth, CSRF, dll |
| Debug error/bug | `@.agents/skills/diagnosing-bugs/` | Loop: reproduce → minimise → hypothesise → fix → test |

## CodeGraph
- Proyek sudah terindex CodeGraph di `.codegraph/`
- Gunakan `codegraph explore "<query>"` untuk eksplorasi codebase
- Auto-sync via file watcher — index selalu up-to-date

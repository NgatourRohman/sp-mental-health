# SP-Mental-Paskibra

Sistem pakar kesehatan mental Paskibra berbasis **frontend statis + Supabase**. Aplikasi ini dirancang agar bisa berjalan gratis tanpa backend PHP/Render: Vercel menyajikan HTML/JS, sedangkan data dibaca dan ditulis langsung ke Supabase.

## Fitur Utama
- Login sederhana berbasis tabel `users` di Supabase.
- CRUD data gangguan, gejala, user, profil siswa, dan relasi.
- Diagnosa langsung di browser memakai scoring gejala per gangguan.
- Penyimpanan hasil diagnosa dan riwayat ke Supabase.
- Dashboard admin dan insight Chart.js.
- Laporan/cetak berbasis HTML dan `localStorage`.

## Stack
- Frontend: HTML5, Tailwind CDN, JavaScript.
- Database/API: Supabase PostgreSQL + REST API.
- Deployment gratis: Vercel Hobby + Supabase Free.

Folder `backend-php/` dan `ml-python/` dipertahankan sebagai arsip versi hybrid sebelumnya, tetapi alur frontend-only tidak membutuhkannya.

## Setup Supabase
1. Buat project di Supabase.
2. Buka **SQL Editor**.
3. Jalankan seluruh isi `supabase-schema/initial_setup.sql`.
4. Buka **Project Settings** -> **API**.
5. Salin:
   - Project URL
   - anon public key
6. Isi `frontend/js/config.js`:
   ```js
   const CONFIG = {
       SUPABASE_URL: 'https://your-project.supabase.co',
       SUPABASE_ANON_KEY: 'your-supabase-anon-key'
   };
   ```

Skema SQL sudah membuat akun demo:
- Admin: `arthur@example.com` / `arthur123`
- Siswa: `siswa@example.com` / `siswa123`

## Menjalankan Lokal
Buka `frontend/index.html` langsung di browser, atau jalankan static server sederhana dari root:

```bash
python -m http.server 5500 -d frontend
```

Lalu buka:

```text
http://localhost:5500
```

## Deploy Vercel
1. Import repo GitHub ke Vercel.
2. Set:
   - Framework Preset: `Other`
   - Root Directory: `frontend`
   - Build Command: kosong
   - Output Directory: kosong/default
3. Deploy.

Setiap push ke branch `main` akan memicu redeploy otomatis.

## Catatan Keamanan
Mode frontend-only memakai anon key di browser dan RLS policy permisif untuk kebutuhan akademik/demo. Untuk production, gunakan Supabase Auth, policy per-role/per-user, dan jangan menaruh logika sensitif di browser.

## Lisensi
Proyek ini dibuat untuk tujuan akademik dan pengembangan kesehatan mental.

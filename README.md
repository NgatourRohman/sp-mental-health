# SP-Mental-Paskibra

Sistem pakar kesehatan mental Paskibra berbasis **React + Supabase**. Aplikasi ini dirancang agar bisa berjalan gratis tanpa backend PHP/Render: Vercel menjalankan frontend React, sedangkan data dan autentikasi memakai Supabase.

## Fitur Utama
- Login aman dengan Supabase Auth.
- CRUD data gangguan, gejala, user, profil siswa, dan relasi.
- Diagnosa langsung di browser memakai scoring gejala per gangguan.
- Penyimpanan hasil diagnosa dan riwayat ke Supabase.
- Dashboard admin dan insight Chart.js.
- Laporan/cetak berbasis HTML dan `localStorage`.

## Stack
- Frontend: React, Vite, React Router.
- Database/Auth/API: Supabase PostgreSQL, Supabase Auth, REST API.
- Deployment gratis: Vercel Hobby + Supabase Free.

Folder `frontend/`, `backend-php/`, dan `ml-python/` dipertahankan sebagai arsip versi sebelumnya. Aplikasi baru berada di `frontend-react/`.

## Setup Supabase
1. Buat project di Supabase.
2. Buka **SQL Editor**.
3. Jalankan seluruh isi `supabase-schema/initial_setup.sql` jika tabel belum ada.
4. Jalankan `supabase-schema/auth_frontend_setup.sql`.
5. Buat user login di **Authentication -> Users**.
6. Masukkan UUID user tersebut ke tabel `profiles`, dengan role `admin` atau `siswa`.
7. Buka **Project Settings** -> **API**.
8. Salin:
   - Project URL
   - anon public key
9. Isi `frontend-react/.env`:
   ```env
   VITE_SUPABASE_URL=https://your-project.supabase.co
   VITE_SUPABASE_ANON_KEY=your-supabase-anon-public-key
   ```

## Menjalankan Lokal
Jalankan React app:

```bash
cd frontend-react
npm install
npm run dev
```

## Deploy Vercel
1. Import repo GitHub ke Vercel.
2. Set:
   - Framework Preset: `Vite`
   - Root Directory: `frontend-react`
   - Build Command: `npm run build`
   - Output Directory: `dist`
3. Tambahkan environment variables Vercel:
   - `VITE_SUPABASE_URL`
   - `VITE_SUPABASE_ANON_KEY`
4. Deploy.

Setiap push ke branch `main` akan memicu redeploy otomatis.

## Catatan Keamanan
React app memakai anon key di browser, tetapi akses data dibatasi Supabase Auth + RLS. Jangan menaruh service role key di frontend atau Vercel.

## Lisensi
Proyek ini dibuat untuk tujuan akademik dan pengembangan kesehatan mental.

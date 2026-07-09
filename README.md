# 🧠 SP-Mental-Paskibra: Advanced Expert System

Sistem Pakar Kesehatan Mental Paskibra berbasis **Machine Learning (Gaussian Naive Bayes)** dengan arsitektur **Hybrid Cloud** yang dirancang untuk performa tinggi, keamanan, dan skalabilitas pada infrastruktur gratis (*Free Tier*).

## 🚀 Fitur Utama
- **ML Inference Engine**: Menggunakan FastAPI untuk prediksi gangguan mental secara real-time.
- **Hybrid Architecture**: Pemisahan Frontend (Vercel), Backend (PHP/Render), dan Database (Supabase).
- **Smart Caching**: Sistem cache berbasis hash untuk mengurangi latensi dan beban server.
- **Security & Stability**: 
  - Rate Limiting (5 req/menit).
  - Retry Strategy (mitigasi Cold Start).
  - Environment variables protection.
- **Admin Insight**: Dashboard analitik dengan visualisasi Chart.js dan fitur ekspor CSV.

## 🛠️ Stack Teknologi
- **Frontend**: HTML5, Tailwind CSS, JavaScript (ES6+).
- **Backend**: PHP (Logic Optimizer) & Python FastAPI (ML Engine).
- **Database**: Supabase (PostgreSQL).
- **Deployment**: Vercel & Render.

## 📂 Struktur Proyek
- `/frontend`: Interface pengguna dan aset web.
- `/backend-php`: Logika bisnis, API Gateway, dan Suppabase Helper.
- `/ml-python`: Service Machine Learning dan model `.pkl`.
- `/supabase-schema`: Script inisialisasi database PostgreSQL.

## ⚙️ Penyiapan Environment Variable
Pastikan variabel berikut dikonfigurasi pada dashboard **Render**:
- `SUPABASE_URL`: URL REST API Supabase Anda.
- `SUPABASE_KEY`: Service/Anon Key Supabase.
- `PYTHON_API_URL`: URL deployment ML Service (tambahkan `/predict`).
- `API_KEY_PASKIBRA`: Token rahasia untuk otentikasi antar-layanan.

Contoh konfigurasi tersedia di `.env.example`.

## ▶️ Menjalankan Lokal
1. Jalankan skema `supabase-schema/initial_setup.sql` di Supabase SQL Editor.
2. Set environment variable backend PHP sesuai `.env.example`.
3. Jalankan backend PHP dari root project:
   ```bash
   php -S localhost:8000 -t backend-php
   ```
4. Jalankan ML service:
   ```bash
   cd ml-python
   uvicorn main:app --reload --host 127.0.0.1 --port 8001
   ```
5. Atur `PYTHON_API_URL` ke `http://127.0.0.1:8001/predict`.
6. Buka halaman di folder `frontend/`.

## 🔧 Status Integrasi
- Frontend memakai `frontend/js/config.js` sebagai satu sumber base URL API.
- Endpoint PHP utama memakai Supabase REST API melalui `backend-php/core/SupabaseHelper.php`.
- File `backend-php/api/koneksi.php` dipertahankan sebagai stub deprecated dan akan memberi error jika endpoint lama masih memanggil MySQL.

## ☁️ Deploy Render
Deploy dua Web Service terpisah:

1. Backend PHP
   - Root Directory: `backend-php`
   - Runtime: `Docker`
   - Health Check Path: `/api/health.php`
   - Environment Variables:
     - `SUPABASE_URL`
     - `SUPABASE_KEY`
     - `PYTHON_API_URL`
     - `API_KEY_PASKIBRA`

2. ML Python
   - Root Directory: `ml-python`
   - Runtime: `Python 3`
   - Build Command: `pip install -r requirements.txt`
   - Start Command: `uvicorn main:app --host 0.0.0.0 --port $PORT`
   - Environment Variables:
     - `API_KEY_PASKIBRA`

## 📝 Lisensi
Proyek ini dibuat untuk tujuan akademik dan pengembangan kesehatan mental.

---

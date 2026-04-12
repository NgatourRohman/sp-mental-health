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

## 📝 Lisensi
Proyek ini dibuat untuk tujuan akademik dan pengembangan kesehatan mental.

---
*Developed with ❤️ for a better mental health support.*

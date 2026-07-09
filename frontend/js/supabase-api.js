(function () {
    const cfg = window.CONFIG || {};
    const baseUrl = (cfg.SUPABASE_URL || '').replace(/\/$/, '');
    const anonKey = cfg.SUPABASE_ANON_KEY || '';

    function assertConfigured() {
        if (!baseUrl || !anonKey || baseUrl.includes('your-project') || anonKey.includes('your-supabase')) {
            throw new Error('Supabase belum dikonfigurasi di frontend/js/config.js');
        }
    }

    function headers(extra) {
        return Object.assign({
            apikey: anonKey,
            Authorization: 'Bearer ' + anonKey,
            'Content-Type': 'application/json',
            Prefer: 'return=representation'
        }, extra || {});
    }

    async function request(path, options) {
        assertConfigured();
        const res = await fetch(baseUrl + '/rest/v1/' + path, Object.assign({
            headers: headers()
        }, options || {}));

        const text = await res.text();
        const data = text ? JSON.parse(text) : null;
        if (!res.ok) {
            const message = data && (data.message || data.details) ? (data.message || data.details) : 'Supabase request failed';
            throw new Error(message);
        }
        return data;
    }

    function select(table, query) {
        return request(table + (query ? '?' + query : '?select=*'));
    }

    function insert(table, payload) {
        return request(table, { method: 'POST', body: JSON.stringify(payload) });
    }

    function update(table, payload, criteria) {
        return request(table + '?' + criteria, { method: 'PATCH', body: JSON.stringify(payload) });
    }

    function remove(table, criteria) {
        return request(table + '?' + criteria, { method: 'DELETE' });
    }

    function esc(value) {
        return encodeURIComponent(value == null ? '' : String(value));
    }

    async function sha256(value) {
        const bytes = new TextEncoder().encode(value);
        const hash = await crypto.subtle.digest('SHA-256', bytes);
        return Array.from(new Uint8Array(hash)).map(b => b.toString(16).padStart(2, '0')).join('');
    }

    function normalizeRole(role) {
        return String(role || 'siswa').toLowerCase();
    }

    async function login(email, password) {
        const rows = await select('users', 'email=eq.' + esc(email) + '&select=id,nama,email,password,role&limit=1');
        const user = rows && rows[0];
        if (!user) throw new Error('Email atau password salah.');

        const passwordHash = await sha256(password);
        const stored = user.password;
        if (stored !== passwordHash && stored !== password) {
            throw new Error('Email atau password salah.');
        }

        return {
            status: 'success',
            id: user.id,
            nama: user.nama,
            email: user.email,
            role: normalizeRole(user.role)
        };
    }

    async function getGangguan() {
        return select('gangguan', 'select=*&order=kode.asc');
    }

    async function getGejala() {
        const gejala = await select('gejala', 'select=*&order=kode_gangguan.asc,kode_gejala.asc');
        const gangguan = await getGangguan();
        const map = {};
        gangguan.forEach(g => { map[g.kode] = g.nama; });
        return gejala.map(g => Object.assign({}, g, {
            aktif: g.aktif ? 1 : 0,
            nama_gangguan: map[g.kode_gangguan] || null
        }));
    }

    async function addGangguan(payload) {
        await insert('gangguan', payload);
        return { status: 'success', message: 'Data gangguan berhasil ditambahkan' };
    }

    async function updateGangguan(id, payload) {
        await update('gangguan', payload, 'id=eq.' + esc(id));
        return { status: 'success', message: 'Data gangguan berhasil diubah' };
    }

    async function deleteGangguan(id) {
        await remove('gangguan', 'id=eq.' + esc(id));
        return { status: 'success', message: 'Data gangguan berhasil dihapus' };
    }

    async function addGejala(payload) {
        await insert('gejala', Object.assign({ aktif: true }, payload));
        return { status: 'success', message: 'Gejala berhasil ditambahkan.' };
    }

    async function updateGejala(id, payload) {
        await update('gejala', payload, 'id=eq.' + esc(id));
        return { status: 'success', message: 'Gejala berhasil diubah.' };
    }

    async function toggleGejala(id, aktif) {
        await update('gejala', { aktif: !!aktif }, 'id=eq.' + esc(id));
        return { status: 'success', message: 'Status gejala berhasil diubah.' };
    }

    async function deleteGejala(id) {
        await remove('gejala', 'id=eq.' + esc(id));
        return { status: 'success', message: 'Data gejala berhasil dihapus.' };
    }

    async function getUsers() {
        return select('users', 'select=id,nama,email,role,created_at&order=created_at.asc');
    }

    async function addUser(payload) {
        const passwordHash = await sha256(payload.password);
        await insert('users', {
            nama: payload.nama,
            email: payload.email,
            password: passwordHash,
            role: payload.role || 'siswa'
        });
        return { success: true, message: 'User berhasil ditambahkan.' };
    }

    async function updateUser(id, payload) {
        const data = { nama: payload.nama, email: payload.email };
        if (payload.password) data.password = await sha256(payload.password);
        await update('users', data, 'id=eq.' + esc(id));
        return { success: true, message: 'User berhasil diperbarui.' };
    }

    async function deleteUser(id) {
        await remove('users', 'id=eq.' + esc(id));
        return { success: true, message: 'User berhasil dihapus.' };
    }

    async function getProfile(email) {
        const rows = await select('user_profile', 'email=eq.' + esc(email) + '&select=*&limit=1');
        return rows[0] || null;
    }

    async function saveProfile(payload) {
        const existing = await getProfile(payload.email);
        if (existing) {
            await update('user_profile', payload, 'email=eq.' + esc(payload.email));
        } else {
            await insert('user_profile', payload);
        }
        return { status: 'success' };
    }

    async function getLatestDiagnosis(email) {
        const rows = await select('hasil_diagnosa', 'email=eq.' + esc(email) + '&select=*&order=waktu_diagnosa.desc&limit=1');
        const row = rows[0];
        if (!row) return { error: 'Tidak ada hasil diagnosa untuk email tersebut.' };
        return {
            kondisi: row.hasil_diagnosa || '-',
            deskripsi: row.deskripsi || '-',
            saran: String(row.saran || '-').split(/\r\n|\r|\n/),
            rekomendasi: row.rekomendasi || '-'
        };
    }

    async function getDiagnosisHistory(email) {
        const rows = await select('hasil_diagnosa', 'email=eq.' + esc(email) + '&select=*&order=waktu_diagnosa.desc');
        return rows.map(row => ({
            kondisi: row.hasil_diagnosa || '-',
            deskripsi: row.deskripsi || '-',
            saran: row.saran || '-',
            rekomendasi: row.rekomendasi || '-',
            tanggal: row.waktu_diagnosa ? new Date(row.waktu_diagnosa).toLocaleString('id-ID') : '-'
        }));
    }

    function kategoriFromScore(total) {
        if (total < 25) return 'Sehat';
        if (total < 32) return 'Ringan';
        return 'Stres Berat';
    }

    async function processDiagnosis(email, answers) {
        const gejala = await getGejala();
        const gangguan = await getGangguan();
        const gangguanMap = {};
        gangguan.forEach(g => { gangguanMap[g.kode] = g; });

        const gejalaMap = {};
        gejala.forEach(g => { gejalaMap[g.kode_gejala] = g; });

        const byGangguan = {};
        let total = 0;
        answers.forEach(item => {
            const value = Math.max(1, Math.min(5, Number(item.nilai || 1)));
            total += value;
            const kodeGangguan = gejalaMap[item.kode_gejala] && gejalaMap[item.kode_gejala].kode_gangguan;
            if (!kodeGangguan) return;
            if (!byGangguan[kodeGangguan]) byGangguan[kodeGangguan] = { total: 0, count: 0 };
            byGangguan[kodeGangguan].total += value;
            byGangguan[kodeGangguan].count += 1;
        });

        let bestKode = Object.keys(byGangguan)[0] || (gangguan[0] && gangguan[0].kode) || '-';
        Object.keys(byGangguan).forEach(kode => {
            const current = byGangguan[kode].total / Math.max(1, byGangguan[kode].count);
            const best = byGangguan[bestKode].total / Math.max(1, byGangguan[bestKode].count);
            if (current > best) bestKode = kode;
        });

        const bestGangguan = gangguanMap[bestKode] || { kode: bestKode, nama: bestKode };
        const kategori = kategoriFromScore(total);
        const confidence = Math.round(((byGangguan[bestKode]?.total || total) / Math.max(1, total)) * 10000) / 100;

        const detailRows = await select(
            'diagnosa_detail',
            'gangguan=eq.' + esc(bestGangguan.nama) + '&tingkat=eq.' + esc(kategori) + '&select=*&limit=1'
        ).catch(() => []);
        const detail = detailRows[0] || {
            deskripsi: 'Analisis untuk ' + bestGangguan.nama + ' (' + kategori + ') telah selesai.',
            saran: 'Konsultasikan dengan pembina atau ahli profesional bila gejala mengganggu aktivitas.',
            rekomendasi: '-'
        };

        const dataLog = {
            email,
            hasil_diagnosa: 'Gangguan ' + bestGangguan.nama + ' ' + kategori,
            deskripsi: detail.deskripsi,
            saran: detail.saran,
            rekomendasi: detail.rekomendasi || '-',
            confidence
        };

        await insert('hasil_diagnosa', dataLog);
        return {
            status: 'success',
            message: 'Diagnosis Complete',
            data: {
                hasil: dataLog.hasil_diagnosa,
                confidence: confidence + '%',
                deskripsi: dataLog.deskripsi,
                saran: String(dataLog.saran || '').split(/\r\n|\r|\n/),
                rekomendasi: dataLog.rekomendasi
            }
        };
    }

    async function getDashboardStats() {
        const [users, gangguan, gejala] = await Promise.all([getUsers(), getGangguan(), getGejala()]);
        return { users: users.length, gangguan: gangguan.length, gejala: gejala.length };
    }

    async function getAnalytics() {
        const rows = await select('hasil_diagnosa', 'select=hasil_diagnosa,confidence');
        const distribusi = {};
        let totalConfidence = 0;
        let countConfidence = 0;
        rows.forEach(row => {
            const label = row.hasil_diagnosa || '-';
            distribusi[label] = (distribusi[label] || 0) + 1;
            if (Number(row.confidence) > 0) {
                totalConfidence += Number(row.confidence);
                countConfidence += 1;
            }
        });
        return {
            status: 'success',
            data: {
                distribusi,
                avg_confidence: countConfidence ? Math.round((totalConfidence / countConfidence) * 100) / 100 : 0,
                total_diagnosa: rows.length,
                cache_hits_estimate: 0
            }
        };
    }

    async function getRelasiDetail(kode) {
        const gangguan = (await select('gangguan', 'kode=eq.' + esc(kode) + '&select=nama&limit=1'))[0] || {};
        const gejala = await select('gejala', 'kode_gangguan=eq.' + esc(kode) + '&select=nama_gejala&order=kode_gejala.asc');
        return gejala.map(g => ({ nama: gangguan.nama || '', nama_gangguan: gangguan.nama || '', nama_gejala: g.nama_gejala || '' }));
    }

    window.SPDB = {
        login,
        getGangguan,
        addGangguan,
        updateGangguan,
        deleteGangguan,
        getGejala,
        addGejala,
        updateGejala,
        toggleGejala,
        deleteGejala,
        getUsers,
        addUser,
        updateUser,
        deleteUser,
        getProfile,
        saveProfile,
        getLatestDiagnosis,
        getDiagnosisHistory,
        processDiagnosis,
        getDashboardStats,
        getAnalytics,
        getRelasiDetail,
        sha256
    };
})();

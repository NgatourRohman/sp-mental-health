import React, { useEffect, useMemo, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { HashRouter, Navigate, Route, Routes, Link, NavLink, useNavigate, useParams, useSearchParams } from 'react-router-dom';
import { Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import { supabase } from './supabaseClient';
import './styles.css';

const ADMIN_NAV = [
  ['Dashboard', '/admin'],
  ['Insight', '/admin/insight'],
  ['Data User', '/admin/users'],
  ['Gangguan', '/admin/gangguan'],
  ['Gejala', '/admin/gejala'],
  ['Relasi', '/admin/relasi'],
];

const USER_NAV = [
  ['Dashboard', '/user'],
  ['Data Diri', '/user/profile'],
  ['Panduan', '/user/panduan'],
  ['Diagnosa', '/user/diagnosa'],
  ['Hasil', '/user/hasil'],
];

function cn(...parts) {
  return parts.filter(Boolean).join(' ');
}

function useAuth() {
  const [session, setSession] = useState(null);
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let mounted = true;

    async function init() {
      const { data } = await supabase.auth.getSession();
      if (!mounted) return;
      setSession(data.session);
      if (data.session?.user) {
        setProfile(await getProfile(data.session.user));
      }
      setLoading(false);
    }

    init();
    const { data: listener } = supabase.auth.onAuthStateChange(async (_event, nextSession) => {
      setSession(nextSession);
      setProfile(nextSession?.user ? await getProfile(nextSession.user) : null);
      setLoading(false);
    });

    return () => {
      mounted = false;
      listener.subscription.unsubscribe();
    };
  }, []);

  return { session, profile, loading, setProfile };
}

async function getProfile(user) {
  const { data, error } = await supabase
    .from('profiles')
    .select('*')
    .eq('id', user.id)
    .maybeSingle();

  if (error) throw error;
  if (data) return data;

  const fallback = {
    id: user.id,
    email: user.email,
    nama: user.user_metadata?.nama || user.email?.split('@')[0] || 'User',
    role: 'siswa',
  };

  const { data: inserted, error: insertError } = await supabase
    .from('profiles')
    .insert(fallback)
    .select()
    .single();
  if (insertError) throw insertError;
  return inserted;
}

function AppShell() {
  const auth = useAuth();

  if (auth.loading) return <FullPageMessage title="Memuat sesi..." />;

  return (
    <AuthContext.Provider value={auth}>
      <Routes>
        <Route path="/" element={auth.session ? <Navigate to={auth.profile?.role === 'admin' ? '/admin' : '/user'} /> : <Login />} />
        <Route path="/admin/*" element={<Protected role="admin"><AdminLayout /></Protected>} />
        <Route path="/user/*" element={<Protected><UserLayout /></Protected>} />
        <Route path="*" element={<Navigate to="/" />} />
      </Routes>
    </AuthContext.Provider>
  );
}

const AuthContext = React.createContext(null);

function useAuthContext() {
  return React.useContext(AuthContext);
}

function Protected({ children, role }) {
  const { session, profile } = useAuthContext();
  if (!session) return <Navigate to="/" />;
  if (role && profile?.role !== role) return <Navigate to={profile?.role === 'admin' ? '/admin' : '/user'} />;
  return children;
}

function FullPageMessage({ title, detail }) {
  return (
    <main className="center-page">
      <section className="panel compact">
        <h1>{title}</h1>
        {detail && <p>{detail}</p>}
      </section>
    </main>
  );
}

function Login() {
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  async function submit(e) {
    e.preventDefault();
    setLoading(true);
    setError('');

    const { data, error: authError } = await supabase.auth.signInWithPassword({ email, password });
    if (authError) {
      setError(authError.message);
      setLoading(false);
      return;
    }

    const profile = await getProfile(data.user);
    navigate(profile.role === 'admin' ? '/admin' : '/user', { replace: true });
  }

  return (
    <main className="login-page">
      <form className="login-card" onSubmit={submit}>
        <div>
          <h1>SP Mental Paskibra</h1>
          <p>Masuk untuk mengelola data dan melakukan diagnosa.</p>
        </div>
        <label>Email<input value={email} onChange={e => setEmail(e.target.value)} type="email" required /></label>
        <label>Password<input value={password} onChange={e => setPassword(e.target.value)} type="password" required /></label>
        {error && <p className="error">{error}</p>}
        <button className="primary" disabled={loading}>{loading ? 'Memproses...' : 'Login'}</button>
      </form>
    </main>
  );
}

function Layout({ nav, base, children }) {
  const { profile } = useAuthContext();
  const navigate = useNavigate();

  async function logout() {
    await supabase.auth.signOut();
    navigate('/', { replace: true });
  }

  return (
    <div className="app-layout">
      <aside className="sidebar">
        <div className="brand">
          <img src="/logo.png" alt="" />
          <div>
            <strong>{profile?.nama || 'User'}</strong>
            <span>{profile?.role || 'siswa'}</span>
          </div>
        </div>
        <nav>
          {nav.map(([label, to]) => (
            <NavLink key={to} to={to} className={({ isActive }) => cn('nav-link', isActive && 'active')}>{label}</NavLink>
          ))}
        </nav>
        <button className="ghost" onClick={logout}>Logout</button>
      </aside>
      <main className="content">
        <header className="topbar">
          <h1>{base}</h1>
          <span>{profile?.email}</span>
        </header>
        {children}
      </main>
    </div>
  );
}

function AdminLayout() {
  return (
    <Layout nav={ADMIN_NAV} base="Admin">
      <Routes>
        <Route index element={<AdminDashboard />} />
        <Route path="insight" element={<AdminInsight />} />
        <Route path="users" element={<UsersPage />} />
        <Route path="gangguan" element={<GangguanPage />} />
        <Route path="gejala" element={<GejalaPage />} />
        <Route path="relasi" element={<RelasiPage />} />
        <Route path="relasi/:kode" element={<RelasiDetailPage />} />
        <Route path="riwayat" element={<RiwayatPage />} />
      </Routes>
    </Layout>
  );
}

function UserLayout() {
  return (
    <Layout nav={USER_NAV} base="Siswa">
      <Routes>
        <Route index element={<UserDashboard />} />
        <Route path="profile" element={<ProfilePage />} />
        <Route path="panduan" element={<PanduanPage />} />
        <Route path="diagnosa" element={<DiagnosaPage />} />
        <Route path="hasil" element={<HasilPage />} />
      </Routes>
    </Layout>
  );
}

function DataState({ loading, error, empty, children }) {
  if (loading) return <section className="panel">Memuat data...</section>;
  if (error) return <section className="panel error">{error}</section>;
  if (empty) return <section className="panel muted">Belum ada data.</section>;
  return children;
}

function AdminDashboard() {
  const [stats, setStats] = useState({ users: 0, gangguan: 0, gejala: 0 });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    Promise.all([
      supabase.from('profiles').select('id', { count: 'exact', head: true }),
      supabase.from('gangguan').select('id', { count: 'exact', head: true }),
      supabase.from('gejala').select('id', { count: 'exact', head: true }),
    ]).then(([u, g, gj]) => {
      setStats({ users: u.count || 0, gangguan: g.count || 0, gejala: gj.count || 0 });
      setLoading(false);
    });
  }, []);

  return (
    <DataState loading={loading}>
      <section className="stats-grid">
        <StatCard label="Total User" value={stats.users} to="/admin/users" />
        <StatCard label="Total Gangguan" value={stats.gangguan} to="/admin/gangguan" />
        <StatCard label="Total Gejala" value={stats.gejala} to="/admin/gejala" />
      </section>
    </DataState>
  );
}

function StatCard({ label, value, to }) {
  return <Link className="stat-card" to={to}><span>{label}</span><strong>{value}</strong></Link>;
}

function UserDashboard() {
  const { profile } = useAuthContext();
  return (
    <section className="panel">
      <h2>Selamat datang, {profile?.nama || 'Siswa'}</h2>
      <p className="muted">Lengkapi data diri, baca panduan, lalu lakukan diagnosa mental secara mandiri.</p>
      <div className="actions">
        <Link className="primary link-button" to="/user/diagnosa">Mulai Diagnosa</Link>
        <Link className="secondary link-button" to="/user/profile">Lengkapi Data Diri</Link>
      </div>
    </section>
  );
}

function AdminInsight() {
  const [rows, setRows] = useState([]);
  const [avg, setAvg] = useState(0);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    supabase.from('hasil_diagnosa').select('hasil_diagnosa,confidence').then(({ data }) => {
      const map = {};
      let total = 0;
      let count = 0;
      (data || []).forEach(row => {
        const label = row.hasil_diagnosa || '-';
        map[label] = (map[label] || 0) + 1;
        if (Number(row.confidence) > 0) {
          total += Number(row.confidence);
          count += 1;
        }
      });
      setRows(Object.entries(map).map(([name, total]) => ({ name, total })));
      setAvg(count ? Math.round((total / count) * 100) / 100 : 0);
      setLoading(false);
    });
  }, []);

  return (
    <DataState loading={loading}>
      <section className="stats-grid">
        <div className="stat-card"><span>Total Diagnosa</span><strong>{rows.reduce((a, b) => a + b.total, 0)}</strong></div>
        <div className="stat-card"><span>Rata Confidence</span><strong>{avg}%</strong></div>
      </section>
      <section className="panel chart-panel">
        <h2>Distribusi Diagnosa</h2>
        <ResponsiveContainer width="100%" height={320}>
          <BarChart data={rows}>
            <CartesianGrid strokeDasharray="3 3" />
            <XAxis dataKey="name" tick={{ fontSize: 11 }} />
            <YAxis />
            <Tooltip />
            <Bar dataKey="total" fill="#14b8a6" />
          </BarChart>
        </ResponsiveContainer>
      </section>
    </DataState>
  );
}

function useTable(table, query = '*') {
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  const load = async () => {
    setLoading(true);
    setError('');
    const { data: rows, error: err } = await supabase.from(table).select(query);
    if (err) setError(err.message);
    setData(rows || []);
    setLoading(false);
  };

  useEffect(() => { load(); }, [table, query]);
  return { data, setData, loading, error, reload: load };
}

function GangguanPage() {
  const { data, loading, error, reload } = useTable('gangguan');
  const [editing, setEditing] = useState(null);

  async function save(e) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    const payload = {
      kode: form.get('kode'),
      nama: form.get('nama'),
      deskripsi: form.get('deskripsi'),
    };
    const result = editing?.id
      ? await supabase.from('gangguan').update(payload).eq('id', editing.id)
      : await supabase.from('gangguan').insert(payload);
    if (result.error) return alert(result.error.message);
    setEditing(null);
    e.currentTarget.reset();
    reload();
  }

  async function del(id) {
    if (!confirm('Hapus data gangguan ini?')) return;
    const { error: err } = await supabase.from('gangguan').delete().eq('id', id);
    if (err) alert(err.message);
    reload();
  }

  return (
    <section className="grid-two">
      <form className="panel form-panel" onSubmit={save}>
        <h2>{editing ? 'Ubah Gangguan' : 'Tambah Gangguan'}</h2>
        <input name="kode" placeholder="Kode" defaultValue={editing?.kode || ''} required />
        <input name="nama" placeholder="Nama gangguan" defaultValue={editing?.nama || ''} required />
        <textarea name="deskripsi" placeholder="Deskripsi" defaultValue={editing?.deskripsi || ''} />
        <button className="primary">Simpan</button>
        {editing && <button type="button" className="ghost" onClick={() => setEditing(null)}>Batal</button>}
      </form>
      <DataState loading={loading} error={error} empty={!data.length}>
        <Table headers={['Kode', 'Nama', 'Deskripsi', 'Aksi']}>
          {data.map(row => (
            <tr key={row.id}>
              <td>{row.kode}</td>
              <td>{row.nama}</td>
              <td>{row.deskripsi || '-'}</td>
              <td><button onClick={() => setEditing(row)}>Ubah</button><button className="danger" onClick={() => del(row.id)}>Hapus</button></td>
            </tr>
          ))}
        </Table>
      </DataState>
    </section>
  );
}

function GejalaPage() {
  const { data: gejala, loading, error, reload } = useTable('gejala');
  const { data: gangguan } = useTable('gangguan');
  const [editing, setEditing] = useState(null);
  const gangguanMap = useMemo(() => Object.fromEntries(gangguan.map(g => [g.kode, g.nama])), [gangguan]);

  async function save(e) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    const payload = {
      kode_gejala: form.get('kode_gejala'),
      nama_gejala: form.get('nama_gejala'),
      kode_gangguan: form.get('kode_gangguan'),
      aktif: true,
    };
    const result = editing?.id
      ? await supabase.from('gejala').update(payload).eq('id', editing.id)
      : await supabase.from('gejala').insert(payload);
    if (result.error) return alert(result.error.message);
    setEditing(null);
    e.currentTarget.reset();
    reload();
  }

  async function toggle(row) {
    const { error: err } = await supabase.from('gejala').update({ aktif: !row.aktif }).eq('id', row.id);
    if (err) alert(err.message);
    reload();
  }

  async function del(id) {
    if (!confirm('Hapus gejala ini?')) return;
    const { error: err } = await supabase.from('gejala').delete().eq('id', id);
    if (err) alert(err.message);
    reload();
  }

  return (
    <section className="grid-two">
      <form className="panel form-panel" onSubmit={save}>
        <h2>{editing ? 'Ubah Gejala' : 'Tambah Gejala'}</h2>
        <input name="kode_gejala" placeholder="Kode gejala" defaultValue={editing?.kode_gejala || ''} required />
        <input name="nama_gejala" placeholder="Nama gejala" defaultValue={editing?.nama_gejala || ''} required />
        <select name="kode_gangguan" defaultValue={editing?.kode_gangguan || ''} required>
          <option value="">Pilih gangguan</option>
          {gangguan.map(g => <option key={g.id} value={g.kode}>{g.kode} - {g.nama}</option>)}
        </select>
        <button className="primary">Simpan</button>
        {editing && <button type="button" className="ghost" onClick={() => setEditing(null)}>Batal</button>}
      </form>
      <DataState loading={loading} error={error} empty={!gejala.length}>
        <Table headers={['Kode', 'Gejala', 'Gangguan', 'Status', 'Aksi']}>
          {gejala.map(row => (
            <tr key={row.id}>
              <td>{row.kode_gejala}</td>
              <td>{row.nama_gejala}</td>
              <td>{gangguanMap[row.kode_gangguan] || row.kode_gangguan}</td>
              <td>{row.aktif ? 'Aktif' : 'Nonaktif'}</td>
              <td><button onClick={() => toggle(row)}>{row.aktif ? 'Nonaktifkan' : 'Aktifkan'}</button><button onClick={() => setEditing(row)}>Ubah</button><button className="danger" onClick={() => del(row.id)}>Hapus</button></td>
            </tr>
          ))}
        </Table>
      </DataState>
    </section>
  );
}

function UsersPage() {
  const { data, loading, error, reload } = useTable('profiles');
  return (
    <section className="panel">
      <div className="section-head">
        <div>
          <h2>Data User</h2>
          <p className="muted">Akun login dibuat melalui Supabase Authentication. Halaman ini mengelola profil dan role.</p>
        </div>
        <button onClick={reload}>Muat Ulang</button>
      </div>
      <DataState loading={loading} error={error} empty={!data.length}>
        <Table headers={['Nama', 'Email', 'Role', 'Aksi']}>
          {data.map(row => <ProfileRow key={row.id} row={row} reload={reload} />)}
        </Table>
      </DataState>
    </section>
  );
}

function ProfileRow({ row, reload }) {
  const [role, setRole] = useState(row.role || 'siswa');
  const [nama, setNama] = useState(row.nama || '');

  async function save() {
    const { error } = await supabase.from('profiles').update({ role, nama }).eq('id', row.id);
    if (error) alert(error.message);
    reload();
  }

  return (
    <tr>
      <td><input value={nama} onChange={e => setNama(e.target.value)} /></td>
      <td>{row.email}</td>
      <td><select value={role} onChange={e => setRole(e.target.value)}><option value="siswa">Siswa</option><option value="admin">Admin</option></select></td>
      <td><button onClick={save}>Simpan</button><Link to={`/admin/riwayat?email=${encodeURIComponent(row.email)}`}>Riwayat</Link></td>
    </tr>
  );
}

function RelasiPage() {
  const { data, loading, error } = useTable('gangguan');
  return (
    <DataState loading={loading} error={error} empty={!data.length}>
      <section className="card-grid">
        {data.map(g => <Link className="color-card" key={g.id} to={`/admin/relasi/${g.kode}`}>{g.nama}</Link>)}
      </section>
    </DataState>
  );
}

function RelasiDetailPage() {
  const { kode } = useParams();
  const { data, loading, error } = useTable('gejala', '*');
  const rows = data.filter(g => g.kode_gangguan === kode);
  return (
    <DataState loading={loading} error={error} empty={!rows.length}>
      <section className="panel">
        <h2>Gejala untuk {kode}</h2>
        <div className="card-grid compact-grid">
          {rows.map(row => <div className="mini-card" key={row.id}>{row.nama_gejala}</div>)}
        </div>
      </section>
    </DataState>
  );
}

function ProfilePage() {
  const { profile } = useAuthContext();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    supabase.from('user_profile').select('*').eq('email', profile.email).maybeSingle().then(({ data }) => {
      setData(data || { email: profile.email, nama: profile.nama || '', tgl_lahir: '', jenis_kelamin: '', alamat: '', no_telp: '' });
      setLoading(false);
    });
  }, [profile.email]);

  async function save(e) {
    e.preventDefault();
    const form = new FormData(e.currentTarget);
    const payload = Object.fromEntries(form.entries());
    const { error } = data?.id
      ? await supabase.from('user_profile').update(payload).eq('id', data.id)
      : await supabase.from('user_profile').insert(payload);
    if (error) return alert(error.message);
    alert('Data diri tersimpan.');
  }

  if (loading) return <section className="panel">Memuat profil...</section>;

  return (
    <form className="panel form-panel wide-form" onSubmit={save}>
      <h2>Data Diri</h2>
      <input name="email" defaultValue={data.email} readOnly />
      <input name="nama" defaultValue={data.nama} placeholder="Nama" required />
      <input name="tgl_lahir" defaultValue={data.tgl_lahir || ''} type="date" required />
      <select name="jenis_kelamin" defaultValue={data.jenis_kelamin || ''} required>
        <option value="">Pilih jenis kelamin</option>
        <option value="Laki-laki">Laki-laki</option>
        <option value="Perempuan">Perempuan</option>
      </select>
      <textarea name="alamat" defaultValue={data.alamat || ''} placeholder="Alamat" required />
      <input name="no_telp" defaultValue={data.no_telp || ''} placeholder="No. telepon" required />
      <button className="primary">Simpan</button>
    </form>
  );
}

function PanduanPage() {
  return (
    <section className="panel">
      <h2>Panduan Diagnosa</h2>
      <p>Jawab setiap pertanyaan dengan jujur sesuai kondisi yang kamu rasakan. Hasil ini bersifat pendukung awal, bukan diagnosis medis final.</p>
      <ul className="guide-list">
        <li>1 - Sangat Jarang</li>
        <li>2 - Jarang</li>
        <li>3 - Kadang-kadang</li>
        <li>4 - Sering</li>
        <li>5 - Sangat Sering</li>
      </ul>
    </section>
  );
}

function DiagnosaPage() {
  const { profile } = useAuthContext();
  const { data: gejala, loading, error } = useTable('gejala');
  const { data: gangguan } = useTable('gangguan');
  const [answers, setAnswers] = useState({});
  const [submitting, setSubmitting] = useState(false);
  const navigate = useNavigate();

  const activeGejala = gejala.filter(g => g.aktif !== false);
  const gangguanMap = Object.fromEntries(gangguan.map(g => [g.kode, g.nama]));

  async function submit(e) {
    e.preventDefault();
    if (activeGejala.some(g => !answers[g.kode_gejala])) return alert('Semua pertanyaan wajib dijawab.');
    setSubmitting(true);

    const grouped = {};
    let totalScore = 0;
    activeGejala.forEach(g => {
      const value = Number(answers[g.kode_gejala]);
      totalScore += value;
      if (!grouped[g.kode_gangguan]) grouped[g.kode_gangguan] = { total: 0, count: 0 };
      grouped[g.kode_gangguan].total += value;
      grouped[g.kode_gangguan].count += 1;
    });

    let bestKode = Object.keys(grouped)[0];
    Object.keys(grouped).forEach(kode => {
      if ((grouped[kode].total / grouped[kode].count) > (grouped[bestKode].total / grouped[bestKode].count)) bestKode = kode;
    });

    const kategori = totalScore < 25 ? 'Sehat' : totalScore < 32 ? 'Ringan' : 'Stres Berat';
    const label = gangguanMap[bestKode] || bestKode || 'Tidak Diketahui';
    const { data: detailRows } = await supabase
      .from('diagnosa_detail')
      .select('*')
      .eq('gangguan', label)
      .eq('tingkat', kategori)
      .limit(1);
    const detail = detailRows?.[0] || {
      deskripsi: `Analisis untuk ${label} (${kategori}) telah selesai.`,
      saran: 'Konsultasikan dengan pembina atau ahli profesional bila kondisi mengganggu aktivitas.',
      rekomendasi: '-',
    };
    const confidence = Math.round(((grouped[bestKode]?.total || totalScore) / Math.max(1, totalScore)) * 10000) / 100;

    const payload = {
      email: profile.email,
      hasil_diagnosa: `Gangguan ${label} ${kategori}`,
      deskripsi: detail.deskripsi,
      saran: detail.saran,
      rekomendasi: detail.rekomendasi || '-',
      confidence,
    };
    const { error: insertError } = await supabase.from('hasil_diagnosa').insert(payload);
    setSubmitting(false);
    if (insertError) return alert(insertError.message);
    navigate('/user/hasil');
  }

  return (
    <DataState loading={loading} error={error} empty={!activeGejala.length}>
      <form className="panel question-list" onSubmit={submit}>
        <h2>Diagnosa</h2>
        {activeGejala.map(g => (
          <label key={g.id} className="question">
            <span>{g.nama_gejala}</span>
            <select value={answers[g.kode_gejala] || ''} onChange={e => setAnswers({ ...answers, [g.kode_gejala]: e.target.value })} required>
              <option value="">Pilih jawaban</option>
              <option value="1">1 - Sangat Jarang</option>
              <option value="2">2 - Jarang</option>
              <option value="3">3 - Kadang-kadang</option>
              <option value="4">4 - Sering</option>
              <option value="5">5 - Sangat Sering</option>
            </select>
          </label>
        ))}
        <button className="primary" disabled={submitting}>{submitting ? 'Memproses...' : 'Selesai'}</button>
      </form>
    </DataState>
  );
}

function HasilPage() {
  const { profile } = useAuthContext();
  const [result, setResult] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    supabase.from('hasil_diagnosa').select('*').eq('email', profile.email).order('waktu_diagnosa', { ascending: false }).limit(1).maybeSingle()
      .then(({ data }) => {
        setResult(data);
        setLoading(false);
      });
  }, [profile.email]);

  if (loading) return <section className="panel">Memuat hasil...</section>;
  if (!result) return <section className="panel muted">Belum ada hasil diagnosa.</section>;

  return (
    <section className="panel result-panel">
      <h2>{result.hasil_diagnosa}</h2>
      <p><strong>Confidence:</strong> {result.confidence || 0}%</p>
      <p>{result.deskripsi}</p>
      <h3>Saran</h3>
      <ul>{String(result.saran || '-').split(/\r\n|\r|\n/).map((s, i) => <li key={i}>{s}</li>)}</ul>
      {result.rekomendasi && result.rekomendasi !== '-' && <p><strong>Rekomendasi:</strong> {result.rekomendasi}</p>}
      <button onClick={() => window.print()}>Cetak Hasil</button>
    </section>
  );
}

function RiwayatPage() {
  const [params] = useSearchParams();
  const email = params.get('email');
  const { data, loading, error } = useTable('hasil_diagnosa');
  const rows = data.filter(row => row.email === email);

  return (
    <DataState loading={loading} error={error} empty={!rows.length}>
      <section className="panel">
        <h2>Riwayat {email}</h2>
        <Table headers={['Tanggal', 'Kondisi', 'Saran']}>
          {rows.map(row => <tr key={row.id}><td>{new Date(row.waktu_diagnosa).toLocaleString('id-ID')}</td><td>{row.hasil_diagnosa}</td><td>{row.saran}</td></tr>)}
        </Table>
      </section>
    </DataState>
  );
}

function Table({ headers, children }) {
  return (
    <div className="table-wrap">
      <table>
        <thead><tr>{headers.map(h => <th key={h}>{h}</th>)}</tr></thead>
        <tbody>{children}</tbody>
      </table>
    </div>
  );
}

createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <HashRouter>
      <AppShell />
    </HashRouter>
  </React.StrictMode>
);

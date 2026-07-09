-- MIGRASI AMAN UNTUK REACT + SUPABASE AUTH
-- Jalankan setelah schema awal sudah ada.
-- Akun login dibuat di Supabase Dashboard -> Authentication -> Users.

CREATE TABLE IF NOT EXISTS profiles (
    id UUID PRIMARY KEY REFERENCES auth.users(id) ON DELETE CASCADE,
    email VARCHAR(100) UNIQUE NOT NULL,
    nama VARCHAR(100),
    role VARCHAR(20) DEFAULT 'siswa' CHECK (role IN ('admin', 'siswa')),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Lepas constraint lama supaya profil siswa Auth tidak wajib ada di tabel users lama.
ALTER TABLE user_profile DROP CONSTRAINT IF EXISTS user_profile_email_fkey;

CREATE OR REPLACE FUNCTION public.is_admin()
RETURNS BOOLEAN
LANGUAGE SQL
SECURITY DEFINER
SET search_path = public
AS $$
    SELECT EXISTS (
        SELECT 1
        FROM profiles
        WHERE id = auth.uid()
          AND role = 'admin'
    );
$$;

ALTER TABLE profiles ENABLE ROW LEVEL SECURITY;
ALTER TABLE gangguan ENABLE ROW LEVEL SECURITY;
ALTER TABLE gejala ENABLE ROW LEVEL SECURITY;
ALTER TABLE diagnosa_detail ENABLE ROW LEVEL SECURITY;
ALTER TABLE hasil_diagnosa ENABLE ROW LEVEL SECURITY;
ALTER TABLE user_profile ENABLE ROW LEVEL SECURITY;
ALTER TABLE relasi ENABLE ROW LEVEL SECURITY;

-- Cabut akses dan policy permisif frontend-only lama.
REVOKE ALL ON ALL TABLES IN SCHEMA public FROM anon;
REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM anon;
DROP POLICY IF EXISTS "anon_all_gangguan" ON gangguan;
DROP POLICY IF EXISTS "anon_all_gejala" ON gejala;
DROP POLICY IF EXISTS "anon_all_diagnosa_detail" ON diagnosa_detail;
DROP POLICY IF EXISTS "anon_all_hasil_diagnosa" ON hasil_diagnosa;
DROP POLICY IF EXISTS "anon_all_diagnosa_cache" ON diagnosa_cache;
DROP POLICY IF EXISTS "anon_all_rate_limits" ON rate_limits;
DROP POLICY IF EXISTS "anon_all_users" ON users;
DROP POLICY IF EXISTS "anon_all_user_profile" ON user_profile;
DROP POLICY IF EXISTS "anon_all_relasi" ON relasi;

GRANT USAGE ON SCHEMA public TO authenticated;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO authenticated;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO authenticated;
GRANT EXECUTE ON FUNCTION public.is_admin() TO authenticated;

DROP POLICY IF EXISTS "profiles_self_read" ON profiles;
DROP POLICY IF EXISTS "profiles_admin_all" ON profiles;
DROP POLICY IF EXISTS "profiles_self_insert" ON profiles;
DROP POLICY IF EXISTS "profiles_self_update" ON profiles;
DROP POLICY IF EXISTS "profiles_admin_update" ON profiles;

CREATE POLICY "profiles_self_read" ON profiles
FOR SELECT
USING (id = auth.uid() OR public.is_admin());

CREATE POLICY "profiles_self_insert" ON profiles
FOR INSERT
WITH CHECK (id = auth.uid() AND role = 'siswa');

CREATE POLICY "profiles_self_update" ON profiles
FOR UPDATE
USING (id = auth.uid())
WITH CHECK (id = auth.uid() AND role = 'siswa');

CREATE POLICY "profiles_admin_update" ON profiles
FOR UPDATE
USING (public.is_admin())
WITH CHECK (public.is_admin());

DROP POLICY IF EXISTS "master_read_authenticated_gangguan" ON gangguan;
DROP POLICY IF EXISTS "master_write_admin_gangguan" ON gangguan;
DROP POLICY IF EXISTS "master_read_authenticated_gejala" ON gejala;
DROP POLICY IF EXISTS "master_write_admin_gejala" ON gejala;
DROP POLICY IF EXISTS "master_read_authenticated_diagnosa_detail" ON diagnosa_detail;
DROP POLICY IF EXISTS "master_write_admin_diagnosa_detail" ON diagnosa_detail;
DROP POLICY IF EXISTS "master_read_authenticated_relasi" ON relasi;
DROP POLICY IF EXISTS "master_write_admin_relasi" ON relasi;

CREATE POLICY "master_read_authenticated_gangguan" ON gangguan FOR SELECT TO authenticated USING (true);
CREATE POLICY "master_write_admin_gangguan" ON gangguan FOR ALL TO authenticated USING (public.is_admin()) WITH CHECK (public.is_admin());

CREATE POLICY "master_read_authenticated_gejala" ON gejala FOR SELECT TO authenticated USING (true);
CREATE POLICY "master_write_admin_gejala" ON gejala FOR ALL TO authenticated USING (public.is_admin()) WITH CHECK (public.is_admin());

CREATE POLICY "master_read_authenticated_diagnosa_detail" ON diagnosa_detail FOR SELECT TO authenticated USING (true);
CREATE POLICY "master_write_admin_diagnosa_detail" ON diagnosa_detail FOR ALL TO authenticated USING (public.is_admin()) WITH CHECK (public.is_admin());

CREATE POLICY "master_read_authenticated_relasi" ON relasi FOR SELECT TO authenticated USING (true);
CREATE POLICY "master_write_admin_relasi" ON relasi FOR ALL TO authenticated USING (public.is_admin()) WITH CHECK (public.is_admin());

DROP POLICY IF EXISTS "hasil_self_read" ON hasil_diagnosa;
DROP POLICY IF EXISTS "hasil_self_insert" ON hasil_diagnosa;
DROP POLICY IF EXISTS "hasil_admin_all" ON hasil_diagnosa;

CREATE POLICY "hasil_self_read" ON hasil_diagnosa
FOR SELECT TO authenticated
USING (email = auth.email() OR public.is_admin());

CREATE POLICY "hasil_self_insert" ON hasil_diagnosa
FOR INSERT TO authenticated
WITH CHECK (email = auth.email());

CREATE POLICY "hasil_admin_all" ON hasil_diagnosa
FOR UPDATE TO authenticated
USING (public.is_admin())
WITH CHECK (public.is_admin());

DROP POLICY IF EXISTS "profile_self_read" ON user_profile;
DROP POLICY IF EXISTS "profile_self_insert" ON user_profile;
DROP POLICY IF EXISTS "profile_self_update" ON user_profile;
DROP POLICY IF EXISTS "profile_admin_all" ON user_profile;

CREATE POLICY "profile_self_read" ON user_profile
FOR SELECT TO authenticated
USING (email = auth.email() OR public.is_admin());

CREATE POLICY "profile_self_insert" ON user_profile
FOR INSERT TO authenticated
WITH CHECK (email = auth.email());

CREATE POLICY "profile_self_update" ON user_profile
FOR UPDATE TO authenticated
USING (email = auth.email() OR public.is_admin())
WITH CHECK (email = auth.email() OR public.is_admin());

-- Setelah membuat user di Authentication, jalankan contoh ini dengan UUID user asli:
-- INSERT INTO profiles (id, email, nama, role)
-- VALUES ('uuid-user-admin', 'arthur@example.com', 'Arthur', 'admin')
-- ON CONFLICT (id) DO UPDATE SET role = EXCLUDED.role, nama = EXCLUDED.nama;

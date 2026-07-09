<?php
require_once 'bootstrap.php';

require_post();
$supabase = get_supabase();

$kode_gejala = post_value('kode_gejala');
$nama_gejala = post_value('nama_gejala');
$kode_gangguan = post_value('kode_gangguan');

if (empty($kode_gejala) || empty($nama_gejala) || empty($kode_gangguan)) {
    json_response(["status" => "error", "message" => "Semua field harus diisi."], 400);
}

$result = $supabase->insert("gejala", [
    "kode_gejala" => $kode_gejala,
    "nama_gejala" => $nama_gejala,
    "kode_gangguan" => $kode_gangguan,
    "aktif" => true
]);

if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result, "Gagal menambahkan gejala.")], 500);
}

echo json_encode(["status" => "success", "message" => "Gejala berhasil ditambahkan."]);

<?php
require_once 'bootstrap.php';

require_post();
$supabase = get_supabase();

$kode = post_value('kode');
$nama = post_value('nama');
$deskripsi = post_value('deskripsi');

if (!$kode || !$nama) {
    json_response(["status" => "error", "message" => "Data tidak lengkap"], 400);
}

$result = $supabase->insert("gangguan", [
    "kode" => $kode,
    "nama" => $nama,
    "deskripsi" => $deskripsi
]);

if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result, "Gagal menambahkan gangguan.")], 500);
}

echo json_encode(["status" => "success", "message" => "Data gangguan berhasil ditambahkan"]);

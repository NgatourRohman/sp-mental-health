<?php
require_once 'bootstrap.php';

require_post();
$supabase = get_supabase();

$id = post_value('id');
$kode = post_value('kode');
$nama = post_value('nama');
$deskripsi = post_value('deskripsi');

if (!$id || !$kode || !$nama) {
    json_response(["status" => "error", "message" => "Data tidak lengkap"], 400);
}

$result = $supabase->update("gangguan", [
    "kode" => $kode,
    "nama" => $nama,
    "deskripsi" => $deskripsi
], "id=eq." . urlencode($id));

if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result, "Gagal mengubah gangguan.")], 500);
}

echo json_encode(["status" => "success", "message" => "Data gangguan berhasil diubah"]);

<?php
require_once 'bootstrap.php';

require_post();
$supabase = get_supabase();

if (
    !isset($_POST['id']) ||
    !isset($_POST['kode_gejala']) ||
    !isset($_POST['nama_gejala']) ||
    !isset($_POST['kode_gangguan'])
) {
    json_response(["status" => "error", "message" => "Data tidak lengkap."], 400);
}

$id = post_value('id');
$kode_gejala = post_value('kode_gejala');
$nama_gejala = post_value('nama_gejala');
$kode_gangguan = post_value('kode_gangguan');

$result = $supabase->update("gejala", [
    "kode_gejala" => $kode_gejala,
    "nama_gejala" => $nama_gejala,
    "kode_gangguan" => $kode_gangguan
], "id=eq." . urlencode($id));

if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result, "Gagal mengubah gejala.")], 500);
}

echo json_encode(["status" => "success", "message" => "Gejala berhasil diubah."]);

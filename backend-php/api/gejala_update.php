<?php
header("Content-Type: application/json");
include 'koneksi.php';

// Validasi input
if (
    !isset($_POST['id']) ||
    !isset($_POST['kode_gejala']) ||
    !isset($_POST['nama_gejala']) ||
    !isset($_POST['kode_gangguan'])
) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap."]);
    exit;
}

$id = $_POST['id'];
$kode_gejala = $_POST['kode_gejala'];
$nama_gejala = $_POST['nama_gejala'];
$kode_gangguan = $_POST['kode_gangguan'];

$stmt = $conn->prepare("UPDATE gejala SET kode_gejala = ?, nama_gejala = ?, kode_gangguan = ? WHERE id = ?");
$stmt->bind_param("sssi", $kode_gejala, $nama_gejala, $kode_gangguan, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Gejala berhasil diubah."]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal mengubah gejala."]);
}

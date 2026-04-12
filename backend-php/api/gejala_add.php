<?php
header("Content-Type: application/json");
include 'koneksi.php';

$kode_gejala = $_POST['kode_gejala'] ?? '';
$nama_gejala = $_POST['nama_gejala'] ?? '';
$kode_gangguan = $_POST['kode_gangguan'] ?? '';

if (empty($kode_gejala) || empty($nama_gejala) || empty($kode_gangguan)) {
    echo json_encode(["status" => "error", "message" => "Semua field harus diisi."]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO gejala (kode_gejala, nama_gejala, kode_gangguan) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $kode_gejala, $nama_gejala, $kode_gangguan);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Gejala berhasil ditambahkan."]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal menambahkan gejala."]);
}

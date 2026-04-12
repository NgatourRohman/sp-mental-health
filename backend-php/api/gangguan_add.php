<?php
include 'koneksi.php';

$kode = $_POST['kode'] ?? '';
$nama = $_POST['nama'] ?? '';
$deskripsi = $_POST['deskripsi'] ?? '';

if (!$kode || !$nama) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO gangguan (kode, nama, deskripsi) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $kode, $nama, $deskripsi);
    $stmt->execute();

    echo json_encode(["status" => "success", "message" => "Data gangguan berhasil ditambahkan"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

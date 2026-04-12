<?php
include 'koneksi.php';

$id = $_POST['id'] ?? '';
$kode = $_POST['kode'] ?? '';
$nama = $_POST['nama'] ?? '';
$deskripsi = $_POST['deskripsi'] ?? '';

if (!$id || !$kode || !$nama) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE gangguan SET kode = ?, nama = ?, deskripsi = ? WHERE id = ?");
    $stmt->bind_param("sssi", $kode, $nama, $deskripsi, $id);
    $stmt->execute();

    echo json_encode(["status" => "success", "message" => "Data gangguan berhasil diubah"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

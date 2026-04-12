<?php
header("Content-Type: application/json");
include 'koneksi.php';

$id = $_POST['id'] ?? null;
$aktif = $_POST['aktif'] ?? null;

if (!$id || !isset($aktif)) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap."]);
    exit;
}

$stmt = $conn->prepare("UPDATE gejala SET aktif = ? WHERE id = ?");
$stmt->bind_param("ii", $aktif, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Status gejala berhasil diubah."]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal mengubah status gejala."]);
}

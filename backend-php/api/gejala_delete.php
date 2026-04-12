<?php
header("Content-Type: application/json");
include 'koneksi.php';

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(["status" => "error", "message" => "ID gejala tidak ditemukan."]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM gejala WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Data gejala berhasil dihapus."]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal menghapus data gejala."]);
}

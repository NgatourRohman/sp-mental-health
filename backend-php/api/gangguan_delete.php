<?php
include 'koneksi.php';

$id = $_POST['id'] ?? '';

if (!$id) {
    echo json_encode(["status" => "error", "message" => "ID tidak ditemukan"]);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM gangguan WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    echo json_encode(["status" => "success", "message" => "Data gangguan berhasil dihapus"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

<?php
include 'koneksi.php';

$id = $_GET['id'] ?? '';
if (!$id) {
    echo json_encode(["success" => false, "message" => "ID tidak valid."]);
    exit;
}

$query = $conn->prepare("DELETE FROM users WHERE id=?");
$query->bind_param("i", $id);

if ($query->execute()) {
    echo json_encode(["success" => true, "message" => "User berhasil dihapus."]);
} else {
    echo json_encode(["success" => false, "message" => "Gagal menghapus user."]);
}

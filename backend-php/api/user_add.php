<?php
include 'koneksi.php';

$nama = $_POST['nama'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$nama || !$email || !$password) {
    echo json_encode(["success" => false, "message" => "Lengkapi semua kolom."]);
    exit;
}

$hashed = password_hash($password, PASSWORD_BCRYPT);
$query = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'Siswa')");
$query->bind_param("sss", $nama, $email, $hashed);

if ($query->execute()) {
    echo json_encode(["success" => true, "message" => "User berhasil ditambahkan."]);
} else {
    echo json_encode(["success" => false, "message" => "Gagal menambahkan user."]);
}

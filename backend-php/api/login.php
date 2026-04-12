<?php
header("Content-Type: application/json");
include 'koneksi.php';

// Ambil email dan password dari POST
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validasi input
if (!$email || !$password) {
    echo json_encode([
        "status" => "debug",
        "message" => "Email dan password tidak diterima",
        "email_received" => $email,
        "password_received" => $password
    ]);
    exit;
}

// Ambil data user dari database
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode([
        "status" => "debug",
        "message" => "Email tidak ditemukan di database",
        "query_email" => $email
    ]);
    exit;
}

// Verifikasi password
$isPasswordValid = password_verify($password, $user['password']);

if (!$isPasswordValid) {
    echo json_encode([
        "status" => "debug",
        "message" => "Password tidak cocok",
        "input_password" => $password,
        "stored_hash" => $user['password']
    ]);
    exit;
}

// Berhasil login
echo json_encode([
    "status" => "success",
    "nama" => $user['nama'],
    "email" => $user['email'],
    "role" => strtolower($user['role']) // lowercase untuk konsistensi
]);

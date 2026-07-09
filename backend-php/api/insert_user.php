<?php
require_once 'bootstrap.php';

$supabase = get_supabase();

$nama = "Siswa";
$email = "siswa@example.com";
$password = password_hash("siswa123", PASSWORD_DEFAULT);
$role = "siswa";

$existing = $supabase->fetch("users", "email=eq." . urlencode($email) . "&limit=1");
if ($existing['status'] === 'success' && !empty($existing['data'])) {
    json_response(["status" => "success", "message" => "Siswa sudah ada."]);
}

$result = $supabase->insert("users", [
    "nama" => $nama,
    "email" => $email,
    "password" => $password,
    "role" => $role
]);

if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result)], 500);
}

echo json_encode(["status" => "success", "message" => "Siswa berhasil ditambahkan."]);

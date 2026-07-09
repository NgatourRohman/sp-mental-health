<?php
require_once 'bootstrap.php';

$supabase = get_supabase();

$nama = "Arthur";
$email = "arthur@example.com";
$password = password_hash("arthur123", PASSWORD_DEFAULT);
$role = "admin";

$existing = $supabase->fetch("users", "email=eq." . urlencode($email) . "&limit=1");
if ($existing['status'] === 'success' && !empty($existing['data'])) {
    json_response(["status" => "success", "message" => "Admin sudah ada."]);
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

echo json_encode(["status" => "success", "message" => "Admin berhasil ditambahkan."]);

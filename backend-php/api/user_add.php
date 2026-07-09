<?php
require_once 'bootstrap.php';

require_post();
$supabase = get_supabase();

$nama = post_value('nama');
$email = filter_var(post_value('email'), FILTER_SANITIZE_EMAIL);
$password = post_value('password');

if (!$nama || !$email || !$password) {
    json_response(["success" => false, "message" => "Lengkapi semua kolom."], 400);
}

$hashed = password_hash($password, PASSWORD_BCRYPT);
$result = $supabase->insert("users", [
    "nama" => $nama,
    "email" => $email,
    "password" => $hashed,
    "role" => "siswa"
]);

if ($result['status'] !== 'success') {
    json_response(["success" => false, "message" => supabase_error($result, "Gagal menambahkan user.")], 500);
}

echo json_encode(["success" => true, "message" => "User berhasil ditambahkan."]);

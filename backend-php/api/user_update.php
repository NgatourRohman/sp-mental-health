<?php
require_once 'bootstrap.php';

require_post();
$supabase = get_supabase();

$id = post_value('id');
$nama = post_value('nama');
$email = filter_var(post_value('email'), FILTER_SANITIZE_EMAIL);
$password = post_value('password');

if (!$id || !$nama || !$email) {
    json_response(["success" => false, "message" => "ID, nama, dan email wajib diisi."], 400);
}

$payload = ["nama" => $nama, "email" => $email];
if ($password) {
    $payload["password"] = password_hash($password, PASSWORD_BCRYPT);
}

$result = $supabase->update("users", $payload, "id=eq." . urlencode($id));

if ($result['status'] !== 'success') {
    json_response(["success" => false, "message" => supabase_error($result, "Gagal memperbarui user.")], 500);
}

echo json_encode(["success" => true, "message" => "User berhasil diperbarui."]);

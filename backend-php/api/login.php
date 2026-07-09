<?php
require_once 'bootstrap.php';

require_post();

$supabase = get_supabase();
$email = filter_var(post_value('email'), FILTER_SANITIZE_EMAIL);
$password = post_value('password');

if (!$email || !$password) {
    json_response(["status" => "error", "message" => "Email dan password wajib diisi."], 400);
}

$result = $supabase->fetch("users", "email=eq." . urlencode($email) . "&limit=1");
if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result)], 500);
}

$user = $result['data'][0] ?? null;

if (!$user) {
    json_response(["status" => "error", "message" => "Email atau password salah."], 401);
}

$isPasswordValid = password_verify($password, $user['password']);

if (!$isPasswordValid) {
    json_response(["status" => "error", "message" => "Email atau password salah."], 401);
}

echo json_encode([
    "status" => "success",
    "nama" => $user['nama'],
    "email" => $user['email'],
    "role" => strtolower($user['role'] ?? 'siswa')
]);

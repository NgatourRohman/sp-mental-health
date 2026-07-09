<?php
require_once 'bootstrap.php';

require_post();
$supabase = get_supabase();

$email = filter_var(post_value('user_email'), FILTER_SANITIZE_EMAIL);
$nama = post_value('nama');
$tgl_lahir = post_value('tgl_lahir');
$jenis_kelamin = post_value('jenis_kelamin');
$alamat = post_value('alamat');
$no_telp = post_value('no_telp');

if (!$email || !$nama || !$tgl_lahir || !$jenis_kelamin || !$alamat || !$no_telp) {
    json_response(['status' => 'error', 'message' => 'Semua field wajib diisi'], 400);
}

$payload = [
    "email" => $email,
    "nama" => $nama,
    "tgl_lahir" => $tgl_lahir,
    "jenis_kelamin" => $jenis_kelamin,
    "alamat" => $alamat,
    "no_telp" => $no_telp
];

$existing = $supabase->fetch("user_profile", "email=eq." . urlencode($email) . "&limit=1");
if ($existing['status'] !== 'success') {
    json_response(['status' => 'error', 'message' => supabase_error($existing)], 500);
}

$result = !empty($existing['data'])
    ? $supabase->update("user_profile", $payload, "email=eq." . urlencode($email))
    : $supabase->insert("user_profile", $payload);

if ($result['status'] === 'success') {
    echo json_encode(['status' => 'success']);
} else {
    json_response(['status' => 'error', 'message' => supabase_error($result)], 500);
}

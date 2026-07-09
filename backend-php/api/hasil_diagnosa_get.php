<?php
require_once 'bootstrap.php';

$supabase = get_supabase();
$email = filter_var(get_value('email'), FILTER_SANITIZE_EMAIL);

if (!$email) {
    json_response(["error" => "Parameter email tidak ditemukan"], 400);
}

$result = $supabase->fetch(
    "hasil_diagnosa",
    "email=eq." . urlencode($email) . "&select=hasil_diagnosa,deskripsi,saran,rekomendasi,waktu_diagnosa&order=waktu_diagnosa.desc&limit=1"
);

if ($result['status'] !== 'success') {
    json_response(["error" => supabase_error($result)], 500);
}

$row = $result['data'][0] ?? null;
if (!$row) {
    echo json_encode(["error" => "Tidak ada hasil diagnosa untuk email tersebut."]);
    exit;
}

$response = [
    "kondisi" => $row['hasil_diagnosa'] ?? "-",
    "deskripsi" => $row['deskripsi'] ?? "-",
    "saran" => preg_split('/\r\n|\r|\n/', $row['saran'] ?? "-"),
    "rekomendasi" => $row['rekomendasi'] ?? "-"
];

echo json_encode($response);

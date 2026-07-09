<?php
require_once 'bootstrap.php';

$supabase = get_supabase();
$email = filter_var(get_value('email'), FILTER_SANITIZE_EMAIL);

if (!$email) {
    echo json_encode([]);
    exit;
}

$result = $supabase->fetch(
    "hasil_diagnosa",
    "email=eq." . urlencode($email) . "&select=hasil_diagnosa,deskripsi,saran,rekomendasi,waktu_diagnosa&order=waktu_diagnosa.desc"
);

if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result)], 500);
}

$data = [];
foreach (($result['data'] ?? []) as $row) {
    $data[] = [
        "kondisi" => $row['hasil_diagnosa'] ?? "-",
        "deskripsi" => $row['deskripsi'] ?? "-",
        "saran" => $row['saran'] ?? "-",
        "rekomendasi" => $row['rekomendasi'] ?? "-",
        "tanggal" => isset($row['waktu_diagnosa']) ? date('d-m-Y H:i', strtotime($row['waktu_diagnosa'])) : "-"
    ];
}

echo json_encode($data);

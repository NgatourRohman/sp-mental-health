<?php
require_once 'bootstrap.php';

$supabase = get_supabase();
$kode = get_value('kode');

if (!$kode) {
    echo json_encode([]);
    exit;
}

$gangguanRes = $supabase->fetch("gangguan", "kode=eq." . urlencode($kode) . "&select=nama&limit=1");
$gejalaRes = $supabase->fetch("gejala", "kode_gangguan=eq." . urlencode($kode) . "&select=nama_gejala&order=kode_gejala.asc");

if ($gejalaRes['status'] !== 'success') {
    json_response(["error" => supabase_error($gejalaRes)], 500);
}

$namaGangguan = $gangguanRes['data'][0]['nama'] ?? "";
$data = [];
foreach (($gejalaRes['data'] ?? []) as $row) {
    $data[] = [
        "nama" => $namaGangguan,
        "nama_gejala" => $row['nama_gejala'] ?? ""
    ];
}

echo json_encode($data);

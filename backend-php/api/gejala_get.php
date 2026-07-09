<?php
require_once 'bootstrap.php';

$supabase = get_supabase();
$gejalaRes = $supabase->fetch("gejala", "select=*&order=kode_gangguan.asc,kode_gejala.asc");
$gangguanRes = $supabase->fetch("gangguan", "select=kode,nama");

if ($gejalaRes['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($gejalaRes, "Gagal mengambil gejala.")], 500);
}

$gangguanMap = [];
if ($gangguanRes['status'] === 'success') {
    foreach (($gangguanRes['data'] ?? []) as $gangguan) {
        $gangguanMap[$gangguan['kode']] = $gangguan['nama'];
    }
}

$data = [];
foreach (($gejalaRes['data'] ?? []) as $row) {
    $row['nama_gangguan'] = $gangguanMap[$row['kode_gangguan'] ?? ''] ?? null;
    $row['aktif'] = !isset($row['aktif']) || $row['aktif'] ? 1 : 0;
    $data[] = $row;
}

echo json_encode($data);

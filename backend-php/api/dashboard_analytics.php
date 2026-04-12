<?php
header("Content-Type: application/json");
require_once '../core/SupabaseHelper.php';

$supabaseUrl  = getenv("SUPABASE_URL");
$supabaseKey  = getenv("SUPABASE_KEY");
$supabase = new SupabaseHelper($supabaseUrl, $supabaseKey);

$diagnosaRes = $supabase->fetch("hasil_diagnosa", "select=hasil_diagnosa,confidence");
if ($diagnosaRes['status'] !== 'success') {
    echo json_encode(["status" => "error", "message" => "Database fetch failed"]);
    exit;
}

$raw_data = $diagnosaRes['data'];
$distribusi = [];
$total_conf = 0;
$count_conf = 0;

foreach ($raw_data as $row) {
    $label = $row['hasil_diagnosa'];
    $distribusi[$label] = ($distribusi[$label] ?? 0) + 1;
    
    if (isset($row['confidence']) && $row['confidence'] > 0) {
        $total_conf += $row['confidence'];
        $count_conf++;
    }
}

$avg_confidence = ($count_conf > 0) ? round($total_conf / $count_conf, 2) : 0;

echo json_encode([
    "status" => "success",
    "data" => [
        "distribusi" => $distribusi,
        "avg_confidence" => $avg_confidence,
        "total_diagnosa" => count($raw_data),
        "cache_hits_estimate" => 0
    ]
]);

<?php
require_once 'bootstrap.php';

require_post();
$supabase = get_supabase();

$id = post_value('id');
$aktif = $_POST['aktif'] ?? null;

if (!$id || !isset($aktif)) {
    json_response(["status" => "error", "message" => "Data tidak lengkap."], 400);
}

$result = $supabase->update("gejala", [
    "aktif" => ((int) $aktif) === 1
], "id=eq." . urlencode($id));

if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result, "Gagal mengubah status gejala.")], 500);
}

echo json_encode(["status" => "success", "message" => "Status gejala berhasil diubah."]);

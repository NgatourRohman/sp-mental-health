<?php
require_once 'bootstrap.php';

$supabase = get_supabase();
$result = $supabase->fetch("gangguan", "select=*&order=kode.asc");

if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result)], 500);
}

echo json_encode($result['data'] ?? []);

<?php
require_once 'bootstrap.php';

$supabase = get_supabase();
$result = $supabase->fetch("relasi", "select=*&order=id.asc");

if ($result['status'] !== 'success') {
    json_response(["status" => "error", "message" => supabase_error($result)], 500);
}

echo json_encode($result['data'] ?? []);

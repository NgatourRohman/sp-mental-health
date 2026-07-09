<?php
require_once 'bootstrap.php';

$supabase = get_supabase();
$tables = ["users", "gangguan", "gejala"];
$response = ["users" => 0, "gangguan" => 0, "gejala" => 0];

foreach ($tables as $table) {
    $result = $supabase->fetch($table, "select=id");
    if ($result['status'] !== 'success') {
        json_response($response + ["error" => supabase_error($result)], 500);
    }
    $response[$table] = count($result['data'] ?? []);
}

echo json_encode($response);

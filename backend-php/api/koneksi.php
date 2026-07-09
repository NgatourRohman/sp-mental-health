<?php
header("Content-Type: application/json");
http_response_code(500);
echo json_encode([
    "status" => "error",
    "message" => "koneksi.php sudah deprecated. Gunakan bootstrap.php dan SupabaseHelper."
]);
exit;

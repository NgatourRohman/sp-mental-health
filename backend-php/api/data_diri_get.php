<?php
header('Content-Type: application/json');
include 'koneksi.php';

$email = $_GET['email'] ?? '';

if (!$email) {
    echo json_encode(['status' => 'error', 'message' => 'Email tidak diberikan']);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM user_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    echo json_encode(['status' => 'success', 'data' => $data]);
} else {
    echo json_encode(['status' => 'not_found']);
}

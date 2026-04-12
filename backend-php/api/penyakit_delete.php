<?php
include 'koneksi.php';

$id = $_POST['id'];
$sql = "DELETE FROM penyakit WHERE id=$id";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Data berhasil dihapus"]);
} else {
    echo json_encode(["status" => "error", "message" => $conn->error]);
}

<?php
require_once __DIR__ . '/../config/cors.php';
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/database.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['status'=>'error','message'=>'Session login tidak ditemukan.']);
    exit;
}

$user_id=(int)$_SESSION['user_id'];
$stmt=$conn->prepare('SELECT id,nama,email,no_hp,alamat,created_at FROM users WHERE id=? LIMIT 1');
$stmt->bind_param('i',$user_id);
$stmt->execute();
$user=$stmt->get_result()->fetch_assoc();
if (!$user) {
    $_SESSION=[];
    session_destroy();
    echo json_encode(['status'=>'error','message'=>'Data pengguna tidak ditemukan.']);
    exit;
}
echo json_encode(['status'=>'success','user'=>$user]);

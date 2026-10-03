<?php
require_once __DIR__ . "/../config/cors.php";
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../functions/fonnte.php";

if (empty($_SESSION["user_id"])) {
    echo json_encode([
        "status" => "error",
        "message" => "Session login tidak ditemukan."
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "Method tidak diperbolehkan."
    ]);
    exit;
}

$user_id = (int)$_SESSION["user_id"];

$stmt = $conn->prepare(
    "SELECT nama, no_hp FROM users WHERE id = ? LIMIT 1"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$user_data = $stmt->get_result()->fetch_assoc();

if (!$user_data) {
    echo json_encode([
        "status" => "error",
        "message" => "Data pengguna tidak ditemukan."
    ]);
    exit;
}

$pesan_wa = "INFORMASI PENGHAPUSAN AKUN\n\n"
          . "Halo {$user_data['nama']},\n"
          . "akun Anda telah berhasil dihapus dari sistem.";

$delete = $conn->prepare("DELETE FROM users WHERE id = ?");
$delete->bind_param("i", $user_id);

if (!$delete->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "Akun gagal dihapus."
    ]);
    exit;
}

$wa = kirimWhatsApp($user_data["no_hp"], $pesan_wa);

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        "",
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

echo json_encode([
    "status" => "success",
    "message" => "Akun berhasil dihapus.",
    "whatsapp" => $wa["success"] ? "terkirim" : "gagal",
    "whatsapp_response" => $wa["response"]
]);

<?php
require_once __DIR__ . "/../config/cors.php";
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "status" => "error",
        "message" => "Method tidak diperbolehkan."
    ]);
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Email dan password wajib diisi."
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, nama, email, no_hp, alamat, password
     FROM users WHERE email = ? LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();

$user = $stmt->get_result()->fetch_assoc();

if (!$user || !password_verify($password, $user["password"])) {
    echo json_encode([
        "status" => "error",
        "message" => "Email atau password salah."
    ]);
    exit;
}

session_regenerate_id(true);
$_SESSION["user_id"] = $user["id"];
$_SESSION["auth_user"] = [
    "id" => $user["id"],
    "nama" => $user["nama"],
    "email" => $user["email"]
];

echo json_encode([
    "status" => "success",
    "message" => "Login berhasil."
]);

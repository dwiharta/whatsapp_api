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

$nama = trim($_POST["nama"] ?? "");
$email = trim($_POST["email"] ?? "");
$no_hp = trim($_POST["no_hp"] ?? "");
$alamat = trim($_POST["alamat"] ?? "");
$password = $_POST["password"] ?? "";

if ($nama === "" || $email === "" || $no_hp === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Nama, email, dan nomor HP wajib diisi."
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "status" => "error",
        "message" => "Format email tidak valid."
    ]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT id FROM users WHERE email = ? AND id <> ?"
);
$stmt->bind_param("si", $email, $user_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Email tersebut sudah digunakan akun lain."
    ]);
    exit;
}

if ($password !== "") {
    if (strlen($password) < 6) {
        echo json_encode([
            "status" => "error",
            "message" => "Password baru minimal 6 karakter."
        ]);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare(
        "UPDATE users
         SET nama = ?, email = ?, no_hp = ?, alamat = ?, password = ?
         WHERE id = ?"
    );
    $stmt->bind_param(
        "sssssi",
        $nama,
        $email,
        $no_hp,
        $alamat,
        $hashedPassword,
        $user_id
    );
} else {
    $stmt = $conn->prepare(
        "UPDATE users
         SET nama = ?, email = ?, no_hp = ?, alamat = ?
         WHERE id = ?"
    );
    $stmt->bind_param(
        "ssssi",
        $nama,
        $email,
        $no_hp,
        $alamat,
        $user_id
    );
}

if (!$stmt->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal memperbarui profil."
    ]);
    exit;
}

$_SESSION["auth_user"]["nama"] = $nama;
$_SESSION["auth_user"]["email"] = $email;

$pesan = "INFORMASI PERUBAHAN PROFIL\n\n"
       . "Halo {$nama},\n"
       . "Data profil Anda berhasil diperbarui.\n\n"
       . "Nama: {$nama}\n"
       . "Email: {$email}\n"
       . "No. HP: {$no_hp}\n"
       . "Alamat: " . ($alamat !== "" ? $alamat : "-");

$wa = kirimWhatsApp($no_hp, $pesan);

echo json_encode([
    "status" => "success",
    "message" => "Profil berhasil diperbarui.",
    "whatsapp" => $wa["success"] ? "terkirim" : "gagal",
    "whatsapp_response" => $wa["response"]
]);

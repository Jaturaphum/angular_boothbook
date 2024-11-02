<?php
$host = 'myadminphp.bowlab.net';
$dbname = 'u583789277_wag12';
$username = 'u583789277_wag12';
$password = 'Episode2567';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $data['title'] ?? '';
    $first_name = $data['first_name'] ?? '';
    $last_name = $data['last_name'] ?? '';
    $phone_number = $data['phone_number'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(["message" => "กรุณากรอกข้อมูลให้ครบถ้วน"]);
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        http_response_code(409); // Conflict
        echo json_encode(["message" => "อีเมลนี้ถูกใช้แล้ว"]);
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO members (title, first_name, last_name, phone_number, email, password) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$title, $first_name, $last_name, $phone_number, $email, $hashed_password])) {
            echo json_encode(["message" => "สมัครสมาชิกสำเร็จ"]);
        } else {
            http_response_code(500); // Internal Server Error
            echo json_encode(["message" => "เกิดข้อผิดพลาดในการสมัครสมาชิก"]);
        }
    }
}
?>

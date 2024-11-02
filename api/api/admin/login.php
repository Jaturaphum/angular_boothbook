<?php
$host = 'myadminphp.bowlab.net';
$dbname = 'u583789277_wag12';
$username = 'u583789277_wag12';
$password = 'Episode2567';

header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['email']) && isset($data['password']) && !empty($data['email']) && !empty($data['password'])) {
        $email = $data['email'];
        $input_password = $data['password'];

        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($input_password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];

                echo json_encode([
                    "success" => true,
                    "message" => "เข้าสู่ระบบสำเร็จ",
                    "user_data" => $user
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(["success" => false, "message" => "รหัสผ่านไม่ถูกต้อง"], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(["success" => false, "message" => "ไม่พบผู้ใช้งานที่ตรงกับอีเมลนี้"], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(["success" => false, "message" => "กรุณากรอกอีเมลและรหัสผ่านให้ครบถ้วน"], JSON_UNESCAPED_UNICODE);
    }
}
?>

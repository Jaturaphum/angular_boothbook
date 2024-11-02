<?php
$host = 'myadminphp.bowlab.net';
$dbname = 'u583789277_wag12';
$username = 'u583789277_wag12';
$password = 'Episode2567';

header('Content-Type: application/json; charset=utf-8');

try {
    // เชื่อมต่อฐานข้อมูล
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบว่ามีการส่งอีเมลและรหัสผ่านหรือไม่
    if (isset($_POST['email']) && isset($_POST['password']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        $email = $_POST['email'];
        $input_password = $_POST['password'];

        // ดึงข้อมูลผู้ใช้ตามอีเมล
        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC); // ดึงข้อมูลในรูปแบบ associative array

        if ($user) {
            // ตรวจสอบรหัสผ่าน
            if (password_verify($input_password, $user['password'])) {
                session_start();
                // ตรวจสอบว่ามีการเริ่ม session แล้วหรือไม่
                if (!isset($_SESSION['user_id'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                }

                // ส่งข้อมูลสมาชิกทั้งหมด
                echo json_encode([
                    "status" => "success",
                    "message" => "เข้าสู่ระบบสำเร็จ",
                    "user_data" => $user // ส่งข้อมูลสมาชิกทั้งหมด
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(["status" => "error", "message" => "รหัสผ่านไม่ถูกต้อง"], JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "ไม่พบผู้ใช้งานที่ตรงกับอีเมลนี้"], JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "กรุณากรอกอีเมลและรหัสผ่านให้ครบถ้วน"], JSON_UNESCAPED_UNICODE);
    }
}
?>

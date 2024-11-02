<?php
$host = 'myadminphp.bowlab.net';
$dbname = 'u583789277_wag12';
$username = 'u583789277_wag12';
$password = 'Episode2567';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $member_id = filter_input(INPUT_POST, 'member_id', FILTER_SANITIZE_NUMBER_INT);
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_STRING);
    $phone_number = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if (empty($member_id) || empty($title) || empty($first_name) || empty($last_name) || empty($phone_number) || empty($email)) {
        echo json_encode(["message" => "กรุณากรอกข้อมูลให้ครบถ้วน"], JSON_UNESCAPED_UNICODE);
        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["message" => "อีเมลไม่ถูกต้อง"], JSON_UNESCAPED_UNICODE);
        exit();
    }

    try {

        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ? AND member_id != ?");
        $stmt->execute([$email, $member_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["message" => "อีเมลนี้ถูกใช้แล้ว"], JSON_UNESCAPED_UNICODE);
        } else {
            $sql = "UPDATE members SET title = ?, first_name = ?, last_name = ?, phone_number = ?, email = ?";
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql .= ", password = ?";
            }
            $sql .= " WHERE member_id = ?";
            
            $stmt = $pdo->prepare($sql);

            $params = [$title, $first_name, $last_name, $phone_number, $email];
            if (!empty($password)) {
                $params[] = $hashed_password;
            }
            $params[] = $member_id;

            if ($stmt->execute($params)) {
                echo json_encode(["message" => "แก้ไขข้อมูลสำเร็จ"], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(["message" => "เกิดข้อผิดพลาดในการแก้ไขข้อมูล"], JSON_UNESCAPED_UNICODE);
            }
        }
    } catch (PDOException $e) {
        echo json_encode(["message" => "ข้อผิดพลาด: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}
?>

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
    echo json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['booth_id'])) {
        $booth_id = $_POST['booth_id'];
        $stmt = $pdo->prepare("DELETE FROM booths WHERE booth_id = :booth_id");
        $stmt->bindParam(':booth_id', $booth_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo json_encode(["message" => "ได้ลบข้อมูลของบูธที่มี ID $booth_id แล้ว"], JSON_UNESCAPED_UNICODE);
        } else {
            $errorInfo = $stmt->errorInfo();
            echo json_encode(["message" => "ไม่สามารถลบข้อมูลได้", "error" => $errorInfo], JSON_UNESCAPED_UNICODE);
        }
    }
}
?>

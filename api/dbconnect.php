<?php
    $host = '151.106.124.154';
    $username = 'u583789277_wag12';
    $password = 'Episode2567';
    $dbname = 'u583789277_wag12';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo json_encode(["message" => "การเชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage()], JSON_UNESCAPED_UNICODE);
        exit();
    }
?>

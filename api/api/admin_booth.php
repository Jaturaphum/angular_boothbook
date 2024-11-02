<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// ตรวจสอบการเชื่อมต่อกับฐานข้อมูล
if (!isset($pdo)) {
    throw new Exception("Database connection not initialized.");
}

// เพิ่มบูธใหม่
$app->post('/add_booth', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $required_fields = ['booth_name', 'booth_size', 'booth_price', 'zone_id'];

    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบ: {$field}"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO booths (booth_name, booth_size, booth_price, zone_id, booth_status) VALUES (?, ?, ?, ?, 'ว่าง')");
        $stmt->execute([$data['booth_name'], $data['booth_size'], $data['booth_price'], $data['zone_id']]);
        $response->getBody()->write(json_encode(["message" => "เพิ่มบูธสำเร็จ"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// อัปเดตข้อมูลบูธ
$app->put('/update_booth/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $booth_id = $args['id'];
    $data = $request->getParsedBody();
    $required_fields = ['booth_name', 'booth_size', 'booth_price', 'zone_id', 'booth_status'];

    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบ: {$field}"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }
    try {
        $stmt = $pdo->prepare("UPDATE booths SET booth_name = ?, booth_size = ?, booth_price = ?, zone_id = ?, booth_status = ? WHERE booth_id = ?");
        $stmt->execute([$data['booth_name'], $data['booth_size'], $data['booth_price'], $data['zone_id'], $data['booth_status'], $booth_id]);
        $response->getBody()->write(json_encode(["message" => "อัปเดตข้อมูลบูธสำเร็จ"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// อนุมัติการจอง
$app->post('/approve_booking', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $booking_id = $data['booking_id'] ?? null;

    if (!$booking_id) {
        $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบ: booking_id"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $pdo->beginTransaction();

        // ตรวจสอบสถานะการจองและบูธที่เกี่ยวข้อง
        $stmt = $pdo->prepare("SELECT booth_id FROM bookings WHERE booking_id = ? AND booking_status = 'ชำระเงิน'");
        $stmt->execute([$booking_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            // อนุมัติการจอง
            $updateBooking = $pdo->prepare("UPDATE bookings SET booking_status = 'อนุมัติแล้ว' WHERE booking_id = ?");
            $updateBooking->execute([$booking_id]);

            // อัปเดตสถานะบูธ
            $updateBooth = $pdo->prepare("UPDATE booths SET booth_status = 'จองแล้ว' WHERE booth_id = ?");
            $updateBooth->execute([$booking['booth_id']]);

            $pdo->commit();
            $response->getBody()->write(json_encode(["message" => "การจองได้รับการอนุมัติแล้ว"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $pdo->rollBack();
            $response->getBody()->write(json_encode(["message" => "ไม่พบการจองที่ชำระเงินแล้ว"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// เพิ่มโซนใหม่
$app->post('/add_zone', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $required_fields = ['zone_name', 'zone_info'];

    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบ: {$field}"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO zones (zone_name, zone_info) VALUES (?, ?)");
        $stmt->execute([$data['zone_name'], $data['zone_info']]);
        $response->getBody()->write(json_encode(["message" => "เพิ่มโซนสำเร็จ"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

$app->get('/get_zone[/{id}]', function (Request $request, Response $response, array $args) use ($pdo) {
    $zone_id = $args['id'] ?? null; // รับ zone_id หรือใช้ null หากไม่มีการส่งมา

    try {
        if ($zone_id) {
            // ดึงข้อมูลเฉพาะโซนตาม zone_id
            $stmt = $pdo->prepare("SELECT * FROM zones WHERE zone_id = ?");
            $stmt->execute([$zone_id]);
            $zone = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($zone) {
                // ถ้าพบข้อมูลโซน
                $response->getBody()->write(json_encode($zone));
                return $response->withHeader('Access-Control-Allow-Origin', '*')
                        ->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                // ถ้าไม่พบโซนที่ต้องการ
                $response->getBody()->write(json_encode(["message" => "ไม่พบโซนที่มี ID นี้"]));
                return $response->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
        } else {
            // ถ้าไม่มีการส่ง zone_id มา ให้ดึงข้อมูลทั้งหมด
            $stmt = $pdo->query("SELECT * FROM zones");
            $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->getBody()->write(json_encode($zones));
            return $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json')->withStatus(200);
        }
    } catch (PDOException $e) {
        // จัดการข้อผิดพลาดฐานข้อมูล
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// อัปเดตข้อมูลโซน
$app->put('/update_zone/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $zone_id = $args['id'];
    $data = $request->getParsedBody();
    $required_fields = ['zone_name', 'zone_info', 'booth_count']; // เพิ่ม booth_count

    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบ: {$field}"]));
            return $response->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE zones SET zone_name = ?, zone_info = ?, booth_count = ? WHERE zone_id = ?");
        $stmt->execute([$data['zone_name'], $data['zone_info'], $data['booth_count'], $zone_id]); // เพิ่ม booth_count
        $response->withHeader('Access-Control-Allow-Origin', '*')->getBody()->write(json_encode(["message" => "อัปเดตข้อมูลโซนสำเร็จ"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]));
        return $response->withHeader('Access-Control-Allow-Origin', '*')
        ->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// ลบโซน
$app->delete('/delete_zone/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $zone_id = $args['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM zones WHERE zone_id = ?");
        $stmt->execute([$zone_id]);
        $response->getBody()->write(json_encode(["message" => "ลบโซนสำเร็จ"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Route สำหรับการสร้างอีเวนต์ใหม่
$app->post('/create_event', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $event_name = $data['event_name'] ?? null;
    $event_start_date = $data['event_start_date'] ?? null;
    $event_end_date = $data['event_end_date'] ?? null;

    if ($event_name && $event_start_date && $event_end_date) {
        try {
            $stmt = $pdo->prepare("INSERT INTO events (event_name, event_start_date, event_end_date) VALUES (?, ?, ?)");
            $stmt->execute([$event_name, $event_start_date, $event_end_date]);
            $response->getBody()->write(json_encode(["message" => "เพิ่มอีเวนต์เรียบร้อยแล้ว"]));
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]));
        }
    } else {
        $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบถ้วน"]));
    }
    return $response->withHeader('Content-Type', 'application/json');
});

// Route สำหรับการลบอีเวนต์
$app->post('/delete_event', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $event_id = filter_var($data['event_id'] ?? null, FILTER_SANITIZE_NUMBER_INT);

    if (!$event_id) {
        $response->getBody()->write(json_encode(["message" => "ID อีเวนต์ไม่ถูกต้อง"], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->execute([$event_id]);

        if ($stmt->rowCount() > 0) {
            $response->getBody()->write(json_encode(["message" => "ลบอีเวนต์เรียบร้อย"], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(["message" => "ไม่พบอีเวนต์ที่ต้องการลบ"], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "ข้อผิดพลาด: " . $e->getMessage()], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Route สำหรับการอัปเดตอีเวนต์
$app->post('/update_event', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $event_id = filter_var($data['event_id'] ?? null, FILTER_SANITIZE_NUMBER_INT);
    $event_name = $data['event_name'] ?? null;
    $event_start_date = $data['event_start_date'] ?? null;
    $event_end_date = $data['event_end_date'] ?? null;

    if (!$event_id || !$event_name || !$event_start_date || !$event_end_date) {
        $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบถ้วนหรือไม่ถูกต้อง"], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        $stmt = $pdo->prepare("UPDATE events SET event_name = ?, event_start_date = ?, event_end_date = ? WHERE event_id = ?");
        $stmt->execute([$event_name, $event_start_date, $event_end_date, $event_id]);
        
        if ($stmt->rowCount() > 0) {
            $response->getBody()->write(json_encode(["message" => "อัปเดตอีเวนต์เรียบร้อย"], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(["message" => "ไม่พบอีเวนต์ที่ต้องการอัปเดต"], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "ข้อผิดพลาด: " . $e->getMessage()], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Route สำหรับการดึงข้อมูลสมาชิกทั้งหมด
$app->get('/get_members', function (Request $request, Response $response) use ($pdo) {
    try {
        $stmt = $pdo->query("SELECT member_id, title, first_name, last_name, phone_number, email FROM members");
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($members, JSON_UNESCAPED_UNICODE));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "การดึงข้อมูลล้มเหลว: " . $e->getMessage()]));
    }
    return $response->withHeader('Content-Type', 'application/json');
});
// Route สำหรับการดึงข้อมูลadminทั้งหมด
$app->get('/get_admin', function (Request $request, Response $response) use ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM admin");
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($members, JSON_UNESCAPED_UNICODE));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "การดึงข้อมูลล้มเหลว: " . $e->getMessage()]));
    }
    return $response->withHeader('Content-Type', 'application/json');
});
$app->get('/get_bookings', function (Request $request, Response $response) use ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM bookings");
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($members, JSON_UNESCAPED_UNICODE));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "การดึงข้อมูลล้มเหลว: " . $e->getMessage()]));
    }
    return $response->withHeader('Content-Type', 'application/json');
});

// Route สำหรับการอัปเดตข้อมูลสมาชิก
$app->post('/update_memberbyadmin', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $member_id = $data['member_id'];
    $title = $data['title'];
    $first_name = $data['first_name'];
    $last_name = $data['last_name'];
    $phone_number = $data['phone_number'];
    $email = $data['email'];
    if ($member_id && $title && $first_name && $last_name && $phone_number && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $sql = "UPDATE members SET title = ?, first_name = ?, last_name = ?, phone_number = ?, email = ? WHERE member_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$title, $first_name, $last_name, $phone_number, $email, $member_id]);
            $response->getBody()->write(json_encode(["message" => "อัปเดตข้อมูลสมาชิกสำเร็จ"], JSON_UNESCAPED_UNICODE));
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["message" => "Error: " . $e->getMessage()], JSON_UNESCAPED_UNICODE));
        }
    } else {
        $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบถ้วนหรือไม่ถูกต้อง"], JSON_UNESCAPED_UNICODE));
    }
    return $response->withHeader('Content-Type', 'application/json');
});

// Route สำหรับการดึงข้อมูลบูธตัวอย่าง
$app->get('/get_booths_exam', function (Request $request, Response $response) use ($pdo) {
    $sql = "SELECT m.first_name, m.last_name, m.phone_number, bo.booth_name, z.zone_name
            FROM bookings AS b
            JOIN members AS m ON b.member_id = m.member_id
            JOIN booths AS bo ON b.booth_id = bo.booth_id
            JOIN zones AS z ON bo.zone_id = z.zone_id
            WHERE bo.booth_status = 'อยู่ระหว่างตรวจสอบ'
            AND b.booking_status != 'ยกเลิกการจอง'";
    try {
        $stmt = $pdo->query($sql);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "Error: " . $e->getMessage()], JSON_UNESCAPED_UNICODE));
    }
    return $response->withHeader('Content-Type', 'application/json');
});

// Route สำหรับการลบข้อมูลโซน
$app->post('/delete_zone', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $zone_name = $data['zone_name'] ?? null;
    if ($zone_name) {
        try {
            $stmt = $pdo->prepare("DELETE FROM zones WHERE zone_name = :zone_name");
            $stmt->bindParam(':zone_name', $zone_name, PDO::PARAM_STR);
            if ($stmt->execute()) {
                $response->getBody()->write(json_encode(["message" => "ได้ลบข้อมูลของโซนชื่อ $zone_name แล้ว"], JSON_UNESCAPED_UNICODE));
            } else {
                $errorInfo = $stmt->errorInfo();
                $response->getBody()->write(json_encode(["message" => "ไม่สามารถลบข้อมูลได้", "error" => $errorInfo], JSON_UNESCAPED_UNICODE));
            }
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["message" => "Error: " . $e->getMessage()], JSON_UNESCAPED_UNICODE));
        }
    } else {
        $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบถ้วน"], JSON_UNESCAPED_UNICODE));
    }
    return $response->withHeader('Content-Type', 'application/json');
});

// ฟังก์ชันสำหรับดึงข้อมูลการจองตามสถานะ
$app->get('/get_bookings_by_status/{status}', function (Request $request, Response $response, array $args) use ($pdo) {
    $status = $args['status']; // รับสถานะจากเส้นทาง
    // ตรวจสอบสถานะที่ได้รับว่าเป็นสถานะที่ยอมรับ
    $allowedStatuses = ['จอง', 'ชำระเงิน', 'อนุมัติแล้ว', 'ยกเลิกการจอง'];
    if (!in_array($status, $allowedStatuses)) {
        $response->getBody()->write(json_encode(["message" => "สถานะไม่ถูกต้อง"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }
    // สร้างคำสั่ง SQL สำหรับดึงข้อมูลการจองตามสถานะ
    $sql = "SELECT m.first_name, m.last_name, m.phone_number, bo.booth_name, z.zone_name, b.booking_status
            FROM bookings AS b
            JOIN members AS m ON b.member_id = m.member_id
            JOIN booths AS bo ON b.booth_id = bo.booth_id
            JOIN zones AS z ON bo.zone_id = z.zone_id
            WHERE b.booking_status = :status";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // ส่งข้อมูลการจองที่ตรงกับสถานะในรูปแบบ JSON
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

?>
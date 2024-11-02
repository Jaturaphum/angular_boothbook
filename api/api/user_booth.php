<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// ตรวจสอบคำขอ OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); // ตอบกลับด้วย 200 OK สำหรับ OPTIONS request
    exit();
}

if (!isset($pdo)) {
    throw new Exception("Database connection not initialized.");
}

// ฟังก์ชันช่วยในการตอบกลับ JSON
function jsonResponse(Response $response, $data, $status = 200) {
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE));
    return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
}

// ฟังก์ชันช่วยตรวจสอบข้อมูลที่ต้องการ
function validateFields($data, $fields) {
    foreach ($fields as $field) {
        if (empty($data[$field])) {
            return ["message" => "ข้อมูลไม่ครบ: {$field}"];
        }
    }
    return null;
}

// Endpoint สำหรับการลงทะเบียน
$app->post('/register', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $required_fields = ['title', 'first_name', 'last_name', 'phone_number', 'email', 'password'];

    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบ: {$field}"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
    $stmt->execute([$data['email']]);
    if ($stmt->rowCount() > 0) {
        $response->getBody()->write(json_encode(["message" => "อีเมลนี้ถูกใช้แล้ว"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(409);
    }

    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO members (title, first_name, last_name, phone_number, email, password) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$data['title'], $data['first_name'], $data['last_name'], $data['phone_number'], $data['email'], $hashed_password])) {
        $response->getBody()->write(json_encode(["message" => "สมัครสมาชิกสำเร็จ"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } else {
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาดในการสมัครสมาชิก"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Endpoint สำหรับการสร้างการจอง
$app->post('/create_booking', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $required_fields = ['member_id', 'booth_id', 'product_info', 'event_id', 'booth_price'];

    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบ: {$field}"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    $stmt = $pdo->prepare("INSERT INTO bookings (booth_id, product_info, member_id, event_id, booth_price, booking_status, booking_date) 
                           VALUES (?, ?, ?, ?, ?, 'จอง', NOW())");
    if ($stmt->execute([$data['booth_id'], $data['product_info'], $data['member_id'], $data['event_id'], $data['booth_price']])) {
        $response->getBody()->write(json_encode(["message" => "จองบูธสำเร็จ"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } else {
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาดในการจองบูธ"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Endpoint สำหรับการยกเลิกการจอง
$app->post('/cancel_booking', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    if (empty($data['booking_id'])) {
        $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบถ้วน: booking_id"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'ยกเลิกการจอง' WHERE booking_id = ?");
    if ($stmt->execute([$data['booking_id']])) {
        $pdo->commit();
        $response->getBody()->write(json_encode(["message" => "ยกเลิกการจองสำเร็จ"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $pdo->rollBack();
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาดในการยกเลิกการจอง"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Endpoint สำหรับการดึงข้อมูลบูธทั้งหมด
$app->get('/get_booths', function (Request $request, Response $response) use ($pdo) {
    $zone_id = $request->getQueryParams()['zone_id'] ?? null;

    $query = "SELECT * FROM booths";
    $params = [];
    if ($zone_id) {
        $query .= " WHERE zone_id = ?";
        $params[] = $zone_id;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $booths = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($booths));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

// Endpoint สำหรับการดึงข้อมูลบูธโดย ID
$app->get('/get_booth/{id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $booth_id = $args['id'];
    $stmt = $pdo->prepare("SELECT * FROM booths WHERE booth_id = ?");
    $stmt->execute([$booth_id]);
    $booth = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booth) {
        $response->getBody()->write(json_encode($booth));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(["message" => "ไม่พบข้อมูลบูธที่มี ID นี้"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
});

// Route to get booths by zone_id
$app->get('/get_booths_by_zone/{zone_id}', function (Request $request, Response $response, array $args) use ($pdo) {
    $zone_id = (int)$args['zone_id'];

    if (!$zone_id) {
        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Invalid zone_id"
        ], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    try {
        // Prepare and execute the query to get booths by zone_id
        $stmt = $pdo->prepare("SELECT * FROM booths WHERE zone_id = :zone_id");
        $stmt->execute(['zone_id' => $zone_id]);
        $booths = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($booths) {
            $response->getBody()->write(json_encode([
                "status" => "success",
                "data" => $booths
            ], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode([
                "status" => "error",
                "message" => "No booths found for this zone"
            ], JSON_UNESCAPED_UNICODE));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode([
            "status" => "error",
            "message" => "Database error: " . $e->getMessage()
        ], JSON_UNESCAPED_UNICODE));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Endpoint สำหรับการดึงการจองของสมาชิก
$app->get('/get_members_booking', function (Request $request, Response $response) use ($pdo) {
    $member_id = $request->getQueryParams()['member_id'] ?? null;
    if (!$member_id) {
        $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบถ้วน: member_id"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE member_id = ?");
    $stmt->execute([$member_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode($bookings));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

// Endpoint สำหรับการดึงข้อมูลโซนทั้งหมด
$app->get('/get_zones', function (Request $request, Response $response) use ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM zones");
        $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($zones));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});


// Endpoint สำหรับการดึงข้อมูลevebtsทั้งหมด
$app->get('/get_events', function (Request $request, Response $response) use ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM events");
        $zones = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($zones));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาด: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Endpoint สำหรับการชำระเงิน
$app->post('/payment', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $required_fields = ['booking_id', 'member_id', 'booth_id', 'booth_price', 'payment_slip'];

    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบ: {$field}"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    $stmt = $pdo->prepare("UPDATE bookings SET booking_status = 'ชำระเงิน', payment_slip = ?, payment_date = NOW() 
                           WHERE booking_id = ? AND member_id = ?");
    if ($stmt->execute([$data['payment_slip'], $data['booking_id'], $data['member_id']])) {
        $response->getBody()->write(json_encode(["message" => "ชำระเงินสำเร็จ"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาดในการชำระเงิน"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Endpoint สำหรับอัปเดตข้อมูลสมาชิก
$app->post('/update_member', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    $required_fields = ['member_id', 'title', 'first_name', 'last_name', 'phone_number', 'email'];

    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $response->getBody()->write(json_encode(["message" => "ข้อมูลไม่ครบถ้วน: {$field}"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
    }

    $sql = "UPDATE members SET title = ?, first_name = ?, last_name = ?, phone_number = ?, email = ?";
    $params = [$data['title'], $data['first_name'], $data['last_name'], $data['phone_number'], $data['email']];
    
    if (!empty($data['password'])) {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql .= ", password = ?";
        $params[] = $hashed_password;
    }
    $sql .= " WHERE member_id = ?";
    $params[] = $data['member_id'];

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($params)) {
        $response->getBody()->write(json_encode(["message" => "แก้ไขข้อมูลสำเร็จ"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(["message" => "เกิดข้อผิดพลาดในการแก้ไขข้อมูล"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Endpoint สำหรับการเข้าสู่ระบบ
$app->post('/login', function (Request $request, Response $response) use ($pdo) {
    $data = $request->getParsedBody();
    if ($error = validateFields($data, ['email', 'password'])) {
        return jsonResponse($response, $error, 400);
    }

    try {
        // ตรวจสอบการเข้าสู่ระบบในตาราง admin
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
        $stmt->execute([$data['email']]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($data['password'], $admin['password'])) {
            return jsonResponse($response, [
                "status" => "success",
                "message" => "เข้าสู่ระบบสำเร็จ (Admin)",
                "user_data" => $admin
            ], 200);
        }

        // ตรวจสอบการเข้าสู่ระบบในตาราง members
        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ?");
        $stmt->execute([$data['email']]);
        $member = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($member && password_verify($data['password'], $member['password'])) {
            return jsonResponse($response, [
                "status" => "success",
                "message" => "เข้าสู่ระบบสำเร็จ (Member)",
                "user_data" => $member
            ], 200);
        }

        // กรณีไม่พบข้อมูลผู้ใช้
        return jsonResponse($response, [
            "status" => "error",
            "message" => "อีเมลหรือรหัสผ่านไม่ถูกต้อง"
        ], 401);

    } catch (PDOException $e) {
        return jsonResponse($response, [
            "status" => "error",
            "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()
        ], 500);
    }
});
?>
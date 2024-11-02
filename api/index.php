<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Slim\Exception\HttpNotFoundException;


require __DIR__ . '/vendor/autoload.php';

$app = AppFactory::create();
$app->setBasePath('/api');
$app->addErrorMiddleware(true, true, true);

// เชื่อมต่อกับฐานข้อมูล
require __DIR__ . '/dbconnect.php';
require __DIR__ . '/api/user_booth.php';
require __DIR__ . '/api/admin_booth.php';

// เพิ่มตัวจัดการ OPTIONS Request เพื่อสนับสนุน CORS

// เพิ่ม Middleware สำหรับ CORS
$app->add(middleware: function (Request $request, $handler) {
    $response = $handler->handle($request);
    return $response
    ->withHeader('Access-Control-Allow-Origin', '*')
    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});


$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                    ->withStatus(200);
});





// เส้นทางสำหรับทดสอบการเชื่อมต่อ
$app->get('/ping', function (Request $request, Response $response) {
    $response->getBody()->write("Pong!!!");
    return $response->withHeader('Content-Type', 'text/plain');
});

// จัดการเส้นทางที่ไม่พบในระบบเพื่อแสดง 404 Not Found
$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}', function ($request, $response) {
    throw new HttpNotFoundException($request);
});

// เริ่มรันแอปพลิเคชัน
$app->run();

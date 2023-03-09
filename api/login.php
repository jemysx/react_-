<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
require_once ('../config.php');
//引入jwt相关的库
require_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;

// 设置密钥
// $secret_key =bin2hex(random_bytes(32));
// print_r($secret_key);die;
// 验证JWT token
//if (!validate_token()) {
//    http_response_code(401);
//    echo json_encode(array("message" => "Unauthorized"));
//    exit();
//}
$res['result'] = 'failed';
// 处理用户登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    //连接数据库
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER,DB_PASSWORD);
    //根据用户名从数据库中获取用户信息
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // 验证用户名和密码，从数据库中检查用户是否存在并是否正确
//    print_r(password_verify($password,$user['password']));die;

    $hash = password_hash($user['password'], PASSWORD_DEFAULT);
    if ($user&& password_verify($password,$hash) ) {
//        print_r($user);die;
//        echo 123;die;
        // 用户验证成功，生成 JWT
        $payload = array(
            "user_id" => $user["id"],
            "username" =>$user["username"],
            "role" => "admin",
            "exp" => time() + 3600
        );

        // 定义 JWT token 的 header 部分
        $header = [
            'alg' => 'HS256', // 使用 HMAC-SHA256 算法加密
            'typ' => 'JWT',
        ];
        $jwt_token = JWT::encode($payload, JWT_SECRET, 'HS256', null, $header);
//        $jwt = JWT::encode($payload,JWT_SECRET,'HS256');
        // 返回 JWT
        header('Content-Type: application/json');
        $res['result'] = 'success';
        $res['jwt'] = $jwt_token;
        echo json_encode($res);
    } else {
        // 用户验证失败，返回错误信息
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json');
        $res['message'] = "用户名或者密码错误";
        echo json_encode($res);
    }
} else {

    // 其他请求返回错误信息
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Invalid request'));
}

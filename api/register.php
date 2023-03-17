<?php
require_once ('../config.php');
//引入jwt相关的库
require_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;

// 接收POST请求
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed");
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    // 接收表单数据
    $username = $_POST["username"];
    $password = $_POST["password"];

    //连接数据库
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    //根据用户名从数据库中获取用户信息
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // 检查用户名和电子邮件是否已经存在
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // 用户验证失败，返回错误信息
        header('HTTP/1.1 401 Unauthorized');
        header('Content-Type: application/json');
        echo json_encode(array('message' => '该用户已存在'));
    }else{

        // 哈希密码
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // 插入用户记录
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username,  $password_hash);
        $uid = $stmt->execute();
        if($uid){
           
            header('Content-Type: application/json');
            $res['result'] = 'success';
            $res['code'] = "200";
            echo json_encode($res);
        }else{
            $res['result'] = 'field';
            echo json_encode($res);
        }
    }
}




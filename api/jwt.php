<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
require_once '../vendor/autoload.php';
require_once ('../config.php');
Use Firebase\JWT\Key;

use \Firebase\JWT\JWT;

$jwt = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
$res = [];

if (empty($jwt)) {
    header('HTTP/1.1 401 Unauthorized');
    header('Content-Type: application/json');
    $res['msg'] = 'You do not have permission to access.';
    echo json_encode($res);
    exit;
}else{
        // 判断请求头中是否使用Bearer认证方案
        if (strpos($jwt, 'Bearer ') === 0) {
            // 从Authorization字段中解析出JWT token，并进行验证
            $jwt_token = substr($jwt, 7);

            try {
                // 解析 JWT token，并验证签名
                $decoded_token = JWT::decode($jwt_token, new key (JWT_SECRET,'HS256'));
                // 从 token 的 payload 部分获取用户信息
                $user_id = $decoded_token->user_id;
//                print_r($user_id)
                $username = $decoded_token->username;
                $role = $decoded_token->role;
                $arr = (array)$decoded_token;
                //判断token时效
                if ($decoded_token->exp < time()) {
                    header('HTTP/1.1 401 Unauthorized');
                    header('Content-Type: application/json');
                    $res['msg'] = 'token已过期,请重新登录';
                    echo json_encode($res);

                }else{
                    header('Content-Type: application/json');
                    $res['result'] = 'success';
                    $res['info'] = $arr;
                    echo json_encode($res);
                }


            } catch (Exception $e) {
                // 处理 token 验证失败的情况，例如返回错误响应
                header('HTTP/1.1 401 Unauthorized');
                header('Content-Type: application/json');
                $res['msg'] = 'Token验证失败,请重新登录';
                echo json_encode($res);

            }
        }

}


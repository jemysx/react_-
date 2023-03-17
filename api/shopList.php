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
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    //连接数据库

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
    die("连接数据库失败: " . $conn->connect_error);
    }
    // echo 4524;die;

    // 查询推荐商品
    $sql = "SELECT p.*, AVG(r.rating) AS average_rating, COUNT(r.review_id) AS total_review_count FROM products p LEFT JOIN review r ON p.id = r.product_id GROUP BY p.id ORDER BY RAND() LIMIT 6";
    $result = $conn->query($sql);

    // 构建响应数据
    $data = array();
    // print_r($result);die;
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $item = array(
              'id' => $row['id'],
              'name' => $row['name'],
              'description' => $row['description'],
              'price' => $row['price'],
              'category_id' => $row['category_id'],
              'image_url' => $row['image_url'],
              'created_at' => $row['created_at'],
              'updated_at' => $row['updated_at'],
              'low_price' => $row['low_price'],
              'high_price' => $row['high_price'],
              'sale' => $row['sale'],
              'average_rating' => $row['average_rating'],
              'total_review_count'=>$row['total_review_count']
            );
            array_push($data, $item);
          }
    } 

  // print_r($data);die;
    
            // 返回响应数据
        header('Content-Type: application/json');
        echo json_encode($data);

        // 关闭数据库连接
        $conn->close();
} else {

    // 其他请求返回错误信息
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Invalid request'));
}

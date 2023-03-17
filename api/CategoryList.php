<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
require_once ('../config.php');
//引入jwt相关的库
require_once '../vendor/autoload.php';
use \Firebase\JWT\JWT;

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
    $sql = "SELECT categories.id AS category_id, categories.name AS category_name, categories.img_src AS category_img_src, products.id AS product_id, products.name AS product_name FROM categories LEFT JOIN products ON categories.id = products.category_id";
    $result = $conn->query($sql);

    // 构建响应数据
    $categories = array();
    // print_r($result);die;
    if ($result->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categoryId = $row['category_id'];
            $categoryName = $row['category_name'];
            $subcategoryId = $row['product_id'];
            $subcategoryName = $row['product_name'];
        
            // 检查是否已经存在这个分类
            if (!isset($categories[$categoryId])) {
                $categories[$categoryId] = array(
                    'id' => $categoryId,
                    'title' => $categoryName,
                    'subcategories' => array()
                );
            }
        
            // 添加子分类
            $categories[$categoryId]['subcategories'][] = array(
                'id' => $subcategoryId,
                'title' => $subcategoryName
            );
        }
    } 

//   print_r($categories);die;
    // 将关联数组转换为索引数组
        $categories = array_values($categories);

        // 将数组转换为 JSON 格式
        $json = json_encode($categories, JSON_UNESCAPED_UNICODE);
            // 返回响应数据
        header('Content-Type: application/json');
        echo $json;
 
        // 关闭数据库连接
        $conn->close();
} else {

    // 其他请求返回错误信息
    header('HTTP/1.1 400 Bad Request');
    header('Content-Type: application/json');
    echo json_encode(array('error' => 'Invalid request'));
}

<?php
// save_danmu.php - 弹幕服务器端处理脚本

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$message = $_POST['message'] ?? '';

// 弹幕存储文件
$filename = 'danmu.txt';

if ($action === 'test') {
    // 测试连接
    echo json_encode(['status' => 'success', 'message' => '服务器连接正常']);
    exit;
}

if ($action === 'get') {
    // 获取弹幕列表
    if (file_exists($filename)) {
        $danmus = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        echo json_encode(['status' => 'success', 'data' => $danmus]);
    } else {
        echo json_encode(['status' => 'success', 'data' => []]);
    }
    exit;
}

if (!empty($message)) {
    // 保存弹幕
    $timestamp = date('Y-m-d H:i:s');
    $data = $timestamp . ' | ' . htmlspecialchars($message) . PHP_EOL;
    
    if (file_put_contents($filename, $data, FILE_APPEND | LOCK_EX)) {
        echo json_encode(['status' => 'success', 'message' => '弹幕保存成功']);
    } else {
        echo json_encode(['status' => 'error', 'message' => '弹幕保存失败']);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => '无效请求']);
?>
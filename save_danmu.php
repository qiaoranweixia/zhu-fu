<?php
// danmu_server.php - 弹幕服务器端处理脚本

// 设置响应头
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 处理预检请求
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 弹幕存储文件
$filename = 'danmu.txt';

// 获取参数（支持GET和POST）
$action = $_REQUEST['action'] ?? '';
$message = $_REQUEST['message'] ?? '';

// 处理不同操作
if ($action === 'save') {
    // 保存弹幕
    if (!empty($message)) {
        $timestamp = date('Y-m-d H:i:s');
        $data = $timestamp . ' | ' . htmlspecialchars($message) . PHP_EOL;
        
        if (file_put_contents($filename, $data, FILE_APPEND | LOCK_EX)) {
            echo json_encode(['status' => 'success', 'message' => '弹幕保存成功']);
        } else {
            echo json_encode(['status' => 'error', 'message' => '弹幕保存失败']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => '弹幕内容为空']);
    }
} elseif ($action === 'get') {
    // 获取弹幕列表
    if (file_exists($filename)) {
        $danmus = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        // 只返回最近100条弹幕，避免数据过大
        $recentDanmus = array_slice($danmus, -100);
        
        // 提取弹幕内容（去掉时间戳）
        $messages = array_map(function($line) {
            $parts = explode(' | ', $line, 2);
            return count($parts) > 1 ? $parts[1] : $line;
        }, $recentDanmus);
        
        echo json_encode(['status' => 'success', 'messages' => $messages]);
    } else {
        echo json_encode(['status' => 'success', 'messages' => []]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '无效操作']);
}
?>

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

// 记录请求日志（用于调试）
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Action: $action, Message: $message\n", FILE_APPEND);

// 处理不同操作
if ($action === 'save') {
    // 保存弹幕
    if (!empty($message)) {
        $timestamp = date('Y-m-d H:i:s');
        $data = $timestamp . ' | ' . htmlspecialchars($message) . PHP_EOL;
        
        // 检查文件是否可写，如果不可写则尝试创建
        if (!file_exists($filename)) {
            // 尝试创建文件
            $file = fopen($filename, 'w');
            if ($file) {
                fclose($file);
                file_put_contents('debug.log', "创建文件: $filename\n", FILE_APPEND);
            } else {
                echo json_encode(['status' => 'error', 'message' => '无法创建弹幕文件']);
                exit();
            }
        }
        
        // 尝试写入文件
        if (file_put_contents($filename, $data, FILE_APPEND | LOCK_EX)) {
            echo json_encode(['status' => 'success', 'message' => '弹幕保存成功']);
        } else {
            // 记录错误信息
            $error = error_get_last();
            file_put_contents('debug.log', "写入失败: " . ($error['message'] ?? '未知错误') . "\n", FILE_APPEND);
            echo json_encode(['status' => 'error', 'message' => '弹幕保存失败: ' . ($error['message'] ?? '未知错误')]);
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
        // 如果文件不存在，返回空数组
        echo json_encode(['status' => 'success', 'messages' => []]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '无效操作']);
}
?>

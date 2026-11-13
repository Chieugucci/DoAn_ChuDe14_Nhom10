<?php
// stream.php - Tối ưu cho XAMPP (Apache)

// 1. Tắt timeout thực thi (tránh bị ngắt kết nối sau 30s)
set_time_limit(0);
ini_set('max_execution_time', '0');

// 2. Tắt bộ đệm và bật tự động đẩy dữ liệu của PHP
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', 'off');
@ini_set('implicit_flush', 1);
ob_implicit_flush(true);

// 3. Header SSE
header('Content-Type: text/event-stream; charset=utf-8');
header('Cache-Control: no-cache, no-transform');
header('Connection: keep-alive');

// 4. Xóa sạch mọi tầng bộ đệm PHP đang bật sẵn trong XAMPP
while (ob_get_level() > 0) {
    ob_end_clean();
}

/**
 * Hàm gửi dữ liệu SSE
 */
function sendEvent($data, $event = 'message')
{
    if (is_array($data)) {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    echo "event: {$event}\n";
    echo "data: {$data}\n\n";

    // Ép đẩy dữ liệu xuống Apache socket ngay lập tức
    flush();
}

// Xử lý luồng gõ chữ...
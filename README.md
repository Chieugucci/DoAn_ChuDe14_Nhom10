# Chủ đề 14: Kỹ thuật Streaming Response (Server-Sent Events - SSE) trong PHP cho Real-time AI Chat

Dự án minh họa giải pháp gửi phản hồi dạng luồng văn bản (real-time streaming text) từ PHP Server xuống trình duyệt client bằng giao thức **Server-Sent Events (SSE)** theo chuẩn W3C.

## 🌟 Tính Năng Nổi Bật
- **Phản hồi thời gian thực (Real-time Typing Effect):** Nhận từng ký tự/chunk văn bản ngay khi PHP xử lý xong.
- **Xử lý Output Buffering nâng cao:** Loại bỏ hiện tượng nghẽn bộ đệm bằng `ob_end_clean()` và `flush()`.
- **Hỗ trợ Nginx & Apache Production Config:** Hướng dẫn tắt `proxy_buffering`.
- **Trực quan hóa Gói Tin (Raw SSE Inspector):** Lắng nghe cấu trúc gói tin `event: message` và `data: {...}`.

## 🚀 Hướng Dẫn Cài Đặt & Chạy Ứng Dụng

### 1. Khởi động nhanh bằng PHP CLI Built-in Server
Không cần cài đặt Apache/Nginx phức tạp, chỉ cần máy tính đã cài **PHP 7.4+** hoặc **PHP 8.x**:

```bash
# Di chuyển vào thư mục dự án
cd DoAn_ChuDe14_Nhom10

# Chạy server nội bộ ở cổng 8000
php -S localhost:8000
```
Sau đó truy cập: `http://localhost:8000`

### 2. Chạy với XAMPP (Apache)
1. Đặt thư mục dự án vào `C:\xampp\htdocs\DoAn_ChuDe14_Nhom10`.
2. Mở **XAMPP Control Panel** và bấm **Start** tại dịch vụ **Apache**.
3. Mở trình duyệt và truy cập: `http://localhost/DoAn_ChuDe14_Nhom10/`.

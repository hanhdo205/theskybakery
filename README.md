# The Sky Bakery

Website WordPress cho The Sky Bakery.

## Yêu cầu hệ thống

- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Apache/Nginx web server

## Cài đặt

1. Clone repository:
   ```bash
   git clone https://github.com/hanhdo205/theskybakery.git
   ```

2. Tạo database MySQL mới

3. Tạo file `wp-config.php` từ file mẫu:
   ```bash
   cp wp-config-sample.php wp-config.php
   ```

4. Cập nhật thông tin database trong `wp-config.php`:
   ```php
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_database_user');
   define('DB_PASSWORD', 'your_database_password');
   define('DB_HOST', 'localhost');
   ```

5. Truy cập website và hoàn tất cài đặt WordPress

## Cấu trúc thư mục

```
├── wp-admin/        # WordPress admin
├── wp-content/      # Themes, plugins, uploads
│   ├── themes/      # Giao diện
│   ├── plugins/     # Plugin
│   └── uploads/     # Media files
├── wp-includes/     # WordPress core
└── wp-config.php    # Cấu hình (không commit)
```

## Lưu ý

- File `wp-config.php` chứa thông tin nhạy cảm và đã được thêm vào `.gitignore`
- Không commit thông tin đăng nhập database lên repository

# ĐỀ TÀI 13: WEBSITE PORTFOLIO / GIỚI THIỆU CÁ NHÂN

**Học phần:** Triển khai và Quản trị Hệ thống Phần mềm
**Sinh viên thực hiện:** Trần Ngọc Huy
**Mã sinh viên:** DTC245180088
**Email:** tranngochuy2509066@gmail.com
**GitHub Repository:** [https://github.com/huytran-01/portfolio-de13.git](https://github.com/huytran-01/portfolio-de13.git)

---

## 1. Giới thiệu & Kiến trúc hệ thống
Hệ thống **Website Portfolio cá nhân (có trang Admin quản lý nội dung)** được đóng gói và triển khai hoàn toàn bằng **Docker Compose** với 9 dịch vụ chạy trên mạng nội bộ cô lập `portfolio_net`:
- **`web` (PHP 8.2 + Apache):** Ứng dụng Website Portfolio cá nhân và trang quản trị Admin.
- **`db` (MySQL 8.0):** Hệ quản trị cơ sở dữ liệu lưu trữ thông tin cá nhân và dự án (có cấu hình `healthcheck`).
- **`phpmyadmin` (phpMyAdmin 5.2):** Công cụ quản trị cơ sở dữ liệu MySQL trực quan qua giao diện web.
- **`nginx` (Nginx Alpine):** Đóng vai trò Reverse Proxy điều phối lưu lượng truy cập vào ứng dụng web, tích hợp Security Headers (Hardening) và mở endpoint `/stub_status`.
- **`nginx-exporter` (Nginx Prometheus Exporter):** Thu thập chỉ số hoạt động từ Nginx chuyển sang định dạng Prometheus.
- **`prometheus` (Prometheus):** Hệ thống thu thập và lưu trữ metrics thời gian thực từ `prometheus` và `nginx-exporter`.
- **`grafana` (Grafana):** Trực quan hóa dữ liệu giám sát (Prometheus) và truy vấn nhật ký tập trung (Loki) qua Dashboard.
- **`loki` (Grafana Loki):** Hệ thống lưu trữ và lập chỉ mục nhật ký (log) tập trung.
- **`alloy` (Grafana Alloy):** Agent thu thập toàn bộ log từ các Docker containers (`/var/run/docker.sock`) và đẩy về Loki với nhãn `{job="portfolio-de13"}`.

---

## 2. Cấu trúc thư mục dự án
```text
portfolio-de13/
├── app/                        # Mã nguồn ứng dụng PHP (public, admin, includes) & Dockerfile
├── database/                   # Script khởi tạo dữ liệu tự động (init.sql, fix-vietnamese.sql)
├── nginx/                      # Cấu hình Nginx Reverse Proxy & Security Headers (default.conf)
├── monitoring/
│   ├── prometheus/             # Cấu hình thu thập metrics (prometheus.yml)
│   ├── loki/                   # Cấu hình Loki server (loki-config.yml)
│   ├── alloy/                  # Cấu hình Grafana Alloy thu thập log Docker (config.alloy)
│   └── grafana/provisioning/   # Cấu hình tự động nạp Datasources cho Grafana
├── logging/                    # Thư mục lưu trữ cấu hình bổ trợ logging
├── screenshots/                # Hình ảnh minh chứng kết quả triển khai hệ thống
├── docker-compose.yml          # File định nghĩa toàn bộ 9 services, networks và volumes
├── .env.example                # File mẫu cấu hình biến môi trường
├── .gitignore                  # Loại trừ file nhạy cảm (.env)
└── README.md                   # Tài liệu hướng dẫn triển khai và vận hành hệ thống
```

---

## 3. Hướng dẫn cài đặt và khởi chạy hệ thống
```bash
# Bước 1: Clone mã nguồn từ GitHub
git clone https://github.com/huytran-01/portfolio-de13.git
cd portfolio-de13

# Bước 2: Tạo file biến môi trường từ file mẫu
cp .env.example .env

# Bước 3: Khởi chạy toàn bộ các dịch vụ bằng Docker Compose
docker compose up -d

# Bước 4: Kiểm tra trạng thái hoạt động của 9 containers
docker compose ps
```

---

## 4. Danh sách các trang và cổng truy cập dịch vụ
- **Website Portfolio (Nginx):** http://localhost:8083
- **Trang quản trị Admin:** http://localhost:8083/admin
- **phpMyAdmin:** http://localhost:8084
- **Grafana:** http://localhost:3001
- **Prometheus:** http://localhost:9090
- **Loki:** http://localhost:3100

---

## 5. Các câu truy vấn Giám sát (PromQL) & Nhật ký (LogQL)
### 5.1. Truy vấn trên Prometheus / Grafana (PromQL)
- Kiểm tra trạng thái hoạt động của các mục tiêu (Targets): `up`
- Kiểm tra kết nối Nginx đang hoạt động: `nginx_connections_active`

### 5.2. Truy vấn Log tập trung trên Grafana Explore (LogQL)
1. **Xem toàn bộ log của hệ thống:**
   `{job="portfolio-de13"}`
2. **Lọc các dòng log truy cập HTTP GET:**
   `{job="portfolio-de13"} |= "GET"`
3. **Thống kê tốc độ sinh log trong mỗi phút:**
   `rate({job="portfolio-de13"}[1m])`

---

## 6. Các biện pháp bảo mật (Hardening) đã áp dụng
1. **Network Isolation (Cô lập mạng):** Tất cả dịch vụ giao tiếp qua mạng nội bộ `portfolio_net` (bridge). Container `db` (MySQL) và `web` (PHP) không mở port trực tiếp ra ngoài máy chủ.
2. **Mật khẩu mạnh & Quản lý biến môi trường:** Thông tin nhạy cảm được lưu trong file `.env` (phân quyền `600`, bị chặn đẩy lên Git qua `.gitignore`), ứng dụng kết nối DB qua tài khoản riêng thay vì tài khoản `root`.
3. **Nginx Security Headers & Ẩn phiên bản:** Bật `server_tokens off;` và cấu hình đầy đủ các header bảo mật (`X-Frame-Options: SAMEORIGIN`, `X-XSS-Protection: 1; mode=block`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`).
4. **Read-Only Volume Mounts (`:ro`):** Các tệp cấu hình gắn vào container đều ở chế độ chỉ đọc (`:ro`) nhằm ngăn chặn việc chỉnh sửa trái phép từ bên trong container.

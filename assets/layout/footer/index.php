<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="./assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./assets/fonts/css/all.min.css" rel="stylesheet">
    
    <style>
        .premium-footer {
            background-color: #0f172a; /* Slate Dark Carbon */
            color: #cbd5e1;
            font-size: 0.9rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .premium-footer h5 {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }

        .footer-link {
            color: #cbd5e1;
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .footer-link:hover {
            color: #60a5fa; /* Glowing Sky Blue */
            transform: translateX(4px);
        }

        .social-circle-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.03);
            color: #cbd5e1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .social-circle-btn:hover {
            background-color: #0d6efd;
            color: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 12px;
            color: #cbd5e1;
        }

        .contact-icon {
            color: #60a5fa;
            margin-top: 3px;
        }
        
        .footer-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* Enforce elegant light contrast on all paragraphs & links inside footer */
        .premium-footer p, 
        .premium-footer span,
        .premium-footer a.text-secondary-emphasis {
            color: #cbd5e1 !important;
        }
        
        .premium-footer a.text-secondary-emphasis:hover,
        .premium-footer a.text-white:hover {
            color: #60a5fa !important;
        }
    </style>
</head>

<body>
    <footer class="premium-footer pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row g-4">
                <!-- Column 1: Brand Info & Description -->
                <div class="col-lg-4 col-md-6">
                    <h5 class="mb-3 text-white fw-extrabold d-flex align-items-center" style="font-size: 1.3rem;">
                        <i class="fa-solid fa-gem me-2 text-primary"></i>UNIQ<span class="text-primary">.</span>
                    </h5>
                    <p class="mb-4 text-secondary-emphasis" style="line-height: 1.6;">
                        UNIQ tự hào là thương hiệu thời trang cao cấp hàng đầu Việt Nam, mang đến cho bạn các dòng sản phẩm hiện đại, chất lượng vượt trội cùng những trải nghiệm mua sắm tuyệt vời nhất.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="#!" class="social-circle-btn" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#!" class="social-circle-btn" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#!" class="social-circle-btn" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#!" class="social-circle-btn" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#!" class="social-circle-btn" aria-label="GitHub"><i class="fab fa-github"></i></a>
                    </div>
                </div>

                <!-- Column 2: Quick Links -->
                <div class="col-lg-3 col-md-6 col-6">
                    <h5 class="mb-3 text-white border-bottom border-secondary border-opacity-10 pb-2">Liên kết nhanh</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2 mb-0">
                        <li><a href="./index.php" class="footer-link d-inline-block"><i class="fa-solid fa-chevron-right me-2 small" style="font-size: 0.7rem;"></i>Trang chủ</a></li>
                        <li><a href="./sanpham.php" class="footer-link d-inline-block"><i class="fa-solid fa-chevron-right me-2 small" style="font-size: 0.7rem;"></i>Sản phẩm</a></li>
                        <li><a href="./voucher.php" class="footer-link d-inline-block text-danger"><i class="fa-solid fa-ticket me-2 small" style="font-size: 0.7rem;"></i>Ưu đãi Voucher</a></li>
                        <li><a href="./giohang.php" class="footer-link d-inline-block"><i class="fa-solid fa-chevron-right me-2 small" style="font-size: 0.7rem;"></i>Giỏ hàng của tôi</a></li>
                    </ul>
                </div>

                <!-- Column 3: Contact & Support info -->
                <div class="col-lg-5 col-md-12">
                    <h5 class="mb-3 text-white border-bottom border-secondary border-opacity-10 pb-2">Thông tin liên hệ</h5>
                    
                    <div class="contact-item">
                        <i class="fa-solid fa-location-dot contact-icon fs-5"></i>
                        <div>
                            <strong class="text-white d-block mb-1">Địa chỉ cửa hàng:</strong>
                            <span>Số 1 Đại Cồ Việt, Bách Khoa, Hai Bà Trưng, Hà Nội</span>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <i class="fa-solid fa-phone contact-icon fs-5"></i>
                        <div>
                            <strong class="text-white d-block mb-1">Hotline CSKH:</strong>
                            <a href="tel:19001000" class="text-decoration-none text-secondary-emphasis hover-text-primary transition-all">1900 1000 (08:00 - 22:00)</a>
                        </div>
                    </div>

                    <div class="contact-item">
                        <i class="fa-solid fa-envelope contact-icon fs-5"></i>
                        <div>
                            <strong class="text-white d-block mb-0.5">Email hỗ trợ:</strong>
                            <a href="mailto:support@uniq.vn" class="text-decoration-none text-secondary-emphasis hover-text-primary transition-all">support@uniq.vn</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Divider and Copyright ribbon -->
            <div class="footer-divider my-4"></div>
            
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                    <p class="mb-0 small text-secondary-emphasis">
                        &copy; 2026 UNIQ Vietnam. Tất cả các quyền được bảo lưu.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0 small text-secondary-emphasis">
                        Phát triển bởi <a href="#!" class="text-white fw-bold text-decoration-none hover-text-primary">Advanced Team</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
</body>

</html>
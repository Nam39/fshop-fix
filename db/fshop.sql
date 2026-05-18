-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th4 25, 2025 lúc 11:35 AM
-- Phiên bản máy phục vụ: 10.4.28-MariaDB
-- Phiên bản PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `_qlbh_`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdonhang`
--
USE fshop;

CREATE TABLE `chitietdonhang` (
  `idchitietdh` int(11) NOT NULL,
  `iddonhang` int(11) NOT NULL,
  `idsanpham` int(11) NOT NULL,
  `soluong` int(11) NOT NULL,
  `gia` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietdonhang`
--

INSERT INTO `chitietdonhang` (`idchitietdh`, `iddonhang`, `idsanpham`, `soluong`, `gia`) VALUES
(1, 3, 1, 10, 1231),
(2, 3, 2, 3, 13),
(3, 4, 1, 1, 1231),
(4, 5, 1, 60, 1231);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhmucsanpham`
--

CREATE TABLE `danhmucsanpham` (
  `id_DanhMuc` int(10) NOT NULL,
  `Ten_DanhMuc` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danhmucsanpham`
--

INSERT INTO `danhmucsanpham` (`id_DanhMuc`, `Ten_DanhMuc`) VALUES
(1, 'Nam'),
(2, 'Nữ'),
(3, 'Trẻ Em');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang`
--

CREATE TABLE `donhang` (
  `idDonHang` int(10) NOT NULL,
  `idKhach` int(10) NOT NULL,
  `ngaydathang` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `trangthai` int(10) NOT NULL,
  `tongtien` int(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `donhang`
--

INSERT INTO `donhang` (`idDonHang`, `idKhach`, `ngaydathang`, `trangthai`, `tongtien`) VALUES
(3, 2, '2025-04-25 07:57:58', 0, 12349),
(4, 2, '2025-04-25 08:09:38', 0, 1231),
(5, 2, '2025-04-25 08:58:55', 0, 73860);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `giohang`
--

CREATE TABLE `giohang` (
  `id` int(10) NOT NULL,
  `iduser` int(10) NOT NULL,
  `idsanpham` int(10) NOT NULL,
  `soluong` int(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `role`
--

CREATE TABLE `role` (
  `roleId` int(10) NOT NULL,
  `Ten` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `role`
--

INSERT INTO `role` (`roleId`, `Ten`) VALUES
(1, 'Admin'),
(2, 'User'),
(3, 'Operater');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sanpham`
--

CREATE TABLE `sanpham` (
  `id` int(10) NOT NULL,
  `Ten` varchar(200) NOT NULL,
  `MoTa` varchar(200) NOT NULL,
  `Gia` float NOT NULL,
  `soluong` int(200) NOT NULL,
  `Anh` varchar(200) NOT NULL,
  `id_DanhMuc` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `sanpham`
--

INSERT INTO `sanpham` (`id`, `Ten`, `MoTa`, `Gia`, `soluong`, `Anh`, `id_DanhMuc`) VALUES
(1, 'thời trang nam', 'San pham dep', 200000, 123, 'cat-item1.jpg', 1),
(2, 'túi xách', 'San pham dep', 1300000, 117, 'cat-item3.jpg', 2),
(3, 'Áo thun nam', 'Áo thun cotton cao cấp', 250000, 50, 'ao-thun-nam.jpg', 1),
(4, 'Quần jean nam', 'Quần jean form slim fit', 450000, 40, 'quan-jean-nam.jpg', 1),
(5, 'Áo khoác nữ', 'Áo khoác thời trang mùa đông', 650000, 30, 'ao-khoac-nu.jpg', 1),
(6, 'Váy nữ', 'Váy thiết kế thanh lịch', 550000, 24, 'vay-nu.jpg', 1),
(7, 'Túi xách da', 'Túi xách nữ da cao cấp', 1200000, 15, 'tui-xach-da.jpg', 2),
(8, 'Balo thời trang', 'Balo chống nước tiện lợi', 700000, 35, 'balo-thoi-trang.jpg', 2),
(9, 'Ví nam', 'Ví da nam nhỏ gọn', 350000, 57, 'vi-nam.jpg', 2),
(10, 'Giày sneaker', 'Giày sneaker trẻ trung', 900000, 16, 'giay-sneaker.jpg', 3),
(11, 'Dép sandal', 'Dép sandal đi biển', 280000, 42, 'dep-sandal.jpg', 3),
(12, 'Mũ lưỡi trai', 'Mũ thời trang unisex', 180000, 70, 'mu-luoi-trai.jpg', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `taikhoan`
--

CREATE TABLE `taikhoan` (
  `idtk` int(10) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `roleId` int(10) NOT NULL,
  `trangthai` int(10) NOT NULL DEFAULT 1,
  `thoigiantao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `taikhoan`
--

INSERT INTO `taikhoan` (`idtk`, `username`, `password`, `roleId`, `trangthai`, `thoigiantao`) VALUES
(1, 'A', '12', 1, 1, '2025-03-27 05:32:27'),
(2, 'B', '123', 2, 1, '2025-03-27 05:32:27'),
(3, 'C', '123', 3, 1, '2025-03-27 05:32:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `iduser` int(10) NOT NULL,
  `idtk` int(10) NOT NULL,
  `Ten_user` varchar(200) NOT NULL,
  `Anh_user` varchar(200) NOT NULL,
  `sdt` int(20) NOT NULL,
  `email` varchar(200) NOT NULL,
  `diachi` varchar(200) NOT NULL,
  `ngaysinh` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`iduser`, `idtk`, `Ten_user`, `Anh_user`, `sdt`, `email`, `diachi`, `ngaysinh`) VALUES
(1, 1, 'Nguyễn Văn A', 'giay.jpg', 98765432, 'admin@gmail.com', 'Hà Nội', '2015-03-03'),
(2, 2, 'Nguyễn Văn B', 'ely.jpg', 98765432, 'b@gmail.com', 'Ha Noi', '2015-03-03');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD PRIMARY KEY (`idchitietdh`),
  ADD KEY `iddonhang` (`iddonhang`),
  ADD KEY `idsanpham` (`idsanpham`);

--
-- Chỉ mục cho bảng `danhmucsanpham`
--
ALTER TABLE `danhmucsanpham`
  ADD PRIMARY KEY (`id_DanhMuc`);

--
-- Chỉ mục cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`idDonHang`),
  ADD KEY `idKhach` (`idKhach`);

--
-- Chỉ mục cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD PRIMARY KEY (`id`),
  ADD KEY `iduser` (`iduser`),
  ADD KEY `idsanpham` (`idsanpham`);

--
-- Chỉ mục cho bảng `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`roleId`);

--
-- Chỉ mục cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_DanhMuc` (`id_DanhMuc`);

--
-- Chỉ mục cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD PRIMARY KEY (`idtk`),
  ADD KEY `roleId` (`roleId`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`iduser`),
  ADD KEY `idtk` (`idtk`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  MODIFY `idchitietdh` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `danhmucsanpham`
--
ALTER TABLE `danhmucsanpham`
  MODIFY `id_DanhMuc` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `donhang`
--
ALTER TABLE `donhang`
  MODIFY `idDonHang` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `giohang`
--
ALTER TABLE `giohang`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT cho bảng `role`
--
ALTER TABLE `role`
  MODIFY `roleId` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  MODIFY `idtk` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `iduser` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD CONSTRAINT `chitietdonhang_ibfk_1` FOREIGN KEY (`iddonhang`) REFERENCES `donhang` (`idDonHang`),
  ADD CONSTRAINT `chitietdonhang_ibfk_2` FOREIGN KEY (`idsanpham`) REFERENCES `sanpham` (`id`);

--
-- Các ràng buộc cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD CONSTRAINT `donhang_ibfk_1` FOREIGN KEY (`idKhach`) REFERENCES `users` (`iduser`);

--
-- Các ràng buộc cho bảng `giohang`
--
ALTER TABLE `giohang`
  ADD CONSTRAINT `giohang_ibfk_2` FOREIGN KEY (`idsanpham`) REFERENCES `sanpham` (`id`),
  ADD CONSTRAINT `giohang_ibfk_3` FOREIGN KEY (`iduser`) REFERENCES `users` (`iduser`);

--
-- Các ràng buộc cho bảng `sanpham`
--
ALTER TABLE `sanpham`
  ADD CONSTRAINT `sanpham_ibfk_1` FOREIGN KEY (`id_DanhMuc`) REFERENCES `danhmucsanpham` (`id_DanhMuc`);

--
-- Các ràng buộc cho bảng `taikhoan`
--
ALTER TABLE `taikhoan`
  ADD CONSTRAINT `taikhoan_ibfk_1` FOREIGN KEY (`roleId`) REFERENCES `role` (`roleId`);

--
-- Các ràng buộc cho bảng `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`idtk`) REFERENCES `taikhoan` (`idtk`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- Bảng loại món ăn
CREATE TABLE Loaimonan (
  Maloai INT AUTO_INCREMENT PRIMARY KEY,
  Tenloai VARCHAR(100) NOT NULL
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Bảng khách hàng + admin + nhân viên (dùng chung)
CREATE TABLE Users (
  UID INT AUTO_INCREMENT PRIMARY KEY,
  Hoten VARCHAR(100) NOT NULL,
  Taikhoan VARCHAR(50) UNIQUE NOT NULL,
  Matkhau VARCHAR(100) NOT NULL,          -- NÊN lưu password_hash, không nên lưu plain text
  Email VARCHAR(100) UNIQUE,
  DienthoaiKH VARCHAR(15),
  DiachiKH VARCHAR(255),
  Ngaysinh DATE,
  Role ENUM('khach','nhanvien','admin')   -- phân quyền
      NOT NULL
      DEFAULT 'khach'
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Bảng món ăn
CREATE TABLE Monan (
  Mamon INT AUTO_INCREMENT PRIMARY KEY,
  Tenmon VARCHAR(100) NOT NULL,
  Giaban DECIMAL(10,2) NOT NULL,
  Giagoc DECIMAL(10,2) NOT NULL,
  Noidung VARCHAR(500),
  Anh VARCHAR(255),
  Maloai INT,
  FOREIGN KEY (Maloai) REFERENCES Loaimonan(Maloai)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Bảng đơn hàng
CREATE TABLE Donhang (
  MaDH INT AUTO_INCREMENT PRIMARY KEY,
  TinhtrangDH VARCHAR(50)
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci
    NOT NULL
    DEFAULT 'Đang xử lý',
  Ngaydat DATETIME DEFAULT NOW(),
  Ngaygiao DATETIME NULL,
  UID INT,
  CONSTRAINT fk_donhang_Users
    FOREIGN KEY (UID) REFERENCES Users(UID)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- Bảng chi tiết đơn hàng
CREATE TABLE Chitietdonhang (
  MaCTDH INT AUTO_INCREMENT PRIMARY KEY,
  MaDH INT,
  Mamon INT,
  Soluong INT NOT NULL,
  Dongia DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (MaDH) REFERENCES Donhang(MaDH),
  FOREIGN KEY (Mamon) REFERENCES Monan(Mamon)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;



-- Bảng giỏ hàng
CREATE TABLE Giohang (
  MaGH INT AUTO_INCREMENT PRIMARY KEY,
  UID INT NOT NULL,
  Mamon INT NOT NULL,
  Soluong INT NOT NULL DEFAULT 1,
  Ngaythem DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_giohang_users
    FOREIGN KEY (UID) REFERENCES Users(UID)
    ON DELETE CASCADE,
  CONSTRAINT fk_giohang_monan
    FOREIGN KEY (Mamon) REFERENCES Monan(Mamon)
    ON DELETE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;




-- Bảng bình luận
CREATE TABLE Binhluan (
  MaBL INT AUTO_INCREMENT PRIMARY KEY,
  UID INT,
  Mamon INT,
  Noidung VARCHAR(1000) NOT NULL,
  Ngaytao DATETIME NOT NULL,
  FOREIGN KEY (UID) REFERENCES Users(UID),
  FOREIGN KEY (Mamon) REFERENCES Monan(Mamon)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;





/* DỮ LIỆU MẪU */

INSERT INTO Loaimonan (Tenloai) VALUES
('Cơm'), ('Phở'), ('Đồ uống'), ('Bánh ngọt'), ('Mì'), ('Gà rán');

INSERT INTO `Monan` (`Mamon`, `Tenmon`, `Giaban`, `Giagoc`, `Noidung`, `Anh`, `Maloai`) VALUES
(1, 'Cơm gà xối mỡ', '40000.00', '45000.00', 'Cơm chiên giòn với gà xối mỡ thơm ngon', 'assets/img/comgaxoimo.jpg', 1),
(2, 'Phở bò tái', '40000.00', '40000.00', 'Phở truyền thống với thịt bò tái mềm', 'assets/img/phobo.jpg', 2),
(3, 'Trà sữa trân châu', '30000.00', '30000.00', 'Trà sữa vị truyền thống, topping trân châu đen', 'assets/img/trasua.webp', 3),
(4, 'Bánh flan caramel', '20000.00', '20000.00', 'Bánh flan mềm mịn, sốt caramel thơm ngon', 'assets/img/banhflan.jpg', 4),
(5, 'Mì xào hải sản', '55000.00', '55000.00', 'Mì xào với tôm, mực, rau củ tươi ngon', 'assets/img/mixaohaisan.jpg', 5),
(6, 'Gà rán giòn', '35000.00', '35000.00', 'Miếng gà chiên giòn rụm đậm vị', 'assets/img/garan.jpg', 6),
(7, 'Nước cam tươi', '25000.00', '25000.00', 'Nước cam ép nguyên chất, không đường', 'assets/img/nuoccam.webp', 3),
(8, 'Matcha đá xay', '35000.00', '35000.00', 'Matcha đá xay', 'assets/img/matchadaxay.webp', 3),
(9, 'Nước ép ổi', '25000.00', '25000.00', 'Nước ổi ép nguyên chất, không đường', 'assets/img/nuocoi.jpg', 3),
(10, 'Cơm chiên dương châu', '50000.00', '50000.00', 'Cơm chiên với xúc xích, trứng và rau củ', 'assets/img/comchien.jpg', 1),
(13, 'Matcha đá xay', '35000.00', '40000.00', 'Thơm, béo', 'assets/img/1764645153_1764642838_matchadaxay.jpg', 3),
(14, 'Phở gà', '45000.00', '50000.00', 'Gà ta, nước ngọt thanh, có thể bạn sẽ thích', 'assets/img/1764645891_phoga.jpg', 2),
(16, 'Nuôi xào ', '45000.00', '50000.00', 'Ngon', 'assets/img/1764646387_nuoixao.jpg', 1),
(17, 'Mì tương đen', '55000.00', '65000.00', 'Mlem', 'assets/img/1764646432_mituongden.jpg', 5),
(18, 'Spagetti', '60000.00', '65000.00', 'Món tui thích nên tui bán ', 'assets/img/1764646497_spagetti.jpg', 5),
(19, 'Gà sốt béo', '75000.00', '85000.00', 'Ngon lắm ăn đi ', 'assets/img/1764646827_gasotbeo.jpg', 6),
(20, 'Chanh dây', '20000.00', '25000.00', 'Chua uống đau bụng ráng chịu ', 'assets/img/1764646882_chanhday.jpg', 3),
(21, 'Gà sốt kem', '85000.00', '95000.00', 'Béo ăn mập ', 'assets/img/1764647062_gasotkem.jpg', 6),
(22, 'Nước ép xoài chanh leo', '25000.00', '30000.00', 'Trending ', 'assets/img/1764647327_xoaichanhleo.jpg', 3),
(23, 'Pizza hải sản', '120000.00', '130000.00', 'No bể bụng', 'assets/img/1764647370_pizza.jpg', 6),
(24, 'Nước chanh', '20000.00', '25000.00', 'Vitamin C', 'assets/img/1764647397_nuocchanh.jpg', 3),
(25, 'Cơm gà ta đùi góc 4', '65000.00', '75000.00', 'Ăn không dai trả tiền lại ', 'assets/img/1764647634_comgata.jpg', 1),
(26, 'Cơm tấm sà bì chưởng', '45000.00', '55000.00', 'Ẩm thực anh em ', 'assets/img/1764647675_comtam.jpg', 1),
(27, 'Sinh tố xoài', '30000.00', '35000.00', 'Healthy', 'assets/img/1764647709_sinhtoxoai.jpg', 3),
(28, 'Mỳ quảng', '35000.00', '45000.00', 'Đặc sản Đà Nẵng', 'assets/img/1764648227_myquang.jpg', 5),
(29, 'Bánh ướt nóng', '25000.00', '30000.00', 'Ngon', 'assets/img/1764648767_banhuotnong.jpg', 2),
(30, 'Bánh xèo', '30000.00', '35000.00', '6 lá', 'assets/img/1764648824_banhxeo.jpg', 1),
(31, 'Bún bò Huế', '40000.00', '45000.00', 'Ngon', 'assets/img/1764648852_bunbohue.webp', 2),
(32, 'Tiramisu', '45000.00', '50000.00', 'Su béoooo', 'assets/img/1764648931_tiramisu.jpg', 4);
-- KHÁCH HÀNG THƯỜNG (role = 'khach')
INSERT INTO Users (Hoten, Taikhoan, Matkhau, Email, DienthoaiKH, DiachiKH, Ngaysinh, Role) VALUES
('Nguyễn Văn A', 'nguyenvana', '123456', 'vana@gmail.com', '0901234567', '123 Lê Lợi, Q1, TP.HCM', '1999-05-15', 'khach'),
('Trần Thị B', 'tranthib', 'abcdef', 'thib@gmail.com', '0912345678', '45 Nguyễn Huệ, TP.HCM', '2000-07-20', 'khach'),
('Lê Văn C', 'levanc', '654321', 'levanc@gmail.com', '0934567890', '22 Hai Bà Trưng, Hà Nội', '1998-12-10', 'khach'),
('Phạm Thị D', 'phamthid', 'dpassword', 'thid@gmail.com', '0923456789', '78 Phan Chu Trinh, Đà Nẵng', '2002-03-05', 'khach');

-- CHUYỂN DỮ LIỆU TỪ BẢNG ADMIN CŨ SANG (role = 'admin' / 'nhanvien')
INSERT INTO Users (Hoten, Taikhoan, Matkhau, Email, DienthoaiKH, DiachiKH, Ngaysinh, Role) VALUES
('Nguyễn Quản Lý', 'admin1', 'admin123', NULL, NULL, NULL, NULL, 'admin'),
('Lê Nhân Viên', 'admin2', 'nhanvien01', NULL, NULL, NULL, NULL, 'nhanvien');

INSERT INTO Donhang (TinhtrangDH, Ngaydat, Ngaygiao, UID) VALUES
('Đang xử lý', '2025-11-01 10:30:00', NULL, 1),
('Đã giao', '2025-11-02 12:45:00', '2025-11-03 14:00:00', 2),
('Đang giao', '2025-11-05 09:15:00', NULL, 3),
('Đã hủy', '2025-11-06 18:00:00', NULL, 4);

INSERT INTO Chitietdonhang (MaDH, Mamon, Soluong, Dongia) VALUES
(1, 1, 2, 45000),
(1, 3, 1, 30000),
(2, 5, 1, 55000),
(2, 7, 2, 25000),
(3, 6, 3, 35000),
(4, 2, 1, 40000);



-- DỮ LIỆU MẪU GIỎ HÀNG

INSERT INTO Giohang (UID, Mamon, Soluong) VALUES
-- User 1: thích cơm gà + trà sữa
(1, 1, 2),   -- 2 phần Cơm gà xối mỡ
(1, 3, 1),   -- 1 ly Trà sữa trân châu

-- User 2: đặt mì xào + nước cam
(2, 5, 1),   -- 1 phần Mì xào hải sản
(2, 7, 2),   -- 2 ly Nước cam tươi

-- User 3: mê gà rán
(3, 6, 3),   -- 3 miếng Gà rán giòn

-- User 4: ăn phở + bánh flan tráng miệng
(4, 2, 1),   -- 1 tô Phở bò tái
(4, 4, 2);   -- 2 bánh flan caramel





INSERT INTO Binhluan (UID, Mamon, Noidung, Ngaytao) VALUES
(1, 1, 'Món này ăn rất ngon, gà giòn và không bị khô.', '2025-11-02 14:20:00'),
(2, 3, 'Trà sữa ngọt vừa, trân châu dẻo ngon.', '2025-11-03 16:00:00'),
(3, 5, 'Mì xào nhiều hải sản, rất chất lượng!', '2025-11-05 09:30:00'),
(4, 6, 'Gà hơi mặn, nhưng vẫn ngon.', '2025-11-06 19:00:00'),
(2, 4, 'Bánh flan mịn, caramel thơm.', '2025-11-07 10:10:00');

-- ============================================
-- ไฟล์: sriracha_travel_db.sql (ฉบับแก้ไข V3 - พร้อมใช้งาน)
-- ============================================

CREATE DATABASE IF NOT EXISTS sriracha_travel_db;
USE sriracha_travel_db;

-- 1. ตาราง Places
DROP TABLE IF EXISTS places;
CREATE TABLE places (
  id int(11) NOT NULL AUTO_INCREMENT,
  name varchar(255) NOT NULL,
  detail text DEFAULT NULL,
  image varchar(255) DEFAULT NULL,
  latitude decimal(10,8) DEFAULT NULL,
  longitude decimal(11,8) DEFAULT NULL,
  created_at timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. ตาราง Admins
DROP TABLE IF EXISTS admins;
CREATE TABLE admins (
  id int(11) NOT NULL AUTO_INCREMENT,
  username varchar(50) NOT NULL,
  password varchar(255) NOT NULL,
  name varchar(100) NOT NULL,
  created_at timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 3. ข้อมูลเริ่มต้น
-- Admin User: admin / Password: 1234
INSERT INTO admins (username, password, name) VALUES
('admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Administrator');

-- Places data
INSERT INTO places (name, detail, latitude, longitude, image) VALUES
('เกาะลอย ศรีราชา', 'เกาะกลางทะเลที่มีวัดจีนสวยงาม เชื่อมกับฝั่งด้วยสะพาน มีมุมชมวิวทะเลสวยมาก เหมาะสำหรับถ่ายรูป และสักการะสิ่งศักดิ์สิทธิ์ เป็นจุดแลนด์มาร์คสำคัญของศรีราชา', 13.17360000, 100.93060000, 'koh_loy.jpg'),
('โรบินสัน ศรีราชา', 'ห้างสรรพสินค้าใหญ่ใจกลางเมืองศรีราชา มีร้านอาหาร ร้านกาแฟ และโรงหนังครบครัน แหล่งรวมไลฟ์สไตล์ของชาวศรีราชา', 13.16850000, 100.93120000, 'robinson.jpg'),
('ตลาดเก่าศรีราชา', 'ตลาดอาหารทะเลสดใหม่ ขายของฝากพื้นเมือง บรรยากาศแบบดั้งเดิม สัมผัสวิถีชีวิตชาวเล และเลือกซื้ออาหารทะเลสดๆ ราคาถูก', 13.16500000, 100.92800000, 'old_market.jpg'),
('สวนสาธารณะศรีราชา', 'สวนสาธารณะริมทะเลเกาะลอย เหมาะสำหรับพักผ่อน วิ่งออกกำลังกาย ชมพระอาทิตย์ตกในยามเย็น บรรยากาศร่มรื่น สดชื่น', 13.17200000, 100.93500000, 'park.jpg'),
('Central Si Racha', 'ศูนย์การค้าสไตล์ Semi-Outdoor แห่งใหม่ที่ใหญ่ที่สุดในศรีราชา ออกแบบภายใต้แนวคิด The Innovation Oasis มีร้านค้าแบรนด์ดัง ร้านอาหาร และจุดถ่ายรูปสวยๆ เพียบ เป็น Pet Friendly Mall พาสัตว์เลี้ยงเดินเที่ยวได้', 13.16550000, 100.93880000, 'Central.jpg'),
('J-Park Sriracha Nihonmura', 'คอมมูนิตี้มอลล์ที่จำลองบรรยากาศหมู่บ้านญี่ปุ่นในสม้ยเอโดะมาไว้ที่ศรีราชา สวยงามร่มรื่นด้วยสวนสไตล์ญี่ปุ่น มีร้านอาหารญี่ปุ่น ขนมหวาน และซูเปอร์มาร์เก็ตสินค้านำเข้า', 13.15900000, 100.93200000, 'jpark.jpg');
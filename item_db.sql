-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2026 at 09:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `item_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `is_active`, `created_at`) VALUES
(1, 'ของที่ระลึก', 1, '2026-09-18 15:30:54'),
(2, 'หนังสือ', 1, '2026-09-18 15:30:54'),
(3, 'โปสเตอร์', 1, '2026-09-18 15:30:54'),
(4, 'สมุด', 1, '2026-10-06 10:24:11');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`) VALUES
(2, 'กองยุทธศาสตร์และแผนงาน'),
(3, 'กอญ.'),
(6, 'กพม.');

-- --------------------------------------------------------

--
-- Table structure for table `employment_types`
--

CREATE TABLE `employment_types` (
  `employment_type_id` int(11) NOT NULL,
  `type_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employment_types`
--

INSERT INTO `employment_types` (`employment_type_id`, `type_name`) VALUES
(1, 'ข้าราชการ'),
(2, 'พนักงานราชการ'),
(3, 'พนักงานจ้างเหมา');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `transaction_id` int(11) NOT NULL,
  `item_id` varchar(20) NOT NULL,
  `category_name` varchar(255) DEFAULT NULL,
  `transaction_type` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL DEFAULT 0,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `reference_id` varchar(50) DEFAULT NULL,
  `remark` varchar(200) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`transaction_id`, `item_id`, `category_name`, `transaction_type`, `quantity`, `previous_stock`, `current_stock`, `reference_id`, `remark`, `created_at`, `created_by`) VALUES
(2, 'ITM1789724864', NULL, 'IN', 20, 0, 0, NULL, 'ปรับแก้โดย', '2026-09-18 16:47:44', 4),
(3, 'ITM1789725629', NULL, 'IN', 100, 0, 0, NULL, 'ปรับแก้โดย', '2026-09-18 17:00:29', 4),
(5, 'ITM1789725782', NULL, 'IN', 20, 0, 0, NULL, 'ปรับแก้โดย', '2026-09-18 17:03:02', 4),
(6, 'ITM1789725807', NULL, 'IN', 20, 0, 0, NULL, 'ปรับแก้โดย', '2026-09-18 17:03:27', 4),
(50, 'ITM1790142845', 'ของที่ระลึก', 'นำ', 0, 0, 0, NULL, 'ลงทะเบียนเพิ่มสิ่งของใหม่ในระบบ', '2026-09-23 12:54:05', 1),
(51, 'ITM1790142860', 'ของที่ระลึก', 'สร้างสิ่งข', 50, 0, 50, NULL, 'รับยอดยกมาเริ่มต้น', '2026-09-23 12:54:20', 1),
(52, 'ITM1790142892', 'หนังสือ', 'CREATE', 0, 0, 0, NULL, 'ลงทะเบียนเพิ่มสิ่งของใหม่ในระบบ', '2026-09-23 12:54:52', 1),
(53, 'ITM1790142898', '-', 'CREATE', 0, 0, 0, NULL, 'ลงทะเบียนเพิ่มสิ่งของใหม่ในระบบ', '2026-09-23 12:54:58', 1),
(54, 'ITM1790142898', '-', 'DELETE', 0, 0, 0, NULL, 'ลบสิ่งของโดย admin (รายการ:  - ประเภท: -)', '2026-09-23 13:10:20', 1),
(55, 'ITM1790143967', 'หนังสือ', 'CREATE', 0, 0, 0, NULL, 'เพิ่มสิ่งของใหม่โดย admin (รายการ: ปผปผปผปผ - ประเภท: หนังสือ)', '2026-09-23 13:12:47', 1),
(56, 'ITM1790143967', 'หนังสือ', 'DELETE', 0, 0, 0, NULL, 'ลบสิ่งของชื่อ: ปผปผปผปผ ประเภท: หนังสือ โดย admin', '2026-09-23 13:15:20', 1),
(57, 'ITM1790144629', 'ของที่ระลึก', 'CREATE', 55, 0, 55, NULL, 'เพิ่มสิ่งของใหม่โดย admin (รายการ: printer_ห้องศูนย์ภาค_อ4ชั้น2 - ประเภท: ของที่ระลึก)', '2026-09-23 13:23:49', 1),
(58, 'ITM1790144629', 'ของที่ระลึก', 'DELETE', -55, 55, 0, NULL, 'ลบสิ่งของ', '2026-09-23 13:23:54', 1),
(59, 'ITM1790155247', 'ของที่ระลึก', 'CREATE', 80, 0, 80, NULL, 'เพิ่มสิ่งของใหม่โดย admin (รายการ: กกกกกกกกกกกก - ประเภท: ของที่ระลึก)', '2026-09-23 16:20:47', 1),
(60, 'ITM1790155247', 'ของที่ระลึก', 'DELETE', -80, 80, 0, NULL, 'ลบสิ่งของ', '2026-09-23 16:20:51', 1),
(61, 'ITM1790216560', 'ของที่ระลึก', 'CREATE', 4, 0, 4, NULL, 'เพิ่มสิ่งของใหม่โดย admin (รายการ: qwqwqwqwqw - ประเภท: ของที่ระลึก)', '2026-09-24 09:22:40', 1),
(62, 'ITM1790217455', 'ของที่ระลึก', 'CREATE', 0, 0, 0, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-09-24 09:37:35', 1),
(63, 'ITM1790217455', 'ของที่ระลึก', 'DELETE', 0, 0, 0, NULL, 'ลบสิ่งของ: โดย admin', '2026-09-24 09:39:57', 1),
(64, 'ITM1790219718', 'ของที่ระลึก', 'CREATE', 0, 0, 0, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-09-24 10:15:18', 1),
(65, 'ITM1790219718', 'ของที่ระลึก', 'IN', 8, 0, 8, NULL, 'เพิ่มสต็อก: พะพะพะ (ประเภท: ของที่ระลึก) โดย admin', '2026-09-24 10:35:31', 1),
(66, 'ITM1790219718', 'ของที่ระลึก', 'IN', 3, 8, 11, NULL, 'เพิ่มสต็อก: พะพะพะ (ประเภท: ของที่ระลึก) โดย admin', '2026-09-24 10:35:45', 1),
(67, 'ITM1790219718', 'ของที่ระลึก', 'OUT', -1, 11, 10, NULL, 'ลดสต็อก: พะพะพะ (ประเภท: ของที่ระลึก) โดย admin', '2026-09-24 10:37:51', 1),
(68, 'ITM1789724864', 'ของที่ระลึก', 'IN', 1, 20, 21, NULL, 'เพิ่มสต็อก: โดย admin', '2026-09-24 10:45:01', 1),
(69, 'ITM1789724864', 'ของที่ระลึก', 'OUT', -1, 21, 20, NULL, 'ลดสต็อก: โดย admin', '2026-09-24 10:54:31', 1),
(70, 'ITM1789724864', 'ของที่ระลึก', 'OUT', -1, 20, 19, NULL, 'ลดสต็อก: โดย admin', '2026-09-24 11:00:54', 1),
(71, 'ITM1790219718', 'ของที่ระลึก', 'DELETE', -10, 10, 0, NULL, 'ลบสิ่งของ: โดย admin', '2026-09-24 11:01:54', 1),
(72, 'ITM1790216560', 'ของที่ระลึก', 'IN', 20, 4, 24, NULL, 'เพิ่มสต็อก: โดย admin', '2026-09-24 11:23:52', 1),
(73, 'ITM1790229162', 'โปสเตอร์', 'CREATE', 20, 0, 20, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-09-24 12:52:42', 1),
(74, 'ITM1790229162', 'หนังสือ', 'EDIT', 0, 20, 20, NULL, 'แก้ไขข้อมูลสิ่งของ: maps โดย admin', '2026-09-24 12:57:14', 1),
(75, 'ITM1790235043', 'หนังสือ', 'CREATE', 50, 0, 50, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-09-24 14:30:43', 1),
(76, 'ITM1790235043', 'หนังสือ', 'OUT', -10, 50, 40, NULL, 'ลดสต็อก: โดย admin', '2026-09-24 14:31:08', 1),
(77, 'ITM1790235043', 'หนังสือ', 'EDIT', 0, 40, 40, NULL, 'แก้ไขข้อมูลสิ่งของ: หนังสือ คำศัพท์ ปส. โดย admin', '2026-09-24 14:31:21', 1),
(78, 'ITM1790235043', 'หนังสือ', 'IN', 5, 40, 45, NULL, 'เพิ่มสต็อก: โดย admin', '2026-09-24 14:32:16', 1),
(79, 'ITM1790142892', 'หนังสือ', 'IN', 1, 0, 1, NULL, 'เพิ่มสต็อก: โดย admin', '2026-09-24 14:53:15', 1),
(80, 'ITM1790236492', 'ของที่ระลึก', 'CREATE', 1, 0, 1, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-09-24 14:54:52', 1),
(81, 'ITM1790236565', 'ของที่ระลึก', 'CREATE', 2, 0, 2, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-09-24 14:56:05', 1),
(82, 'ITM1790216560', 'ของที่ระลึก', 'EDIT', 0, 24, 24, NULL, 'แก้ไขข้อมูลสิ่งของ: qwqwqwqwqw โดย admin', '2026-09-24 15:06:58', 1),
(83, 'ITM1790236492', 'ของที่ระลึก', 'EDIT', 0, 1, 1, NULL, 'แก้ไขข้อมูลสิ่งของ: ทุงทุงทุงซาฮูร์ โดย admin', '2026-09-24 15:07:53', 1),
(84, 'ITM1790236565', 'ของที่ระลึก', 'OUT', -1, 2, 1, NULL, 'ลดสต็อก: โดย admin', '2026-09-24 17:48:48', 1),
(85, 'ITM1790235043', 'หนังสือ', 'IN', 20, 45, 65, NULL, 'เพิ่มสต็อก: โดย admin', '2026-09-24 17:49:08', 1),
(86, 'ITM1790247359', 'โปสเตอร์', 'CREATE', 34, 0, 34, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-09-24 17:55:59', 1),
(87, 'ITM1790247359', 'โปสเตอร์', 'EDIT', 0, 34, 34, NULL, 'แก้ไขข้อมูลสิ่งของ: โปสเตอร์ 2 โดย admin', '2026-09-24 17:57:24', 1),
(88, 'ITM1790247359', 'โปสเตอร์', 'OUT', -10, 34, 24, NULL, 'ลดสต็อก: โดย admin', '2026-09-24 18:03:25', 1),
(89, 'ITM1790247359', 'โปสเตอร์', 'IN', 100, 24, 124, NULL, 'เพิ่มสต็อก: โดย admin', '2026-09-24 18:03:58', 1),
(90, 'ITM1790247359', 'โปสเตอร์', 'OUT', -50, 124, 74, NULL, 'ลดสต็อก: โดย admin', '2026-09-24 18:04:23', 1),
(91, 'ITM1790247359', 'โปสเตอร์', 'DELETE', -74, 74, 0, NULL, 'ลบสิ่งของ: โดย admin', '2026-09-24 18:04:49', 1),
(92, 'ITM1790142892', 'หนังสือ', 'IN', 4, 1, 5, NULL, 'เพิ่มสต็อก: โดย admin', '2026-09-25 16:20:40', 1),
(93, 'ITM1790142860', 'ของที่ระลึก', 'EDIT', 0, 50, 50, NULL, 'แก้ไขข้อมูลสิ่งของ: พวงกุญแจ โดย admin', '2026-09-29 15:51:35', 1),
(94, 'ITM1790142860', 'ของที่ระลึก', 'EDIT', 0, 50, 50, NULL, 'แก้ไขข้อมูลสิ่งของ: พวงกุญแจ โดย admin', '2026-09-29 16:47:32', 1),
(95, 'ITM1790235043', 'หนังสือ', 'EDIT', 0, 65, 65, NULL, 'แก้ไขข้อมูลสิ่งของ: หนังสือ คำศัพท์ ปส. โดย admin', '2026-09-29 16:48:56', 1),
(96, 'ITM1790832155', 'หนังสือ', 'เพิ่มสิ่งข', 60, 0, 60, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-10-01 12:22:35', 1),
(97, 'ITM1790832155', 'หนังสือ', 'ลบสิ่งของ', -60, 60, 0, NULL, 'ลบสิ่งของ: โดย admin', '2026-10-01 12:29:04', 1),
(98, 'ITM1790832561', 'ของที่ระลึก', 'สร้างสิ่งข', 8, 0, 8, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-10-01 12:29:21', 1),
(99, 'ITM1789725807', 'หนังสือ', 'แก้ไขสิ่งข', 0, 20, 20, NULL, 'แก้ไขข้อมูลสิ่งของ: หนังสือ การ์ตูน โดย admin', '2026-10-01 12:31:04', 1),
(100, 'ITM1790832561', 'ของที่ระลึก', 'แก้ไขสิ่งของ', 0, 8, 8, NULL, 'แก้ไขข้อมูลสิ่งของ: Port123 โดย admin', '2026-10-01 12:32:21', 1),
(101, 'ITM1790832768', 'หนังสือ', 'สร้างสิ่งของ', 5, 0, 5, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-10-01 12:32:48', 1),
(102, 'ITM1790832561', 'ของที่ระลึก', 'นำเข้าสิ่งของ', 5, 8, 13, NULL, 'เพิ่มสต็อก: โดย admin', '2026-10-01 12:33:11', 1),
(103, 'ITM1790236565', 'ของที่ระลึก', 'ถอนออกสิ่งของ', -1, 1, 0, NULL, 'ลดสต็อก: โดย admin', '2026-10-01 12:33:27', 1),
(104, 'ITM1790236565', 'ของที่ระลึก', 'ลบสิ่งของ', 0, 0, 0, NULL, 'ลบสิ่งของ: โดย admin', '2026-10-01 16:50:48', 1),
(105, 'ITM1790832768', 'หนังสือ', 'ลบสิ่งของ', -5, 5, 0, NULL, 'ลบสิ่งของ: โดย admin', '2026-10-01 17:54:38', 1),
(106, 'ITM1790832561', 'ของที่ระลึก', 'OUT', -5, 13, 8, 'REQ-2610-0001', 'จ่ายออกตามคำขอเบิก REQ-2610-0001', '2026-10-02 14:22:19', 1),
(107, 'ITM1790216560', 'ของที่ระลึก', 'OUT', -4, 24, 20, 'REQ-2610-0001', 'จ่ายออกตามคำขอเบิก REQ-2610-0001', '2026-10-02 14:22:19', 1),
(108, 'ITM1789725629', 'ของที่ระลึก', 'OUT', -1, 100, 99, 'REQ-2610-0001', 'จ่ายออกตามคำขอเบิก REQ-2610-0001', '2026-10-02 14:22:19', 1),
(109, 'ITM1790236492', NULL, 'OUT', -1, 1, 0, 'REQ-2609-DD6E', 'จ่ายออกตามคำขอเบิก REQ-2609-DD6E', '2026-10-02 14:47:28', 1),
(110, 'ITM1789725807', NULL, 'OUT', -5, 20, 15, 'REQ-2609-DD6E', 'จ่ายออกตามคำขอเบิก REQ-2609-DD6E', '2026-10-02 14:47:28', 1),
(111, 'ITM1789724864', NULL, 'OUT', -3, 19, 16, 'REQ-2609-DD6E', 'จ่ายออกตามคำขอเบิก REQ-2609-DD6E', '2026-10-02 14:47:28', 1),
(112, 'ITM1790832561', 'ของที่ระลึก', 'IN', 5, 8, 13, 'REQ-2610-0001', 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0001', '2026-10-02 14:47:49', 1),
(113, 'ITM1790216560', 'ของที่ระลึก', 'IN', 4, 20, 24, 'REQ-2610-0001', 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0001', '2026-10-02 14:47:49', 1),
(114, 'ITM1789725629', 'ของที่ระลึก', 'IN', 1, 99, 100, 'REQ-2610-0001', 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0001', '2026-10-02 14:47:49', 1),
(115, 'ITM1790229162', 'หนังสือ', 'ถอนจากคำร้อง', -5, 20, 15, 'REQ-2610-0002', 'จ่ายออกตามคำขอเบิก REQ-2610-0002', '2026-10-02 18:22:23', 1),
(116, 'ITM1790235043', 'หนังสือ', 'ถอนจากคำร้อง', -5, 65, 60, 'REQ-2610-0002', 'จ่ายออกตามคำขอเบิก REQ-2610-0002', '2026-10-02 18:22:23', 1),
(117, 'ITM1790229162', 'หนังสือ', 'ยกเลิกคำร้อง', 5, 15, 20, 'REQ-2610-0002', 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0002', '2026-10-02 18:27:20', 1),
(118, 'ITM1790235043', 'หนังสือ', 'ยกเลิกคำร้อง', 5, 60, 65, 'REQ-2610-0002', 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0002', '2026-10-02 18:27:20', 1),
(119, 'ITM1790229162', 'หนังสือ', 'ยกเลิกคำร้อง', 5, 20, 25, 'REQ-2610-0002', 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0002', '2026-10-02 18:27:52', 1),
(120, 'ITM1790235043', 'หนังสือ', 'ยกเลิกคำร้อง', 5, 65, 70, 'REQ-2610-0002', 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0002', '2026-10-02 18:27:52', 1),
(121, 'ITM1790236492', NULL, 'ยกเลิกคำร้อง', 1, 0, 1, 'REQ-2609-DD6E', 'คืนสต็อกจากการยกเลิกคำขอ REQ-2609-DD6E', '2026-10-04 20:26:48', 1),
(122, 'ITM1789725807', NULL, 'ยกเลิกคำร้อง', 5, 15, 20, 'REQ-2609-DD6E', 'คืนสต็อกจากการยกเลิกคำขอ REQ-2609-DD6E', '2026-10-04 20:26:48', 1),
(123, 'ITM1789724864', NULL, 'ยกเลิกคำร้อง', 3, 16, 19, 'REQ-2609-DD6E', 'คืนสต็อกจากการยกเลิกคำขอ REQ-2609-DD6E', '2026-10-04 20:26:48', 1),
(124, 'ITM1789725782', 'ของที่ระลึก', 'นำเข้าสิ่งของ', 1, 20, 21, NULL, 'เพิ่มสต็อก: โดย admin', '2026-10-04 20:58:58', 1),
(125, 'ITM1790142845', 'ของที่ระลึก', 'นำเข้าสิ่งของ', 1, 0, 1, NULL, 'เพิ่มสต็อก: โดย buris.s', '2026-10-04 23:29:50', 4),
(126, 'ITM1791135547', 'ของที่ระลึก', 'สร้างสิ่งของ', 55, 0, 55, NULL, 'เพิ่มสิ่งของใหม่โดย buris.s', '2026-10-05 00:39:07', 4),
(127, 'ITM1791135560', 'ของที่ระลึก', 'สร้างสิ่งของ', 4, 0, 4, NULL, 'เพิ่มสิ่งของใหม่โดย buris.s', '2026-10-05 00:39:20', 4),
(128, 'ITM1791135571', 'ของที่ระลึก', 'สร้างสิ่งของ', 7, 0, 7, NULL, 'เพิ่มสิ่งของใหม่โดย buris.s', '2026-10-05 00:39:31', 4),
(129, 'ITM1791135560', 'ของที่ระลึก', 'ลบสิ่งของ', -4, 4, 0, NULL, 'ลบสิ่งของ: โดย buris.s', '2026-10-05 01:16:40', 4),
(130, 'ITM1789725629', 'ของที่ระลึก', 'ถอนจากคำร้อง', -1, 100, 99, 'REQ-2610-0003', 'จ่ายออกตามคำขอเบิก REQ-2610-0003', '2026-10-05 01:26:48', 4),
(131, 'ITM1789725807', 'หนังสือ', 'ถอนจากคำร้อง', -1, 20, 19, 'REQ-2610-0003', 'จ่ายออกตามคำขอเบิก REQ-2610-0003', '2026-10-05 01:26:48', 4),
(132, 'ITM1791135547', 'ของที่ระลึก', 'ถอนจากคำร้อง', -1, 55, 54, 'REQ-2610-0006', 'จ่ายออกตามคำขอเบิก REQ-2610-0006', '2026-10-05 11:23:53', 4),
(133, 'ITM1790236492', 'ของที่ระลึก', 'แก้ไขสิ่งของ', 0, 1, 1, NULL, 'แก้ไขข้อมูลสิ่งของ: ทุงทุงทุงซาฮูร์ โดย admin', '2026-10-05 18:02:36', 1),
(134, 'ITM1790236492', 'ของที่ระลึก', 'ลบสิ่งของ', -1, 1, 0, NULL, 'ลบสิ่งของ: โดย admin', '2026-10-06 07:57:38', 1),
(135, 'ITM1791135547', 'ของที่ระลึก', 'ลบสิ่งของ', -54, 54, 0, NULL, 'ลบสิ่งของ: โดย admin', '2026-10-06 07:59:47', 1),
(136, 'ITM1790142892', 'ของที่ระลึก', 'แก้ไขสิ่งของ', 0, 5, 5, NULL, 'แก้ไขข้อมูลสิ่งของ: กระเป๋าผ้า โดย admin', '2026-10-06 08:08:46', 1),
(137, 'ITM1791135571', 'ของที่ระลึก', 'แก้ไขสิ่งของ', 0, 7, 7, NULL, 'แก้ไขข้อมูลสิ่งของ: ชั้นวาง โดย admin', '2026-10-06 08:20:37', 1),
(138, 'ITM1790216560', 'ของที่ระลึก', 'แก้ไขสิ่งของ', 0, 24, 24, NULL, 'แก้ไขข้อมูลสิ่งของ โดย admin', '2026-10-06 08:22:25', 1),
(139, 'ITM1790142860', 'ของที่ระลึก', 'แก้ไขสิ่งของ', 0, 50, 50, NULL, 'แก้ไขข้อมูลสิ่งของ โดย admin', '2026-10-06 08:22:40', 1),
(140, 'ITM1790142845', 'ของที่ระลึก', 'แก้ไขสิ่งของ', 0, 1, 1, NULL, 'แก้ไขข้อมูลสิ่งของ โดย admin', '2026-10-06 08:23:04', 1),
(141, 'ITM1789725807', 'หนังสือ', 'แก้ไขสิ่งของ', 0, 19, 19, NULL, 'แก้ไขข้อมูลสิ่งของ โดย admin', '2026-10-06 08:24:40', 1),
(142, 'ITM1791252767', 'โปสเตอร์', 'สร้างสิ่งของ', 10, 0, 10, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-10-06 09:12:47', 1),
(143, 'ITM1789724864', 'ของที่ระลึก', 'ถอนจากคำร้อง', -13, 19, 6, 'REQ-2610-0010', 'จ่ายออกตามคำขอเบิก REQ-2610-0010', '2026-10-06 10:11:12', 1),
(144, 'ITM1789725782', 'ของที่ระลึก', 'ถอนจากคำร้อง', -3, 21, 18, 'REQ-2610-0010', 'จ่ายออกตามคำขอเบิก REQ-2610-0010', '2026-10-06 10:11:12', 1),
(145, 'ITM1790229162', 'หนังสือ', 'ถอนจากคำร้อง', -1, 25, 24, 'REQ-2610-0011', 'จ่ายออกตามคำขอเบิก REQ-2610-0011', '2026-10-06 10:14:09', 1),
(146, 'ITM1789724864', 'ของที่ระลึก', 'ถอนจากคำร้อง', -1, 6, 5, 'REQ-2610-0011', 'จ่ายออกตามคำขอเบิก REQ-2610-0011', '2026-10-06 10:14:09', 1),
(147, 'ITM1790229162', 'หนังสือ', 'ยกเลิกคำร้อง', 1, 24, 25, 'REQ-2610-0011', 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0011', '2026-10-06 10:16:28', 1),
(148, 'ITM1789724864', 'ของที่ระลึก', 'ยกเลิกคำร้อง', 1, 5, 6, 'REQ-2610-0011', 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0011', '2026-10-06 10:16:28', 1),
(149, 'ITM1791257169', 'สมุด', 'สร้างสิ่งของ', 5, 0, 5, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-10-06 10:26:09', 1),
(150, 'ITM1791257169', 'สมุด', 'แก้ไขสิ่งของ', 0, 5, 5, NULL, 'แก้ไขข้อมูลสิ่งของ โดย admin', '2026-10-06 10:26:35', 1),
(151, 'ITM1791257169', 'สมุด', 'นำเข้าสิ่งของ', 2, 5, 7, NULL, 'เพิ่มสต็อก: โดย admin', '2026-10-06 10:27:38', 1),
(152, 'ITM1791257169', 'สมุด', 'ถอนออกสิ่งของ', -7, 7, 0, NULL, 'ลดสต็อก: โดย admin', '2026-10-06 10:28:09', 1),
(153, 'ITM1791257169', 'สมุด', 'ลบสิ่งของ', 0, 0, 0, NULL, 'ลบสิ่งของ: โดย admin', '2026-10-06 10:28:49', 1),
(154, 'ITM1789724864', 'ของที่ระลึก', 'ลบสิ่งของ', -6, 6, 0, NULL, 'ลบสิ่งของ: โดย admin', '2026-10-06 10:30:32', 1),
(155, 'ITM1790229162', 'หนังสือ', 'ถอนออกสิ่งของ', -15, 25, 10, NULL, 'ลดสต็อก: โดย admin', '2026-10-06 11:24:57', 1),
(156, 'ITM1791252767', 'โปสเตอร์', 'นำเข้าสิ่งของ', 1, 10, 11, NULL, 'เพิ่มสต็อก: โดย admin', '2026-10-06 11:49:56', 1),
(157, 'ITM1790229162', 'หนังสือ', 'นำเข้าสิ่งของ', 3, 10, 13, NULL, 'เพิ่มสต็อก: โดย admin', '2026-10-06 11:58:03', 1),
(158, 'ITM1790142860', 'ของที่ระลึก', 'ถอนจากคำร้อง', -6, 50, 44, 'REQ-2610-0009', 'จ่ายออกตามคำขอเบิก REQ-2610-0009', '2026-10-06 12:21:29', 1),
(159, 'ITM1790142845', 'ของที่ระลึก', 'ถอนจากคำร้อง', -1, 1, 0, 'REQ-2610-0009', 'จ่ายออกตามคำขอเบิก REQ-2610-0009', '2026-10-06 12:21:29', 1),
(160, 'ITM1790229162', 'หนังสือ', 'ถอนออกสิ่งของ', -13, 13, 0, NULL, 'ลดสต็อก: โดย buris.s', '2026-10-06 13:47:05', 4),
(161, 'ITM1791252767', 'โปสเตอร์', 'ถอนออกสิ่งของ', -11, 11, 0, NULL, 'ลดสต็อก: โดย admin', '2026-10-06 17:10:55', 1),
(162, 'ITM1791252767', 'โปสเตอร์', 'ลบสิ่งของ', 0, 0, 0, NULL, 'ลบสิ่งของ: โดย admin', '2026-10-06 17:11:02', 1),
(163, 'ITM1791282156', 'สมุด', 'สร้างสิ่งของ', 20, 0, 20, NULL, 'เพิ่มสิ่งของใหม่โดย admin', '2026-10-06 17:22:36', 1),
(164, 'ITM1791282156', 'สมุด', 'นำเข้าสิ่งของ', 5, 20, 25, NULL, 'เพิ่มสต็อก: โดย admin', '2026-10-06 17:23:01', 1),
(165, 'ITM1791282156', 'สมุด', 'ถอนออกสิ่งของ', -3, 25, 22, NULL, 'ลดสต็อก: โดย admin', '2026-10-06 17:23:56', 1),
(166, 'ITM1791282156', 'สมุด', 'ถอนจากคำร้อง', -3, 22, 19, 'REQ-2610-0012', 'จ่ายออกตามคำขอเบิก REQ-2610-0012', '2026-10-06 17:26:58', 1),
(167, 'ITM1791282156', 'สมุด', 'ลบสิ่งของ', -19, 19, 0, NULL, 'ลบสิ่งของ: โดย admin', '2026-10-06 17:28:13', 1);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` varchar(20) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `current_stock` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 10,
  `images` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `category_id`, `name`, `description`, `current_stock`, `low_stock_threshold`, `images`, `created_at`) VALUES
('ITM1789725629', 1, 'ดินสอ 2B', '', 99, 10, '[\"ITM1789725629_1_6aad0bbd463c1.jpg\"]', '2026-09-18 17:00:29'),
('ITM1789725782', 1, 'ตุ๊กตา', '', 18, 10, '[\"ITM1789725782_1_6aad0c568e4bd.jpg\"]', '2026-09-18 17:03:02'),
('ITM1789725807', 2, 'สมุดระบายสี', '', 19, 10, '[\"ITM1789725807_edit_1_6ac44dd8b2fcb.jpg\",\"ITM1789725807_edit_2_6ac44dd8b3163.jpg\"]', '2026-09-18 17:03:27'),
('ITM1790142845', 1, 'ร่ม', '', 0, 10, '[\"ITM1790142845_edit_1_6ac44d78227e3.jpg\"]', '2026-09-23 12:54:05'),
('ITM1790142860', 1, 'พวงกุญแจ', 'พวงกุญแจ การตูน น่ารัก', 44, 10, '[\"ITM1790142860_edit_1_6ac44d605df43.jpg\"]', '2026-09-23 12:54:20'),
('ITM1790142892', 1, 'กระเป๋าผ้า', '', 5, 10, '[\"ITM1790142892_edit_1_6ac44a1eadde6.jpg\"]', '2026-09-23 12:54:52'),
('ITM1790216560', 1, 'จอทีวี', '', 24, 10, '[\"ITM1790216560_edit_1_6ab4da22602ad.jpg\",\"ITM1790216560_edit_2_6ab4da226079d.jpg\"]', '2026-09-24 09:22:40'),
('ITM1790229162', 2, 'maps', 'map of  forest', 0, 10, '[\"ITM1790229162_1_6ab4baaae0e05.png\",\"ITM1790229162_2_6ab4baaae16bf.jpg\"]', '2026-09-24 12:52:42'),
('ITM1790235043', 2, 'หนังสือ คำศัพท์ ปส.', 'หนีงสือคำศัพท์', 70, 10, '[\"ITM1790235043_1_6ab4d1a38d150.png\"]', '2026-09-24 14:30:43'),
('ITM1790832561', 1, 'Port123', '', 13, 10, '[\"ITM1790832561_1_6abdefb1f094f.png\"]', '2026-10-01 12:29:21'),
('ITM1791135571', 1, 'ชั้นวาง', '', 7, 10, '[\"ITM1791135571_edit_1_6ac44ce5bd9ea.jpg\"]', '2026-10-05 00:39:31');

-- --------------------------------------------------------

--
-- Table structure for table `monthly_balances`
--

CREATE TABLE `monthly_balances` (
  `balance_id` int(11) NOT NULL,
  `item_id` varchar(20) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `balance_forward` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `request_date` datetime DEFAULT current_timestamp(),
  `use_date` date DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `event_name` varchar(200) DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `user_note` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `admin_comment` text DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`request_id`, `user_id`, `request_date`, `use_date`, `event_type`, `event_name`, `location`, `purpose`, `user_note`, `status`, `admin_comment`, `approved_at`, `approved_by`) VALUES
('REQ-2609-0751', 3, '2026-09-30 18:27:24', '2026-10-08', 'จัดนิทรรศการ', 'วันน้ำท่วม', 'โรงอาหาร', 'บูชายัญ', '', 'ไม่อนุมัติ', '', '2026-10-02 16:14:24', 1),
('REQ-2609-B401', 3, '2026-09-30 18:28:05', '2026-10-08', 'จัดนิทรรศการ', 'วันน้ำท่วม', 'โรงอาหาร', 'บูชายัญ', '', 'ยกเลิก', 'ไม่ให้', '2026-10-02 14:47:07', 1),
('REQ-2609-DD6E', 3, '2026-09-30 17:33:07', '2026-10-02', 'ประชุม/สัมมนา', 'วันเด็ก 2569', 'โรงอาหาร', 'รางวัล', '', 'ยกเลิก', '', '2026-10-02 14:47:28', 1),
('REQ-2610-0001', 3, '2026-10-02 14:06:36', '2026-10-15', 'ประชุม/สัมมนา', 'ใหม่', 'โรงทาน', 'ประกอบพิธีสานฝัน', '', 'ยกเลิก', 'อนุมัติแค่บางส่วน', '2026-10-02 14:22:19', 1),
('REQ-2610-0002', 2, '2026-10-02 18:05:28', '2026-10-14', 'ประชุม/สัมมนา', 'วันน้ำท่วม', 'โรงทาน', 'แจกผู้ลำบาก', 'ของมีจำนวนจำกัด', 'ยกเลิก', 'แผนที่หลงทางตอนน้ำท่วม มีจำนวนจำกัด', '2026-10-02 18:22:23', 1),
('REQ-2610-0003', 5, '2026-10-04 21:14:28', '2026-10-20', 'ประชุม/สัมมนา', 'ปีใหม่', 'ห้องประชุม เดลต้า', 'ของทีระลึกแก่พนักงาน', 'ขอเพิ่มพิเศษ ใส่ไข่', 'อนุมัติ', '', '2026-10-05 01:26:48', 4),
('REQ-2610-0004', 5, '2026-10-05 01:15:07', '2026-09-30', 'โครงการ', 'ทดลองอาบูดาบี', 'ทดลองอาบูดาบี', 'ลองลบรายการฝั่งแอดมิน ในระหว่างที่ส่งคำร้องเสร็จ', '', 'ยกเลิก', 'เนื่องจากว่า ได้ลบของชิ้นนี้ไปจาก คลังแล้ว', '2026-10-05 01:17:33', 4),
('REQ-2610-0005', 5, '2026-10-05 10:37:38', '2026-10-01', 'งาน', 'วันน้ำท่วม', 'ทดลองอาบูดาบี', '1234', '', 'ไม่อนุมัติ', '', '2026-10-05 11:25:25', 4),
('REQ-2610-0006', 5, '2026-10-05 11:23:34', '2026-10-15', 'งาน', 'มิงกาละยา', 'ห้องประชุม เดลต้า', '7894', '', 'อนุมัติ', '', '2026-10-05 11:23:53', 4),
('REQ-2610-0007', 5, '2026-10-05 13:26:22', '2026-10-06', 'งาน', 'หฟหฟห', 'หฟหฟ', 'หฟหฟหฟ', '', 'รออนุมัติ', NULL, NULL, NULL),
('REQ-2610-0008', 5, '2026-10-05 13:26:45', '2026-10-14', 'งาน', 'วันน้ำท่วม', 'หกหก', 'กหกหก', 'กหกหก', 'รออนุมัติ', NULL, NULL, NULL),
('REQ-2610-0009', 5, '2026-10-05 17:49:54', '2026-10-15', 'สัมมนา', 'หหห', 'หหห', 'หหห', 'หห', 'อนุมัติบางส่วน', 'ตัวโมเดล ไม้หมด', '2026-10-06 12:21:29', 1),
('REQ-2610-0010', 5, '2026-10-06 09:58:39', '2026-10-06', 'จัดนิทรรศการ', 'ปีใหม่', 'โรงอาหาร', 'แจกของรางว้ล', '', 'อนุมัติบางส่วน', '', '2026-10-06 10:11:12', 1),
('REQ-2610-0011', 5, '2026-10-06 10:13:59', '2026-10-14', 'งาน', 'วันน้ำท่วม', 'โรงทาน', 'ดกอก', '', 'ยกเลิก', '', '2026-10-06 10:14:09', 1),
('REQ-2610-0012', 5, '2026-10-06 17:26:43', '2026-10-06', 'งาน', 'วันเด็ก 2569', 'โรงทาน', '55555', '', 'อนุมัติ', '', '2026-10-06 17:26:58', 1);

-- --------------------------------------------------------

--
-- Table structure for table `request_items`
--

CREATE TABLE `request_items` (
  `request_item_id` int(11) NOT NULL,
  `request_id` varchar(20) DEFAULT NULL,
  `item_id` varchar(20) DEFAULT NULL,
  `item_name` varchar(200) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `images` text DEFAULT NULL,
  `requested_qty` int(11) NOT NULL,
  `approved_qty` int(11) DEFAULT NULL,
  `item_status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `request_items`
--

INSERT INTO `request_items` (`request_item_id`, `request_id`, `item_id`, `item_name`, `category`, `images`, `requested_qty`, `approved_qty`, `item_status`) VALUES
(1, 'REQ-2609-DD6E', 'ITM1790236492', 'ทุงทุงทุงซาฮูร์', NULL, NULL, 1, 1, 'อนุมัติเต็ม'),
(2, 'REQ-2609-DD6E', 'ITM1789725807', 'หนังสือ การ์ตูน', NULL, NULL, 5, 5, 'อนุมัติเต็ม'),
(3, 'REQ-2609-DD6E', 'ITM1789724864', 'โมเดลรถ', NULL, NULL, 3, 3, 'อนุมัติเต็ม'),
(4, 'REQ-2609-0751', 'ITM1790236565', 'ทาราเรโร่ ทาราร่า', NULL, NULL, 1, 0, 'ไม่อนุมัติ'),
(5, 'REQ-2609-0751', 'ITM1789725782', 'ตุ๊กตา', NULL, NULL, 4, 0, 'ไม่อนุมัติ'),
(6, 'REQ-2609-B401', 'ITM1790236565', 'ทาราเรโร่ ทาราร่า', NULL, NULL, 1, 0, 'ไม่อนุมัติ'),
(7, 'REQ-2609-B401', 'ITM1789725782', 'ตุ๊กตา', NULL, NULL, 4, 0, 'ไม่อนุมัติ'),
(8, 'REQ-2610-0001', 'ITM1790832561', 'Port123', 'ของที่ระลึก', '[\"ITM1790832561_1_6abdefb1f094f.png\"]', 5, 5, 'อนุมัติเต็ม'),
(9, 'REQ-2610-0001', 'ITM1790216560', 'qwqwqwqwqw', 'ของที่ระลึก', '[\"ITM1790216560_edit_1_6ab4da22602ad.jpg\",\"ITM1790216560_edit_2_6ab4da226079d.jpg\"]', 5, 4, 'อนุมัติบางส่วน'),
(10, 'REQ-2610-0001', 'ITM1789725629', 'ดินสอ 2B', 'ของที่ระลึก', '[\"ITM1789725629_1_6aad0bbd463c1.jpg\"]', 1, 1, 'อนุมัติเต็ม'),
(11, 'REQ-2610-0002', 'ITM1790229162', 'maps', 'หนังสือ', '[\"ITM1790229162_1_6ab4baaae0e05.png\",\"ITM1790229162_2_6ab4baaae16bf.jpg\"]', 10, 5, 'อนุมัติบางส่วน'),
(12, 'REQ-2610-0002', 'ITM1790235043', 'หนังสือ คำศัพท์ ปส.', 'หนังสือ', '[\"ITM1790235043_1_6ab4d1a38d150.png\"]', 5, 5, 'อนุมัติเต็ม'),
(13, 'REQ-2610-0003', 'ITM1789725629', 'ดินสอ 2B', 'ของที่ระลึก', '[\"ITM1789725629_1_6aad0bbd463c1.jpg\"]', 1, 1, 'อนุมัติเต็ม'),
(14, 'REQ-2610-0003', 'ITM1789725807', 'หนังสือ การ์ตูน', 'หนังสือ', '[\"ITM1789725807_edit_1_6abdf01871b1e.png\"]', 1, 1, 'อนุมัติเต็ม'),
(15, 'REQ-2610-0004', 'ITM1791135560', 'ดหหก', 'ของที่ระลึก', '[\"ITM1791135560_1_6ac28f48ee676.png\"]', 1, 0, 'ไม่อนุมัติ'),
(16, 'REQ-2610-0005', 'ITM1790832561', 'Port123', 'ของที่ระลึก', '[\"ITM1790832561_1_6abdefb1f094f.png\"]', 1, 0, 'ไม่อนุมัติ'),
(17, 'REQ-2610-0005', 'ITM1789725782', 'ตุ๊กตา', 'ของที่ระลึก', '[\"ITM1789725782_1_6aad0c568e4bd.jpg\"]', 1, 0, 'ไม่อนุมัติ'),
(18, 'REQ-2610-0005', 'ITM1790236492', 'ทุงทุงทุงซาฮูร์', 'ของที่ระลึก', '[\"ITM1790236492_1_6ab4d74c29371.jpg\"]', 1, 0, 'ไม่อนุมัติ'),
(19, 'REQ-2610-0005', 'ITM1790229162', 'maps', 'หนังสือ', '[\"ITM1790229162_1_6ab4baaae0e05.png\",\"ITM1790229162_2_6ab4baaae16bf.jpg\"]', 1, 0, 'ไม่อนุมัติ'),
(20, 'REQ-2610-0006', 'ITM1791135547', 'กหกกหก', 'ของที่ระลึก', '[\"ITM1791135547_1_6ac28f3b8ecae.png\"]', 1, 1, 'อนุมัติเต็ม'),
(21, 'REQ-2610-0007', 'ITM1790142845', 'printer_ห้อง_ปสภ.', 'ของที่ระลึก', '[\"ITM1790142845_1_6ab3697d07f82.png\"]', 1, NULL, NULL),
(22, 'REQ-2610-0008', 'ITM1791135547', 'กหกกหก', 'ของที่ระลึก', '[\"ITM1791135547_1_6ac28f3b8ecae.png\"]', 1, NULL, NULL),
(23, 'REQ-2610-0009', 'ITM1790236492', 'ทุงทุงทุงซาฮูร์', 'ของที่ระลึก', '[\"ITM1790236492_1_6ab4d74c29371.jpg\"]', 11, 0, 'ไม่อนุมัติ'),
(24, 'REQ-2610-0009', 'ITM1790142860', 'พวงกุญแจ', 'ของที่ระลึก', '[\"ITM1790142860_edit_1_6abb7c1790b02.jpg\"]', 9, 6, 'อนุมัติบางส่วน'),
(25, 'REQ-2610-0009', 'ITM1790142845', 'printer_ห้อง_ปสภ.', 'ของที่ระลึก', '[\"ITM1790142845_1_6ab3697d07f82.png\"]', 2, 1, 'อนุมัติบางส่วน'),
(26, 'REQ-2610-0010', 'ITM1789724864', 'โมเดลรถ', 'ของที่ระลึก', '[\"ITM1789724864_1_6aad08c00326d.jpg\"]', 13, 13, 'อนุมัติเต็ม'),
(27, 'REQ-2610-0010', 'ITM1789725629', 'ดินสอ 2B', 'ของที่ระลึก', '[\"ITM1789725629_1_6aad0bbd463c1.jpg\"]', 2, 0, 'ไม่อนุมัติ'),
(28, 'REQ-2610-0010', 'ITM1789725782', 'ตุ๊กตา', 'ของที่ระลึก', '[\"ITM1789725782_1_6aad0c568e4bd.jpg\"]', 3, 3, 'อนุมัติเต็ม'),
(29, 'REQ-2610-0011', 'ITM1790229162', 'maps', 'หนังสือ', '[\"ITM1790229162_1_6ab4baaae0e05.png\",\"ITM1790229162_2_6ab4baaae16bf.jpg\"]', 1, 1, 'อนุมัติเต็ม'),
(30, 'REQ-2610-0011', 'ITM1789724864', 'โมเดลรถ', 'ของที่ระลึก', '[\"ITM1789724864_1_6aad08c00326d.jpg\"]', 1, 1, 'อนุมัติเต็ม'),
(32, 'REQ-2610-0012', 'ITM1791282156', 'สมุดจดบันทึก', 'สมุด', '[\"ITM1791282156_1_6ac4cbec36ed9.jpg\"]', 3, 3, 'อนุมัติเต็ม');

-- --------------------------------------------------------

--
-- Table structure for table `sub_departments`
--

CREATE TABLE `sub_departments` (
  `sub_department_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `sub_department_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sub_departments`
--

INSERT INTO `sub_departments` (`sub_department_id`, `department_id`, `sub_department_name`) VALUES
(1, 2, 'เทคโนโลยีสารสนเทศ'),
(2, 2, 'กลุ่มต่างประเทศ');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `sub_department_id` int(11) DEFAULT NULL,
  `employment_type_id` int(11) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'User'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `department_id`, `sub_department_id`, `employment_type_id`, `phone_number`, `role`) VALUES
(1, 'admin', '$2y$10$Sz.VW6tpB1VygwluJrCPHuOXVD6kiVoBS0op1DAweyckSqjEnTmZa', 'BURIS S.', NULL, NULL, NULL, '081-234-5678', 'Admin'),
(2, 'Chai.s', '$2y$10$VDFuRX4GytsGQjL2Zr3mke2DZvGjll7qrzffjWxPmWGo8Z7kw8DJG', 'ชัยวัตน์ ปรมาณู', 3, 1, 1, '0856867489', 'User'),
(3, 'adithep.k', '$2y$10$Sz.VW6tpB1VygwluJrCPHuOXVD6kiVoBS0op1DAweyckSqjEnTmZa', 'อดิเทพ กรทับทิม', 2, 1, 1, '123456789', 'User'),
(4, 'buris.s', '$2y$10$Sz.VW6tpB1VygwluJrCPHuOXVD6kiVoBS0op1DAweyckSqjEnTmZa', 'บุริศร์ สังขศรี', 2, 1, 1, '123456789', 'Admin'),
(5, 'kasam.s', '$2y$10$Sz.VW6tpB1VygwluJrCPHuOXVD6kiVoBS0op1DAweyckSqjEnTmZa', 'เกษม สร้างสรรค์', 2, 2, 2, '89954789', 'User');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `employment_types`
--
ALTER TABLE `employment_types`
  ADD PRIMARY KEY (`employment_type_id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `fk_trans_item` (`item_id`),
  ADD KEY `fk_trans_user` (`created_by`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `fk_items_category` (`category_id`);

--
-- Indexes for table `monthly_balances`
--
ALTER TABLE `monthly_balances`
  ADD PRIMARY KEY (`balance_id`),
  ADD KEY `fk_balances_item` (`item_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_requests_user` (`user_id`),
  ADD KEY `fk_requests_admin` (`approved_by`);

--
-- Indexes for table `request_items`
--
ALTER TABLE `request_items`
  ADD PRIMARY KEY (`request_item_id`),
  ADD KEY `fk_reqitems_request` (`request_id`),
  ADD KEY `fk_reqitems_item` (`item_id`);

--
-- Indexes for table `sub_departments`
--
ALTER TABLE `sub_departments`
  ADD PRIMARY KEY (`sub_department_id`),
  ADD KEY `fk_subdep_department` (`department_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `fk_users_department` (`department_id`),
  ADD KEY `fk_users_sub_department` (`sub_department_id`),
  ADD KEY `fk_users_employment_type` (`employment_type_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employment_types`
--
ALTER TABLE `employment_types`
  MODIFY `employment_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- AUTO_INCREMENT for table `monthly_balances`
--
ALTER TABLE `monthly_balances`
  MODIFY `balance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `request_items`
--
ALTER TABLE `request_items`
  MODIFY `request_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `sub_departments`
--
ALTER TABLE `sub_departments`
  MODIFY `sub_department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `fk_trans_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `fk_requests_admin` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `request_items`
--
ALTER TABLE `request_items`
  ADD CONSTRAINT `fk_reqitems_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_departments`
--
ALTER TABLE `sub_departments`
  ADD CONSTRAINT `fk_subdep_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_employment_type` FOREIGN KEY (`employment_type_id`) REFERENCES `employment_types` (`employment_type_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_users_sub_department` FOREIGN KEY (`sub_department_id`) REFERENCES `sub_departments` (`sub_department_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

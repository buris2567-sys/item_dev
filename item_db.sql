-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2026 at 01:48 AM
-- Server version: 13.0.2-MariaDB
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
(6, 'กพม.'),
(8, 'กยผ.'),
(9, 'สลก.'),
(10, 'ศสบ.'),
(11, 'กตส.');

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
  `item_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `transaction_type` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `previous_stock` int(11) NOT NULL DEFAULT 0,
  `current_stock` int(11) NOT NULL DEFAULT 0,
  `remark` varchar(200) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`transaction_id`, `item_id`, `request_id`, `transaction_type`, `quantity`, `previous_stock`, `current_stock`, `remark`, `created_at`, `created_by`) VALUES
(3, 1, NULL, 'IN', 100, 0, 0, 'ปรับแก้โดย', '2026-09-18 17:00:29', 4),
(5, 2, NULL, 'IN', 20, 0, 0, 'ปรับแก้โดย', '2026-09-18 17:03:02', 4),
(6, 3, NULL, 'IN', 20, 0, 0, 'ปรับแก้โดย', '2026-09-18 17:03:27', 4),
(50, 4, NULL, 'นำ', 0, 0, 0, 'ลงทะเบียนเพิ่มสิ่งของใหม่ในระบบ', '2026-09-23 12:54:05', 1),
(51, 5, NULL, 'สร้างสิ่งข', 50, 0, 50, 'รับยอดยกมาเริ่มต้น', '2026-09-23 12:54:20', 1),
(52, 6, NULL, 'CREATE', 0, 0, 0, 'ลงทะเบียนเพิ่มสิ่งของใหม่ในระบบ', '2026-09-23 12:54:52', 1),
(61, 7, NULL, 'CREATE', 4, 0, 4, 'เพิ่มสิ่งของใหม่โดย admin (รายการ: qwqwqwqwqw - ประเภท: ของที่ระลึก)', '2026-09-24 09:22:40', 1),
(72, 7, NULL, 'IN', 20, 4, 24, 'เพิ่มสต็อก: โดย admin', '2026-09-24 11:23:52', 1),
(73, 8, NULL, 'CREATE', 20, 0, 20, 'เพิ่มสิ่งของใหม่โดย admin', '2026-09-24 12:52:42', 1),
(74, 8, NULL, 'EDIT', 0, 20, 20, 'แก้ไขข้อมูลสิ่งของ: maps โดย admin', '2026-09-24 12:57:14', 1),
(75, 9, NULL, 'CREATE', 50, 0, 50, 'เพิ่มสิ่งของใหม่โดย admin', '2026-09-24 14:30:43', 1),
(76, 9, NULL, 'OUT', -10, 50, 40, 'ลดสต็อก: โดย admin', '2026-09-24 14:31:08', 1),
(77, 9, NULL, 'EDIT', 0, 40, 40, 'แก้ไขข้อมูลสิ่งของ: หนังสือ คำศัพท์ ปส. โดย admin', '2026-09-24 14:31:21', 1),
(78, 9, NULL, 'IN', 5, 40, 45, 'เพิ่มสต็อก: โดย admin', '2026-09-24 14:32:16', 1),
(79, 6, NULL, 'IN', 1, 0, 1, 'เพิ่มสต็อก: โดย admin', '2026-09-24 14:53:15', 1),
(82, 7, NULL, 'EDIT', 0, 24, 24, 'แก้ไขข้อมูลสิ่งของ: qwqwqwqwqw โดย admin', '2026-09-24 15:06:58', 1),
(85, 9, NULL, 'IN', 20, 45, 65, 'เพิ่มสต็อก: โดย admin', '2026-09-24 17:49:08', 1),
(92, 6, NULL, 'IN', 4, 1, 5, 'เพิ่มสต็อก: โดย admin', '2026-09-25 16:20:40', 1),
(93, 5, NULL, 'EDIT', 0, 50, 50, 'แก้ไขข้อมูลสิ่งของ: พวงกุญแจ โดย admin', '2026-09-29 15:51:35', 1),
(94, 5, NULL, 'EDIT', 0, 50, 50, 'แก้ไขข้อมูลสิ่งของ: พวงกุญแจ โดย admin', '2026-09-29 16:47:32', 1),
(95, 9, NULL, 'EDIT', 0, 65, 65, 'แก้ไขข้อมูลสิ่งของ: หนังสือ คำศัพท์ ปส. โดย admin', '2026-09-29 16:48:56', 1),
(98, 10, NULL, 'สร้างสิ่งข', 8, 0, 8, 'เพิ่มสิ่งของใหม่โดย admin', '2026-10-01 12:29:21', 1),
(99, 3, NULL, 'แก้ไขสิ่งข', 0, 20, 20, 'แก้ไขข้อมูลสิ่งของ: หนังสือ การ์ตูน โดย admin', '2026-10-01 12:31:04', 1),
(100, 10, NULL, 'แก้ไขสิ่งของ', 0, 8, 8, 'แก้ไขข้อมูลสิ่งของ: Port123 โดย admin', '2026-10-01 12:32:21', 1),
(102, 10, NULL, 'นำเข้าสิ่งของ', 5, 8, 13, 'เพิ่มสต็อก: โดย admin', '2026-10-01 12:33:11', 1),
(106, 10, NULL, 'OUT', -5, 13, 8, 'จ่ายออกตามคำขอเบิก REQ-2610-0001', '2026-10-02 14:22:19', 1),
(107, 7, NULL, 'OUT', -4, 24, 20, 'จ่ายออกตามคำขอเบิก REQ-2610-0001', '2026-10-02 14:22:19', 1),
(108, 1, NULL, 'OUT', -1, 100, 99, 'จ่ายออกตามคำขอเบิก REQ-2610-0001', '2026-10-02 14:22:19', 1),
(110, 3, NULL, 'OUT', -5, 20, 15, 'จ่ายออกตามคำขอเบิก REQ-2609-DD6E', '2026-10-02 14:47:28', 1),
(112, 10, NULL, 'IN', 5, 8, 13, 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0001', '2026-10-02 14:47:49', 1),
(113, 7, NULL, 'IN', 4, 20, 24, 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0001', '2026-10-02 14:47:49', 1),
(114, 1, NULL, 'IN', 1, 99, 100, 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0001', '2026-10-02 14:47:49', 1),
(115, 8, NULL, 'ถอนจากคำร้อง', -5, 20, 15, 'จ่ายออกตามคำขอเบิก REQ-2610-0002', '2026-10-02 18:22:23', 1),
(116, 9, NULL, 'ถอนจากคำร้อง', -5, 65, 60, 'จ่ายออกตามคำขอเบิก REQ-2610-0002', '2026-10-02 18:22:23', 1),
(117, 8, NULL, 'ยกเลิกคำร้อง', 5, 15, 20, 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0002', '2026-10-02 18:27:20', 1),
(118, 9, NULL, 'ยกเลิกคำร้อง', 5, 60, 65, 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0002', '2026-10-02 18:27:20', 1),
(119, 8, NULL, 'ยกเลิกคำร้อง', 5, 20, 25, 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0002', '2026-10-02 18:27:52', 1),
(120, 9, NULL, 'ยกเลิกคำร้อง', 5, 65, 70, 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0002', '2026-10-02 18:27:52', 1),
(122, 3, NULL, 'ยกเลิกคำร้อง', 5, 15, 20, 'คืนสต็อกจากการยกเลิกคำขอ REQ-2609-DD6E', '2026-10-04 20:26:48', 1),
(124, 2, NULL, 'นำเข้าสิ่งของ', 1, 20, 21, 'เพิ่มสต็อก: โดย admin', '2026-10-04 20:58:58', 1),
(125, 4, NULL, 'นำเข้าสิ่งของ', 1, 0, 1, 'เพิ่มสต็อก: โดย buris.s', '2026-10-04 23:29:50', 4),
(128, 11, NULL, 'สร้างสิ่งของ', 7, 0, 7, 'เพิ่มสิ่งของใหม่โดย buris.s', '2026-10-05 00:39:31', 4),
(130, 1, NULL, 'ถอนจากคำร้อง', -1, 100, 99, 'จ่ายออกตามคำขอเบิก REQ-2610-0003', '2026-10-05 01:26:48', 4),
(131, 3, NULL, 'ถอนจากคำร้อง', -1, 20, 19, 'จ่ายออกตามคำขอเบิก REQ-2610-0003', '2026-10-05 01:26:48', 4),
(136, 6, NULL, 'แก้ไขสิ่งของ', 0, 5, 5, 'แก้ไขข้อมูลสิ่งของ: กระเป๋าผ้า โดย admin', '2026-10-06 08:08:46', 1),
(137, 11, NULL, 'แก้ไขสิ่งของ', 0, 7, 7, 'แก้ไขข้อมูลสิ่งของ: ชั้นวาง โดย admin', '2026-10-06 08:20:37', 1),
(138, 7, NULL, 'แก้ไขสิ่งของ', 0, 24, 24, 'แก้ไขข้อมูลสิ่งของ โดย admin', '2026-10-06 08:22:25', 1),
(139, 5, NULL, 'แก้ไขสิ่งของ', 0, 50, 50, 'แก้ไขข้อมูลสิ่งของ โดย admin', '2026-10-06 08:22:40', 1),
(140, 4, NULL, 'แก้ไขสิ่งของ', 0, 1, 1, 'แก้ไขข้อมูลสิ่งของ โดย admin', '2026-10-06 08:23:04', 1),
(141, 3, NULL, 'แก้ไขสิ่งของ', 0, 19, 19, 'แก้ไขข้อมูลสิ่งของ โดย admin', '2026-10-06 08:24:40', 1),
(144, 2, NULL, 'ถอนจากคำร้อง', -3, 21, 18, 'จ่ายออกตามคำขอเบิก REQ-2610-0010', '2026-10-06 10:11:12', 1),
(145, 8, NULL, 'ถอนจากคำร้อง', -1, 25, 24, 'จ่ายออกตามคำขอเบิก REQ-2610-0011', '2026-10-06 10:14:09', 1),
(147, 8, NULL, 'ยกเลิกคำร้อง', 1, 24, 25, 'คืนสต็อกจากการยกเลิกคำขอ REQ-2610-0011', '2026-10-06 10:16:28', 1),
(155, 8, NULL, 'ถอนออกสิ่งของ', -15, 25, 10, 'ลดสต็อก: โดย admin', '2026-10-06 11:24:57', 1),
(157, 8, NULL, 'นำเข้าสิ่งของ', 3, 10, 13, 'เพิ่มสต็อก: โดย admin', '2026-10-06 11:58:03', 1),
(158, 5, NULL, 'ถอนจากคำร้อง', -6, 50, 44, 'จ่ายออกตามคำขอเบิก REQ-2610-0009', '2026-10-06 12:21:29', 1),
(159, 4, NULL, 'ถอนจากคำร้อง', -1, 1, 0, 'จ่ายออกตามคำขอเบิก REQ-2610-0009', '2026-10-06 12:21:29', 1),
(160, 8, NULL, 'ถอนออกสิ่งของ', -13, 13, 0, 'ลดสต็อก: โดย buris.s', '2026-10-06 13:47:05', 4),
(168, 11, NULL, 'นำเข้าสิ่งของ', 3, 7, 10, 'ปรับเพิ่มสต็อกโดยแอดมิน', '2026-10-15 15:27:29', 1),
(169, 10, NULL, 'ถอนสิ่งของ', -1, 13, 12, 'ปรับลดสต็อกโดยแอดมิน', '2026-10-15 15:27:37', 1),
(172, 8, NULL, 'นำเข้าสิ่งของ', 3, 0, 3, 'ปรับเพิ่มสต็อกโดยแอดมิน', '2026-10-15 16:16:12', 1),
(173, 8, NULL, 'ลบสิ่งของ', 0, 3, 3, 'ยกเลิกการใช้งานสิ่งของ (Soft Delete)', '2026-10-15 17:21:22', 1),
(174, 11, NULL, 'แก้ไขสิ่งของ', 0, 10, 10, 'แก้ไขข้อมูลรายละเอียดสิ่งของ', '2026-10-15 18:03:35', 1),
(175, 12, NULL, 'สร้างสิ่งของ', 70, 0, 70, 'ลงทะเบียนเพิ่มสิ่งของใหม่ในระบบ', '2026-10-15 18:04:16', 1),
(176, 2, NULL, 'ลบสิ่งของ', 0, 18, 18, 'ยกเลิกการใช้งานสิ่งของ (Soft Delete)', '2026-10-15 18:04:43', 1),
(177, 2, NULL, 'ลบสิ่งของ', -18, 18, 0, 'ยกเลิกการใช้งานและล้างสต็อกเป็น 0', '2026-10-15 18:10:43', 1),
(178, 8, NULL, 'ลบสิ่งของ', -3, 3, 0, 'ยกเลิกการใช้งานและล้างสต็อกเป็น 0', '2026-10-16 01:28:00', 1),
(179, 12, NULL, 'แก้ไขสิ่งของ', 0, 70, 70, 'แก้ไขข้อมูลรายละเอียดสิ่งของ', '2026-10-18 20:59:10', 1),
(180, 12, NULL, 'นำเข้าสิ่งของ', 1, 70, 71, 'ปรับเพิ่มสต็อกโดยแอดมิน', '2026-10-18 20:59:55', 1),
(181, 1, NULL, 'ถอนสิ่งของ', -44, 99, 55, 'ปรับลดสต็อกโดยแอดมิน', '2026-10-19 10:35:25', 1),
(182, 6, NULL, 'นำเข้าสิ่งของ', 7, 5, 12, 'ปรับเพิ่มสต็อกโดยแอดมิน', '2026-10-20 15:50:18', 1),
(183, 12, NULL, 'นำเข้าสิ่งของ', 1, 71, 72, 'ปรับเพิ่มสต็อกโดยแอดมิน', '2026-10-20 15:50:44', 1),
(184, 6, NULL, 'นำเข้าสิ่งของ', 1, 12, 13, 'ปรับเพิ่มสต็อกโดยแอดมิน', '2026-10-20 15:50:59', 1),
(185, 13, NULL, 'สร้างสิ่งของ', 20, 0, 20, 'ลงทะเบียนเพิ่มสิ่งของใหม่ในระบบ', '2026-10-20 15:52:59', 1),
(186, 13, NULL, 'แก้ไขสิ่งของ', 0, 20, 20, 'แก้ไขข้อมูลรายละเอียดสิ่งของ', '2026-10-20 15:53:18', 1),
(187, 6, NULL, 'ถอนสิ่งของ', -7, 13, 6, 'ปรับลดสต็อกโดยแอดมิน', '2026-10-20 15:53:52', 1),
(188, 13, NULL, 'ถอนสิ่งของ', -20, 20, 0, 'ปรับลดสต็อกโดยแอดมิน', '2026-10-20 15:56:20', 1),
(189, 6, NULL, 'ลบสิ่งของ', -6, 6, 0, 'ยกเลิกการใช้งานและล้างสต็อกเป็น 0', '2026-10-20 15:58:06', 1),
(190, 13, NULL, 'นำเข้าสิ่งของ', 20, 0, 20, 'ปรับเพิ่มสต็อกโดยแอดมิน', '2026-10-20 16:14:24', 1),
(191, 13, NULL, 'ลบสิ่งของ', 0, 20, 20, 'ยกเลิกการใช้งานสิ่งของ (Soft Delete)', '2026-10-20 16:14:28', 1),
(192, 12, NULL, 'แก้ไขสิ่งของ', 0, 72, 72, 'แก้ไขข้อมูลรายละเอียดสิ่งของ', '2026-10-20 16:20:33', 1),
(193, 14, NULL, 'สร้างสิ่งของ', 77, 0, 77, 'ลงทะเบียนเพิ่มสิ่งของใหม่ในระบบ', '2026-10-20 16:25:06', 1),
(194, 14, NULL, 'นำเข้าสิ่งของ', 3, 77, 80, 'ปรับเพิ่มสต็อกโดยแอดมิน', '2026-10-23 09:37:52', 1),
(195, 14, NULL, 'ถอนสิ่งของ', -2, 80, 78, 'ปรับลดสต็อกโดยแอดมิน', '2026-10-23 09:38:02', 1),
(196, 14, NULL, 'นำเข้าสิ่งของ', 1, 78, 79, 'ปรับเพิ่มสต็อกโดยแอดมิน', '2026-10-23 11:21:09', 1),
(197, 14, NULL, 'นำเข้าสิ่งของ', 3, 79, 82, 'ปรับเพิ่มสต็อกโดยแอดมิน', '2026-10-23 11:29:58', 1),
(198, 14, NULL, 'นำเข้าสิ่งของ', 1, 82, 83, '', '2026-10-23 11:30:18', 1),
(199, 14, NULL, 'ถอนสิ่งของ', -1, 83, 82, 'ปรับลดสต็อกโดยแอดมิน', '2026-10-23 11:46:59', 4),
(200, 12, NULL, 'แก้ไขสิ่งของ', 0, 72, 72, 'แก้ไขข้อมูลรายละเอียดสิ่งของ', '2026-10-23 11:48:18', 4),
(201, 12, 20, 'ถอนจากคำร้อง', -5, 72, 67, 'จ่ายออกตามคำขอเบิก', '2026-10-23 12:08:07', 4),
(202, 1, 20, 'ถอนจากคำร้อง', -6, 55, 49, 'จ่ายออกตามคำขอเบิก', '2026-10-23 12:08:07', 4),
(203, 3, 20, 'ถอนจากคำร้อง', -4, 19, 15, 'จ่ายออกตามคำขอเบิก', '2026-10-23 12:08:07', 4),
(204, 4, NULL, 'นำเข้าสิ่งของ', 2, 0, 2, 'ปรับเพิ่มสต็อกโดยแอดมิน', '2026-10-23 12:17:54', 4),
(205, 11, NULL, 'ถอนสิ่งของ', -10, 10, 0, 'ปรับลดสต็อกโดยแอดมิน', '2026-10-23 12:19:17', 4),
(206, 11, NULL, 'แก้ไขสิ่งของ', 0, 0, 0, 'แก้ไขข้อมูลรายละเอียดสิ่งของ', '2026-10-23 12:21:16', 4),
(207, 12, 20, 'ยกเลิกคำร้อง', 5, 67, 72, 'คืนสต็อกจากการยกเลิกคำขอ', '2026-10-23 12:30:50', 4),
(208, 1, 20, 'ยกเลิกคำร้อง', 6, 49, 55, 'คืนสต็อกจากการยกเลิกคำขอ', '2026-10-23 12:30:50', 4),
(209, 3, 20, 'ยกเลิกคำร้อง', 4, 15, 19, 'คืนสต็อกจากการยกเลิกคำขอ', '2026-10-23 12:30:50', 4),
(210, 12, NULL, 'แก้ไขสิ่งของ', 0, 72, 72, 'แก้ไขข้อมูลรายละเอียดสิ่งของ', '2026-10-23 12:40:06', 4),
(211, 1, 21, 'ถอนจากคำร้อง', -1, 55, 54, 'จ่ายออกตามคำขอเบิก', '2026-10-23 13:13:35', 1),
(212, 5, 21, 'ถอนจากคำร้อง', -1, 44, 43, 'จ่ายออกตามคำขอเบิก', '2026-10-23 13:13:35', 1),
(213, 9, 21, 'ถอนจากคำร้อง', -1, 70, 69, 'จ่ายออกตามคำขอเบิก', '2026-10-23 13:13:35', 1),
(214, 12, 21, 'ถอนจากคำร้อง', -1, 72, 71, 'จ่ายออกตามคำขอเบิก', '2026-10-23 13:13:35', 1),
(215, 1, 22, 'ถอนจากคำร้อง', -20, 54, 34, 'จ่ายออกตามคำขอเบิก', '2026-10-24 16:07:04', 4),
(216, 1, 22, 'ยกเลิกคำร้อง', 20, 34, 54, 'คืนสต็อกจากการยกเลิกคำขอ', '2026-10-24 16:07:42', 4),
(217, 1, 23, 'ถอนจากคำร้อง', -30, 54, 24, 'จ่ายออกตามคำขอเบิก', '2026-10-24 16:20:07', 4);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `item_code` varchar(20) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `current_stock` int(11) DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `low_stock_threshold` int(11) DEFAULT 10,
  `images` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `item_code`, `category_id`, `name`, `description`, `current_stock`, `is_active`, `low_stock_threshold`, `images`, `created_at`) VALUES
(1, 'ITM1789725629', 1, 'ดินสอ 2B', '', 24, 1, 10, '[\"ITM1789725629_1_6aad0bbd463c1.jpg\"]', '2026-09-18 17:00:29'),
(2, 'ITM1789725782', 1, 'ตุ๊กตา', '', 0, 0, 10, '[\"ITM1789725782_1_6aad0c568e4bd.jpg\"]', '2026-09-18 17:03:02'),
(3, 'ITM1789725807', 2, 'สมุดระบายสี', '', 19, 1, 10, '[\"ITM1789725807_edit_1_6ac44dd8b2fcb.jpg\",\"ITM1789725807_edit_2_6ac44dd8b3163.jpg\"]', '2026-09-18 17:03:27'),
(4, 'ITM1790142845', 1, 'ร่ม', '', 2, 1, 10, '[\"ITM1790142845_edit_1_6ac44d78227e3.jpg\"]', '2026-09-23 12:54:05'),
(5, 'ITM1790142860', 1, 'พวงกุญแจ', 'พวงกุญแจ การตูน น่ารัก', 43, 1, 10, '[\"ITM1790142860_edit_1_6ac44d605df43.jpg\"]', '2026-09-23 12:54:20'),
(6, 'ITM1790142892', 1, 'กระเป๋าผ้า', '', 0, 0, 10, '[\"ITM1790142892_edit_1_6ac44a1eadde6.jpg\"]', '2026-09-23 12:54:52'),
(7, 'ITM1790216560', 1, 'จอทีวี', '', 24, 1, 10, '[\"ITM1790216560_edit_1_6ab4da22602ad.jpg\",\"ITM1790216560_edit_2_6ab4da226079d.jpg\"]', '2026-09-24 09:22:40'),
(8, 'ITM1790229162', 2, 'maps', 'map of  forest', 0, 0, 10, '[\"ITM1790229162_1_6ab4baaae0e05.png\",\"ITM1790229162_2_6ab4baaae16bf.jpg\"]', '2026-09-24 12:52:42'),
(9, 'ITM1790235043', 2, 'หนังสือ คำศัพท์ ปส.', 'หนีงสือคำศัพท์', 69, 1, 10, '[\"ITM1790235043_1_6ab4d1a38d150.png\"]', '2026-09-24 14:30:43'),
(10, 'ITM1790832561', 1, 'Port123', '', 12, 1, 10, '[\"ITM1790832561_1_6abdefb1f094f.png\"]', '2026-10-01 12:29:21'),
(11, 'ITM1791135571', 1, 'ชั้นวาง 123', '', 0, 1, 10, '[\"11_edit_1_6adaeecc49cb5.jpg\"]', '2026-10-05 00:39:31'),
(12, 'ITM1792062256586', 3, 'ถังขยา', '', 71, 1, 10, '[\"ITM1792062256586_1_6ad0b3304151c.jpg\",\"ITM1792062256586_edit_2_6ad4d0aedc0bd.jpg\"]', '2026-10-15 18:04:16'),
(13, 'ITM1792486379777', 3, 'ร่มไม้', '', 20, 0, 10, '[\"ITM1792486379777_1_6ad72beb56b6e.jpg\"]', '2026-10-20 15:52:59'),
(14, 'ITM1792488306656', 2, 'ดูดวง', '', 82, 1, 10, '[\"ITM1792488306656_1_6ad733723773b.jpg\"]', '2026-10-20 16:25:06');

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
  `request_id` int(11) NOT NULL,
  `request_no` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
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

INSERT INTO `requests` (`request_id`, `request_no`, `user_id`, `request_date`, `use_date`, `event_type`, `event_name`, `location`, `purpose`, `user_note`, `status`, `admin_comment`, `approved_at`, `approved_by`) VALUES
(1, 'REQ-2609-0751', 3, '2026-09-30 18:27:24', '2026-10-08', 'จัดนิทรรศการ', 'วันน้ำท่วม', 'โรงอาหาร', 'บูชายัญ', '', 'ไม่อนุมัติ', '', '2026-10-02 16:14:24', 1),
(2, 'REQ-2609-B401', 3, '2026-09-30 18:28:05', '2026-10-08', 'จัดนิทรรศการ', 'วันน้ำท่วม', 'โรงอาหาร', 'บูชายัญ', '', 'ยกเลิก', 'ไม่ให้', '2026-10-02 14:47:07', 1),
(3, 'REQ-2609-DD6E', 3, '2026-09-30 17:33:07', '2026-10-02', 'ประชุม/สัมมนา', 'วันเด็ก 2569', 'โรงอาหาร', 'รางวัล', '', 'ยกเลิก', '', '2026-10-02 14:47:28', 1),
(4, 'REQ-2610-0001', 3, '2026-10-02 14:06:36', '2026-10-15', 'ประชุม/สัมมนา', 'ใหม่', 'โรงทาน', 'ประกอบพิธีสานฝัน', '', 'ยกเลิก', 'อนุมัติแค่บางส่วน', '2026-10-02 14:22:19', 1),
(5, 'REQ-2610-0002', 2, '2026-10-02 18:05:28', '2026-10-14', 'ประชุม/สัมมนา', 'วันน้ำท่วม', 'โรงทาน', 'แจกผู้ลำบาก', 'ของมีจำนวนจำกัด', 'ยกเลิก', 'แผนที่หลงทางตอนน้ำท่วม มีจำนวนจำกัด', '2026-10-02 18:22:23', 1),
(6, 'REQ-2610-0003', 5, '2026-10-04 21:14:28', '2026-10-20', 'ประชุม/สัมมนา', 'ปีใหม่', 'ห้องประชุม เดลต้า', 'ของทีระลึกแก่พนักงาน', 'ขอเพิ่มพิเศษ ใส่ไข่', 'อนุมัติ', '', '2026-10-05 01:26:48', 4),
(7, 'REQ-2610-0004', 5, '2026-10-05 01:15:07', '2026-09-30', 'โครงการ', 'ทดลองอาบูดาบี', 'ทดลองอาบูดาบี', 'ลองลบรายการฝั่งแอดมิน ในระหว่างที่ส่งคำร้องเสร็จ', '', 'ยกเลิก', 'เนื่องจากว่า ได้ลบของชิ้นนี้ไปจาก คลังแล้ว', '2026-10-05 01:17:33', 4),
(8, 'REQ-2610-0005', 5, '2026-10-05 10:37:38', '2026-10-01', 'งาน', 'วันน้ำท่วม', 'ทดลองอาบูดาบี', '1234', '', 'ไม่อนุมัติ', '', '2026-10-05 11:25:25', 4),
(9, 'REQ-2610-0006', 5, '2026-10-05 11:23:34', '2026-10-15', 'งาน', 'มิงกาละยา', 'ห้องประชุม เดลต้า', '7894', '', 'อนุมัติ', '', '2026-10-05 11:23:53', 4),
(10, 'REQ-2610-0007', 5, '2026-10-05 13:26:22', '2026-10-06', 'งาน', 'หฟหฟห', 'หฟหฟ', 'หฟหฟหฟ', '', 'รออนุมัติ', NULL, NULL, NULL),
(11, 'REQ-2610-0008', 5, '2026-10-05 13:26:45', '2026-10-14', 'งาน', 'วันน้ำท่วม', 'หกหก', 'กหกหก', 'กหกหก', 'รออนุมัติ', NULL, NULL, NULL),
(12, 'REQ-2610-0009', 5, '2026-10-05 17:49:54', '2026-10-15', 'สัมมนา', 'หหห', 'หหห', 'หหห', 'หห', 'อนุมัติบางส่วน', 'ตัวโมเดล ไม้หมด', '2026-10-06 12:21:29', 1),
(13, 'REQ-2610-0010', 5, '2026-10-06 09:58:39', '2026-10-06', 'จัดนิทรรศการ', 'ปีใหม่', 'โรงอาหาร', 'แจกของรางว้ล', '', 'อนุมัติบางส่วน', '', '2026-10-06 10:11:12', 1),
(14, 'REQ-2610-0011', 5, '2026-10-06 10:13:59', '2026-10-14', 'งาน', 'วันน้ำท่วม', 'โรงทาน', 'ดกอก', '', 'ยกเลิก', '', '2026-10-06 10:14:09', 1),
(15, 'REQ-2610-0012', 5, '2026-10-06 17:26:43', '2026-10-06', 'งาน', 'วันเด็ก 2569', 'โรงทาน', '55555', '', 'อนุมัติ', '', '2026-10-06 17:26:58', 1),
(16, 'REQ-2610-0013', 5, '2026-10-15 17:50:21', '2026-10-13', 'งาน', 'มิงกาละยา', 'โรงทาน', '778888', '', 'รออนุมัติ', NULL, NULL, NULL),
(17, 'REQ-2610-0014', 5, '2026-10-15 18:10:07', '2026-10-06', 'งาน', 'วันเด็ก 2569', 'โรงทาน', '111', '', 'ไม่อนุมัติ', 'ตุ๊กตาหมด จ้า', '2026-10-15 18:11:27', 1),
(18, 'REQ-2610-0015', 5, '2026-10-16 01:24:07', '2026-10-08', 'อบรม', 'วันน้ำท่วม', 'ssss', 'sss', '', 'รออนุมัติ', NULL, NULL, NULL),
(19, 'REQ-2610-0016', 5, '2026-10-16 01:27:31', '2026-10-06', 'สัมมนา', 'วันน้ำท่วม', 'fff', 'ddd', '', 'รออนุมัติ', NULL, NULL, NULL),
(20, 'REQ-2610-0017', 5, '2026-10-18 21:03:41', '2026-10-06', 'สัมมนา', 'วันเด็ก 2569', 'โรงทาน', 'แจกรางวัลเด็ก', 'ของฟรี', 'ยกเลิก', '', '2026-10-23 12:30:50', 4),
(21, 'REQ-2610-0021', 5, '2026-10-23 12:30:08', '2026-10-02', 'งาน', 'วันน้ำท่วม', 'โรงอาหาร', 'หหห', '', 'อนุมัติบางส่วน', 'ชั้นวาง 123 ไม่มี', '2026-10-23 13:13:35', 1),
(22, 'REQ-2610-0025', 5, '2026-10-24 16:06:37', '2026-10-21', 'สัมมนา', 'ปีใหม่', 'ทดลองจำนวน', 'ทดลองจำนวน', 'ทดลองจำนวน', 'ยกเลิก', '', '2026-10-24 16:07:42', 4),
(23, 'REQ-2610-0029', 5, '2026-10-24 16:09:15', '2026-10-13', 'งาน', 'ทดลองจำนวน', 'ทดลองจำนวน', 'ทดลองจำนวน', '', 'อนุมัติ', 'เทหมดหน้าตัก', '2026-10-24 16:20:07', 4);

-- --------------------------------------------------------

--
-- Table structure for table `request_items`
--

CREATE TABLE `request_items` (
  `request_item_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `requested_qty` int(11) NOT NULL,
  `approved_qty` int(11) DEFAULT NULL,
  `item_status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `request_items`
--

INSERT INTO `request_items` (`request_item_id`, `request_id`, `item_id`, `requested_qty`, `approved_qty`, `item_status`) VALUES
(2, 3, 3, 5, 5, 'อนุมัติเต็ม'),
(5, 1, 2, 4, 0, 'ไม่อนุมัติ'),
(7, 2, 2, 4, 0, 'ไม่อนุมัติ'),
(8, 4, 10, 5, 5, 'อนุมัติเต็ม'),
(9, 4, 7, 5, 4, 'อนุมัติบางส่วน'),
(10, 4, 1, 1, 1, 'อนุมัติเต็ม'),
(11, 5, 8, 10, 5, 'อนุมัติบางส่วน'),
(12, 5, 9, 5, 5, 'อนุมัติเต็ม'),
(13, 6, 1, 1, 1, 'อนุมัติเต็ม'),
(14, 6, 3, 1, 1, 'อนุมัติเต็ม'),
(16, 8, 10, 1, 0, 'ไม่อนุมัติ'),
(17, 8, 2, 1, 0, 'ไม่อนุมัติ'),
(19, 8, 8, 1, 0, 'ไม่อนุมัติ'),
(21, 10, 4, 1, NULL, NULL),
(24, 12, 5, 9, 6, 'อนุมัติบางส่วน'),
(25, 12, 4, 2, 1, 'อนุมัติบางส่วน'),
(27, 13, 1, 2, 0, 'ไม่อนุมัติ'),
(28, 13, 2, 3, 3, 'อนุมัติเต็ม'),
(29, 14, 8, 1, 1, 'อนุมัติเต็ม'),
(33, 16, 2, 0, NULL, NULL),
(34, 16, 5, 2, NULL, NULL),
(35, 16, 3, 1, NULL, NULL),
(36, 17, 2, 4, 0, 'ไม่อนุมัติ'),
(37, 18, 3, 8, NULL, NULL),
(38, 18, 12, 2, NULL, NULL),
(39, 19, 8, 3, NULL, NULL),
(40, 19, 10, 1, NULL, NULL),
(41, 19, 6, 1, NULL, NULL),
(42, 20, 12, 5, 0, 'ยกเลิก'),
(43, 20, 1, 6, 0, 'ยกเลิก'),
(44, 20, 3, 4, 0, 'ยกเลิก'),
(45, 21, 1, 1, 1, 'อนุมัติเต็ม'),
(46, 21, 5, 1, 1, 'อนุมัติเต็ม'),
(47, 21, 9, 1, 1, 'อนุมัติเต็ม'),
(48, 21, 11, 2, 0, 'ไม่อนุมัติ'),
(49, 21, 12, 1, 1, 'อนุมัติเต็ม'),
(50, 22, 1, 40, 0, 'ยกเลิก'),
(51, 23, 1, 30, 30, 'อนุมัติเต็ม');

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
  ADD KEY `fk_trans_user` (`created_by`),
  ADD KEY `fk_trans_request_new` (`request_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_code` (`item_code`),
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
  ADD UNIQUE KEY `request_no` (`request_no`),
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
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `employment_types`
--
ALTER TABLE `employment_types`
  MODIFY `employment_type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=218;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `monthly_balances`
--
ALTER TABLE `monthly_balances`
  MODIFY `balance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `request_items`
--
ALTER TABLE `request_items`
  MODIFY `request_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

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
  ADD CONSTRAINT `fk_trans_item_new` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trans_request_new` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_trans_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON UPDATE CASCADE;

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
  ADD CONSTRAINT `fk_reqitems_item_new` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reqitems_request_new` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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

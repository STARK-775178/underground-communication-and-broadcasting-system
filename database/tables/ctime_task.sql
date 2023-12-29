/*
 Navicat Premium Data Transfer

 定时广播

 Source Server         : localhost_3306
 Source Server Type    : MySQL
 Source Server Version : 50740
 Source Host           : localhost:3306
 Source Schema         : underground_communication

 Target Server Type    : MySQL
 Target Server Version : 50740
 File Encoding         : 65001

 Date: 20/10/2023 17:36:19
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for ctime_task
-- ----------------------------
DROP TABLE IF EXISTS `ctime_task`;
CREATE TABLE `ctime_task`  (
  `task_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '定时广播任务ID',
  `task_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '定时广播任务名称',
  `start_datetime` datetime NULL DEFAULT NULL COMMENT '开始时间日期',
  `reminder_time` set('opt0','opt1','opt2','opt3','opt4','opt5','opt6') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'opt0,opt1' COMMENT '提醒时间',
  `reminder_method` enum('opt0','opt1') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'opt0' COMMENT '提醒方式',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `update_time` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  PRIMARY KEY (`task_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 25 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '定时广播任务表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ctime_task
-- ----------------------------
INSERT INTO `ctime_task` VALUES (19, '444', '2023-10-13 20:52:24', 'opt0,opt1,opt2,opt3,opt4,opt5,opt6', 'opt0', '4444', 1695362540, 1697028690);
INSERT INTO `ctime_task` VALUES (20, '111', '2023-10-12 20:52:38', 'opt0,opt1', 'opt0', '1111', 1696769358, 1697028704);
INSERT INTO `ctime_task` VALUES (22, '4444', '2023-10-11 07:02:02', 'opt0,opt1,opt2,opt3', 'opt0', '5555', 1696772116, 1696772116);
INSERT INTO `ctime_task` VALUES (23, '555', '2023-10-11 22:40:43', 'opt0,opt1,opt5', 'opt0', '555', 1697035247, 1697035247);
INSERT INTO `ctime_task` VALUES (24, '111', '2023-10-31 00:00:00', 'opt0,opt1', 'opt0', '11111', 1697763265, 1697763265);

SET FOREIGN_KEY_CHECKS = 1;

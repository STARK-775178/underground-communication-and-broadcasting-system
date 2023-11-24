/*
 Navicat Premium Data Transfer

 录音文件

 Source Server         : localhost_3306
 Source Server Type    : MySQL
 Source Server Version : 50740
 Source Host           : localhost:3306
 Source Schema         : underground_communication

 Target Server Type    : MySQL
 Target Server Version : 50740
 File Encoding         : 65001

 Date: 24/11/2023 11:06:51
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for crecording_file
-- ----------------------------
DROP TABLE IF EXISTS `crecording_file`;
CREATE TABLE `crecording_file`  (
  `recording_file_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `recording_file_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件名称',
  `recording_file_url` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `duration` time NULL DEFAULT NULL COMMENT '时长',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `update_time` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  `create_time` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`recording_file_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '录音文件' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of crecording_file
-- ----------------------------
INSERT INTO `crecording_file` VALUES (1, '111', '/storage/default/20231102/3272437395896296cbec301aee260f91a5a308e1bcc8b345ee.mp3', '11:16:57', '11111', 1698895053, 1698895053);
INSERT INTO `crecording_file` VALUES (2, 'ceshi', '/storage/default/20231102/12418791552145f82fc77be096c4225ce6d14fbb6aaa489ea7.mp3', '11:47:08', '', 1698896847, 1698896847);
INSERT INTO `crecording_file` VALUES (3, 'ceshi1', '/storage/default/20231012/3272437395896296cbec301aee260f91a5a308e1bcc8b345ee.mp3', '11:47:27', 'ceshi', 1698896941, 1698896941);
INSERT INTO `crecording_file` VALUES (4, 'ceshi5', '/storage/default/20231102/12418791552145f82fc77be096c4225ce6d14fbb6aaa489ea7.mp3', '11:36:51', 'ceshi5', 1699933039, 1699933039);
INSERT INTO `crecording_file` VALUES (6, 'ceshi6', '/storage/default/20231114/recorder5aba3af15fdc5223d0b415543bb9babec4ac6165.mp3', NULL, '', 1699969631, 1699969631);
INSERT INTO `crecording_file` VALUES (7, 'ceshi7', '/storage/default/20231114/recorder274b1ff0915486c8de50532795a9f65a59740f4d.mp3', '00:03:00', '', 1699969989, 1699969989);
INSERT INTO `crecording_file` VALUES (8, 'ceshihahah', '/storage/default/20231114/recorder1bc24edac81a401436387344444365cfdf658326.mp3', '00:00:00', 'd&#039;d&#039;d&#039;d&#039;d', 1699972571, 1699972571);
INSERT INTO `crecording_file` VALUES (9, 'cehsi', '/storage/default/20231114/recorderb7c3a454476b9ce18502c14854a46ec18b7b926f.mp3', '00:04:00', 'c&#039;c', 1699972588, 1699972588);
INSERT INTO `crecording_file` VALUES (10, 'd&#039;d&#039;d&#039;d&#039;dddddd', '/storage/default/20231114/recorder7589ed28e815f449261250efa8cad219ad265129.mp3', '00:03:00', 'd&#039;d&#039;d&#039;d&#039;d', 1699972666, 1699972666);
INSERT INTO `crecording_file` VALUES (11, '111', '/storage/default/20231115/recorder8f115cfc16a95296af2939b0c0ad190216a4451b.mp3', '00:00:03', '111', 1700051897, 1700051897);

SET FOREIGN_KEY_CHECKS = 1;

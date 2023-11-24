/*
 Navicat Premium Data Transfer

 音频文件

 Source Server         : localhost_3306
 Source Server Type    : MySQL
 Source Server Version : 50740
 Source Host           : localhost:3306
 Source Schema         : test1

 Target Server Type    : MySQL
 Target Server Version : 50740
 File Encoding         : 65001

 Date: 12/10/2023 21:50:41
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for cvoice_file
-- ----------------------------
DROP TABLE IF EXISTS `cvoice_file`;
CREATE TABLE `cvoice_file`  (
  `voice_file_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '文件ID',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '备注',
  `update_time` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '修改时间',
  `create_time` bigint(20) UNSIGNED NULL DEFAULT NULL COMMENT '创建时间',
  `voice_file_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件路径',
  `voice_file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '文件名称',
  `duration` time NULL DEFAULT NULL COMMENT '时长',
  PRIMARY KEY (`voice_file_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '音频文件表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of cvoice_file
-- ----------------------------
INSERT INTO `cvoice_file` VALUES (1, '1111', 1692702213, 1692702213, '', '', NULL);
INSERT INTO `cvoice_file` VALUES (2, '111', 1692703531, 1692703531, '', '', '19:27:16');
INSERT INTO `cvoice_file` VALUES (3, '111', 1692704558, 1692704558, '', '', '19:40:42');
INSERT INTO `cvoice_file` VALUES (4, '1111', 1692704881, 1692704881, '/storage/default/20230822/3272437395896296cbec301aee260f91a5a308e1bcc8b345ee.mp3', '111', '19:47:18');
INSERT INTO `cvoice_file` VALUES (5, '11111', 1692705092, 1692705092, '/storage/default/20230822/a1000_u0_p409_s27ef2ebf8c4ab1bf3da999c3cd3c7ab91c3cd39a.mp3', '1111', '19:51:20');

SET FOREIGN_KEY_CHECKS = 1;

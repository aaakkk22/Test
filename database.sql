/*
 Navicat MySQL Data Transfer

 Source Server         : dsj
 Source Server Type    : MySQL
 Source Server Version : 50718
 Source Host           : rds1.c3w.cc:59668
 Source Schema         : yao

 Target Server Type    : MySQL
 Target Server Version : 50718
 File Encoding         : 65001

 Date: 11/10/2021 14:26:25
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tp_admin
-- ----------------------------
DROP TABLE IF EXISTS `tp_admin`;
CREATE TABLE `tp_admin`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id自增',
  `username` varchar(55) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户名',
  `password` varchar(55) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '密码',
  `roles` tinyint(1) NULL DEFAULT 0 COMMENT '默认权限0超级管理员 1系统管理员 2仓库管理员 3医护人员',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮箱',
  `add_time` int(11) NULL DEFAULT NULL COMMENT '注册时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_admin
-- ----------------------------
INSERT INTO `tp_admin` VALUES (4, 'xiaohe', '202cb962ac59075b964b07152d234b70', 1, '862109672@qq.com', 1608041001);
INSERT INTO `tp_admin` VALUES (5, 'dage', 'e10adc3949ba59abbe56e057f20f883e', 1, 'sssssss@qq.com', 1608084783);
INSERT INTO `tp_admin` VALUES (7, '1261334618', '43fd2d39ee6f5bacd31bad177581e313', 1, '1261334618@qq.com', 1608090004);
INSERT INTO `tp_admin` VALUES (8, 'xiaoping', 'e10adc3949ba59abbe56e057f20f883e', 3, '952365145@qq.com', 1608090020);
INSERT INTO `tp_admin` VALUES (10, 'xiaohe', 'e10adc3949ba59abbe56e057f20f883e', 3, '1787211521@qq.com', 1608114593);
INSERT INTO `tp_admin` VALUES (11, '李四', '202cb962ac59075b964b07152d234b70', 1, '862109672@qq.com', 1608120498);
INSERT INTO `tp_admin` VALUES (12, '小邓', 'e10adc3949ba59abbe56e057f20f883e', 1, '1787211521@qq.com', 1608188849);
INSERT INTO `tp_admin` VALUES (13, 'admin', 'e10adc3949ba59abbe56e057f20f883e', 1, '1787211521@qq.com', 1614051789);

-- ----------------------------
-- Table structure for tp_medicine_add_store
-- ----------------------------
DROP TABLE IF EXISTS `tp_medicine_add_store`;
CREATE TABLE `tp_medicine_add_store`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id自增',
  `medicine_id` int(11) NULL DEFAULT NULL COMMENT '药品id',
  `supplier_id` int(11) NULL DEFAULT NULL COMMENT '供应商id',
  `worker_id` int(11) NULL DEFAULT NULL COMMENT '职工id',
  `add_time` int(11) NULL DEFAULT NULL COMMENT '添加时间',
  `nums` int(11) NULL DEFAULT 0 COMMENT '入库数量',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_medicine_add_store
-- ----------------------------
INSERT INTO `tp_medicine_add_store` VALUES (2, 8, 6, 8, 1608113512, 86);
INSERT INTO `tp_medicine_add_store` VALUES (3, 7, 4, 10, 1608117089, 121);
INSERT INTO `tp_medicine_add_store` VALUES (4, 6, 6, 10, 1608189402, 50);

-- ----------------------------
-- Table structure for tp_medicine_list
-- ----------------------------
DROP TABLE IF EXISTS `tp_medicine_list`;
CREATE TABLE `tp_medicine_list`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id自增',
  `medicine_sn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '药品编号',
  `medicine_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '药品名称',
  `image` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '照片',
  `key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '规格',
  `dose` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '剂量',
  `price` decimal(12, 2) NULL DEFAULT NULL COMMENT '价格',
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地址',
  `stock` int(11) NULL DEFAULT 0 COMMENT '库存',
  `add_time` int(11) NULL DEFAULT NULL COMMENT '添加时间',
  `cat_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '药品类别',
  `nums` int(40) NULL DEFAULT NULL COMMENT '原数量',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 10 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_medicine_list
-- ----------------------------
INSERT INTO `tp_medicine_list` VALUES (5, 'H22026193', '感康复方氨酚烷胺片', 'https://pic.c3w.cc/video_xcx/20201216/77c6a83463d1405383131d5c78570e071000.jpg', '盒', NULL, 59.00, '吉林省吴太感康药业有限公司', 1000, 1608090937, '复方制剂', 1000);
INSERT INTO `tp_medicine_list` VALUES (6, 'Z44023001', '白云山板蓝根颗粒', 'https://pic.c3w.cc/video_xcx/20201216/838fe86bc7a65b95b6d20653cc89b9751000.jpg', '包', NULL, 27.80, '广州白云山和记黄埔中药有限公司', 900, 1608091094, '中药', 1000);
INSERT INTO `tp_medicine_list` VALUES (7, 'H10952530', '善存多维元素', 'https://pic.c3w.cc/video_xcx/20201216/c52346d0428e2e8c0857c2d176986c7e1000.jpg', '瓶', NULL, 79.50, '惠氏制药有限公司', 878, 1608091270, '复方制剂', 1000);
INSERT INTO `tp_medicine_list` VALUES (8, 'Z20013220', '江中健胃消食片', 'https://pic.c3w.cc/video_xcx/20201216/684a5c363439c9213b41e44c9d4be9501000.jpg', '盒', NULL, 42.00, '江中药业股份有限公司', 900, 1608092323, '中药', 1000);

-- ----------------------------
-- Table structure for tp_medicine_out_store
-- ----------------------------
DROP TABLE IF EXISTS `tp_medicine_out_store`;
CREATE TABLE `tp_medicine_out_store`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id自增',
  `medicine_id` int(11) NULL DEFAULT NULL COMMENT '药品id',
  `worker_id` int(11) NULL DEFAULT NULL COMMENT '职工id',
  `user_id` int(11) NULL DEFAULT NULL COMMENT '用户id',
  `update_time` int(11) NULL DEFAULT NULL COMMENT '添加时间',
  `nums` int(11) NULL DEFAULT NULL COMMENT '出库数量',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_medicine_out_store
-- ----------------------------
INSERT INTO `tp_medicine_out_store` VALUES (2, 8, 8, 2, 1608113594, 12);
INSERT INTO `tp_medicine_out_store` VALUES (3, 8, 8, 2, 1608113737, 2);
INSERT INTO `tp_medicine_out_store` VALUES (4, 7, 10, 5, 1608175650, 1);
INSERT INTO `tp_medicine_out_store` VALUES (5, 6, 10, 5, 1608189936, 50);

-- ----------------------------
-- Table structure for tp_role_list
-- ----------------------------
DROP TABLE IF EXISTS `tp_role_list`;
CREATE TABLE `tp_role_list`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id自增',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '角色名称',
  `permission` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '拥有权限',
  `description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '描述',
  `add_time` int(11) NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_role_list
-- ----------------------------
INSERT INTO `tp_role_list` VALUES (1, '系统管理员', '管理功能模块', '最高权限', 1608048238);
INSERT INTO `tp_role_list` VALUES (2, '仓库管理员', '供应商管理，客户信息管理，药品信息管理，库存信息管理', '部分权限', 1608096211);
INSERT INTO `tp_role_list` VALUES (3, '医护人员', '客户信息管理，药品信息管理，库存信息管理', '部分权限', 1608096235);

-- ----------------------------
-- Table structure for tp_staff
-- ----------------------------
DROP TABLE IF EXISTS `tp_staff`;
CREATE TABLE `tp_staff`  (
  `staff_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '职工id 自增',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名称',
  `add_time` int(11) NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`staff_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for tp_supplier_list
-- ----------------------------
DROP TABLE IF EXISTS `tp_supplier_list`;
CREATE TABLE `tp_supplier_list`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键Id',
  `supplier_sn` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '供应商编号',
  `name` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '供应商名称',
  `address` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地址',
  `phone` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机联系',
  `add_time` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_supplier_list
-- ----------------------------
INSERT INTO `tp_supplier_list` VALUES (3, 'Hu3301624', '河南慧润药业有限公司', '河南省郑州市金水区花园路国基路SOHO三栋A座', '2147483647', 1608091616);
INSERT INTO `tp_supplier_list` VALUES (4, 'CY3025640', '山东省济宁市翰辰医药有限公司', '山东省济宁市嘉祥县嘉祥梁宝寺 ', '757575', 1608091852);
INSERT INTO `tp_supplier_list` VALUES (5, 'Gw2614583', '武汉九港生物科技有限公司', '湖北省武汉市东西湖区科技产业基地1幢7层5号房', '5455', 1608092011);
INSERT INTO `tp_supplier_list` VALUES (6, 'Hk3625149', '深圳市慧康医疗康复设备有限公司', '广东省 深圳市宝安区深圳市光明区长圳社区长兴科技工业园 ', '18382979930', 1608092079);

-- ----------------------------
-- Table structure for tp_users
-- ----------------------------
DROP TABLE IF EXISTS `tp_users`;
CREATE TABLE `tp_users`  (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id自增',
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户名称',
  `image` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '照片',
  `phone` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机号码',
  `address` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '地址',
  `add_time` int(11) NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`user_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tp_users
-- ----------------------------
INSERT INTO `tp_users` VALUES (2, '张山', 'https://www.c3w.com.cn/public/images/avatar.png', '18382979934', '四川阆中', 1608117009);
INSERT INTO `tp_users` VALUES (5, '小邓', 'https://www.c3w.com.cn/public/images/avatar.png', '1326813023', '小何家里', 1608189059);
INSERT INTO `tp_users` VALUES (6, '阿拉丁', 'oss上传文件失败，InvalidAccessKeyId: The OSS Access Key Id you provided is disabled. RequestId: 61639711FA7FEF3530E02249', '12313', '翻斗花园', 1633916709);

SET FOREIGN_KEY_CHECKS = 1;

<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id:sql.update.VM-1.0.x_to_VM-1.1.0.php 431 2006-10-17 21:55:46 +0200 (Di, 17 Okt 2006) soeren_nb $
* @package VirtueMart
* @subpackage core
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_phpshop/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/

$db->query( "CREATE TABLE IF NOT EXISTS `#__{vm}_userfield` (
  `fieldid` int(11) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  `title` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `type` varchar(50) NOT NULL default '',
  `maxlength` int(11) default NULL,
  `size` int(11) default NULL,
  `required` tinyint(4) default '0',
  `ordering` int(11) default NULL,
  `cols` int(11) default NULL,
  `rows` int(11) default NULL,
  `value` varchar(50) default NULL,
  `default` int(11) default NULL,
  `published` tinyint(1) NOT NULL default '1',
  `registration` tinyint(1) NOT NULL default '0',
  `shipping` tinyint(1) NOT NULL default '0',
  `account` tinyint(1) NOT NULL default '1',
  `readonly` tinyint(1) NOT NULL default '0',
  `calculated` tinyint(1) NOT NULL default '0',
  `sys` tinyint(4) NOT NULL default '0',
  `vendor_id` int(11) default NULL,
  `params` mediumtext,
  PRIMARY KEY  (`fieldid`)
) TYPE=MyISAM AUTO_INCREMENT=30 COMMENT='Holds the fields for the user information';" );

## 
## Dumping data for table `#__{vm}_userfield`
## 

$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (1, 'email', 'REGISTER_EMAIL', '', 'emailaddress', 100, 30, 1, 2, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (7, 'title', 'PHPSHOP_SHOPPER_FORM_TITLE', '', 'select', 0, 0, 0, 8, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (3, 'password', 'PHPSHOP_SHOPPER_FORM_PASSWORD_1', '', 'password', 25, 30, 1, 4, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (4, 'password2', 'PHPSHOP_SHOPPER_FORM_PASSWORD_2', '', 'password', 25, 30, 1, 5, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (6, 'company', 'PHPSHOP_SHOPPER_FORM_COMPANY_NAME', '', 'text', 64, 30, 0, 7, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (5, 'delimiter_billto', 'PHPSHOP_USER_FORM_BILLTO_LBL', '', 'delimiter', 25, 30, 0, 6, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 0, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (2, 'username', 'REGISTER_UNAME', '', 'text', 25, 30, 1, 3, 0, 0, '', 0, 1, 1, 0, 1, 0, 0, 1, 1, '');" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (35, 'address_type_name', 'PHPSHOP_USER_FORM_ADDRESS_LABEL', '', 'text', 32, 30, 1, 6, NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (8, 'first_name', 'PHPSHOP_SHOPPER_FORM_FIRST_NAME', '', 'text', 32, 30, 1, 9, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (9, 'last_name', 'PHPSHOP_SHOPPER_FORM_LAST_NAME', '', 'text', 32, 30, 1, 10, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (10, 'middle_name', 'PHPSHOP_SHOPPER_FORM_MIDDLE_NAME', '', 'text', 32, 30, 0, 11, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (11, 'address_1', 'PHPSHOP_SHOPPER_FORM_ADDRESS_1', '', 'text', 64, 30, 1, 12, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (12, 'address_2', 'PHPSHOP_SHOPPER_FORM_ADDRESS_2', '', 'text', 64, 30, 0, 13, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (13, 'city', 'PHPSHOP_SHOPPER_FORM_CITY', '', 'text', 32, 30, 1, 14, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (14, 'zip', 'PHPSHOP_SHOPPER_FORM_ZIP', '', 'text', 32, 30, 1, 15, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (15, 'country', 'PHPSHOP_SHOPPER_FORM_COUNTRY', '', 'select', 0, 0, 1, 16, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (16, 'state', 'PHPSHOP_SHOPPER_FORM_STATE', '', 'select', 0, 0, 1, 17, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (17, 'phone_1', 'PHPSHOP_SHOPPER_FORM_PHONE', '', 'text', 32, 30, 1, 18, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (18, 'phone_2', 'PHPSHOP_SHOPPER_FORM_PHONE2', '', 'text', 32, 30, 0, 19, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (19, 'fax', 'PHPSHOP_SHOPPER_FORM_FAX', '', 'text', 32, 30, 0, 20, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (20, 'delimiter_bankaccount', 'PHPSHOP_ACCOUNT_BANK_TITLE', '', 'delimiter', 25, 30, 0, 21, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0, 0, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (21, 'bank_account_holder', 'PHPSHOP_ACCOUNT_LBL_BANK_ACCOUNT_HOLDER', '', 'text', 48, 30, 0, 22, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (22, 'bank_account_nr', 'PHPSHOP_ACCOUNT_LBL_BANK_ACCOUNT_NR', '', 'text', 32, 30, 0, 23, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (23, 'bank_sort_code', 'PHPSHOP_ACCOUNT_LBL_BANK_SORT_CODE', '', 'text', 16, 30, 0, 24, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (24, 'bank_name', 'PHPSHOP_ACCOUNT_LBL_BANK_NAME', '', 'text', 32, 30, 0, 25, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (25, 'bank_account_type', 'PHPSHOP_ACCOUNT_LBL_ACCOUNT_TYPE', '', 'select', 0, 0, 0, 26, 0, 0, '', 0, 1, 0, 0, 1, 1, 0, 1, 1, '');" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (26, 'bank_iban', 'PHPSHOP_ACCOUNT_LBL_BANK_IBAN', '', 'text', 64, 30, 0, 27, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (27, 'delimiter_sendregistration', 'BUTTON_SEND_REG', '', 'delimiter', 25, 30, 0, 28, NULL, NULL, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (28, 'agreed', 'PHPSHOP_I_AGREE_TO_TOS', '', 'checkbox', NULL, NULL, 1, 29, NULL, NULL, NULL, NULL, 1, 1, 0, 0, 0, 0, 1, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (29, 'delimiter_userinfo', 'PHPSHOP_ORDER_PRINT_CUST_INFO_LBL', '', 'delimiter', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 0, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (30, 'extra_field_1', 'PHPSHOP_SHOPPER_FORM_EXTRA_FIELD_1', '', 'text', 255, 30, 0, 31, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (31, 'extra_field_2', 'PHPSHOP_SHOPPER_FORM_EXTRA_FIELD_2', '', 'text', 255, 30, 0, 32, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (32, 'extra_field_3', 'PHPSHOP_SHOPPER_FORM_EXTRA_FIELD_3', '', 'text', 255, 30, 0, 33, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (33, 'extra_field_4', 'PHPSHOP_SHOPPER_FORM_EXTRA_FIELD_4', '', 'select', 1, 1, 0, 34, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL);" );
$db->query( "INSERT INTO `#__{vm}_userfield` VALUES (34, 'extra_field_5', 'PHPSHOP_SHOPPER_FORM_EXTRA_FIELD_5', '', 'select', 1, 1, 0, 35, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL);" );

## --------------------------------------------------------


$db->query( "CREATE TABLE IF NOT EXISTS `#__{vm}_userfield_values` (
  `fieldvalueid` int(11) NOT NULL auto_increment,
  `fieldid` int(11) NOT NULL default '0',
  `fieldtitle` varchar(255) NOT NULL default '',
  `fieldvalue` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL default '0',
  `sys` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`fieldvalueid`)
) TYPE=MyISAM COMMENT='Holds the different values for dropdown and radio lists';" );

$db->query( "INSERT INTO `#__{vm}_userfield_values` VALUES (1, 25, 'PHPSHOP_ACCOUNT_LBL_ACCOUNT_TYPE_BUSINESSCHECKING','Checking', 1, 1);" );
$db->query( "INSERT INTO `#__{vm}_userfield_values` VALUES (2, 25, 'PHPSHOP_ACCOUNT_LBL_ACCOUNT_TYPE_CHECKING', 'Business Checking', 2, 1);" );
$db->query( "INSERT INTO `#__{vm}_userfield_values` VALUES (3, 25, 'PHPSHOP_ACCOUNT_LBL_ACCOUNT_TYPE_SAVINGS', 'Savings', 3, 1);" );

## New functions, required for using the new features
$db->query( "INSERT INTO `#__{vm}_function` VALUES ('', 1, 'userfieldSave', 'ps_userfield', 'savefield', 'add or edit a user field', 'admin');" );
$db->query( "INSERT INTO `#__{vm}_function` VALUES ('', 1, 'userfieldDelete', 'ps_userfield', 'deletefield', '', 'admin');" );
$db->query( "INSERT INTO `#__{vm}_function` VALUES ('', 1, 'changeordering', 'vmAbstractObject.class', 'handleordering', '', 'admin');" );
$db->query( "INSERT INTO `#__{vm}_function` VALUES ('', 2, 'moveProduct', 'ps_product', 'move', 'Move products from one category to another.', 'admin,storeadmin');" );

# http://virtuemart.net/index.php?option=com_smf&Itemid=71&topic=17143.0
$db->query( "INSERT INTO `#__{vm}_function` VALUES ('', 7, 'productAsk', 'ps_communication', 'mail_question', 'Lets the customer send a question about a specific product.', 'none');" );
$db->query( "INSERT INTO `#__{vm}_function` VALUES ('', 7, 'recommendProduct', 'ps_communication', 'sendRecommendation', 'Lets the customer send a recommendation about a specific product to a friend.', 'none');" );

$db->query( "INSERT INTO `#__{vm}_function` VALUES ('', 8, 'ExportUpdate', 'ps_export', 'update', '', 'admin,storeadmin');" );
$db->query( "INSERT INTO `#__{vm}_function` VALUES ('', 8, 'ExportAdd', 'ps_export', 'add', '', 'admin,storeadmin');" );
$db->query( "INSERT INTO `#__{vm}_function` VALUES ('', 8, 'ExportDelete', 'ps_export', 'delete', '', 'admin,storeadmin');" );
$db->query( "INSERT INTO `#__{vm}_function` VALUES ('', 1, 'writeThemeConfig', 'ps_config', 'writeThemeConfig', 'Writes a theme configuration file.', 'admin');" );

$db->query( "ALTER TABLE `#__{vm}_payment_method` ADD `payment_method_discount_is_percent` TINYINT( 1 ) NOT NULL AFTER `payment_method_discount` ,
ADD `payment_method_discount_max_amount` DECIMAL( 10, 2 ) NOT NULL AFTER `payment_method_discount_is_percent` ,
ADD `payment_method_discount_min_amount` DECIMAL( 10, 2 ) NOT NULL AFTER `payment_method_discount_max_amount` ;");

# DHL integration
$db->query( "CREATE TABLE IF NOT EXISTS `#__vm_shipping_label` (
	`order_id` int(11) NOT NULL default '0',
	`shipper_class` varchar(32) default NULL,
	`ship_date` varchar(32) default NULL,
	`service_code` varchar(32) default NULL,
	`special_service` varchar(32) default NULL,
	`package_type` varchar(16) default NULL,
	`order_weight` decimal(10,2) default NULL,
	`is_international` tinyint(1) default NULL,
	`additional_protection_type` varchar(16) default NULL,
	`additional_protection_value` decimal(10,2) default NULL,
	`duty_value` decimal(10,2) default NULL,
	`content_desc` varchar(255) default NULL,
	`label_is_generated` tinyint(1) NOT NULL default '0',
	`tracking_number` varchar(32) default NULL,
	`label_image` blob default NULL,
	`have_signature` tinyint(1) NOT NULL default '0',
	`signature_image` blob default NULL,
	PRIMARY KEY (`order_id`)
) TYPE=MyISAM COMMENT='Stores information used in generating shipping labels'; ");

## Export Modules
$db->query( "CREATE TABLE IF NOT EXISTS `#__{vm}_export` (
  `export_id` int(11) NOT NULL auto_increment,
  `vendor_id` int(11) default NULL,
  `export_name` varchar(255) default NULL,
  `export_desc` text NOT NULL,
  `export_class` varchar(50) NOT NULL,
  `export_enabled` char(1) NOT NULL default 'N',
  `export_config` text NOT NULL,
  `iscore` tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (`export_id`)
) TYPE=MyISAM COMMENT='Export Modules';");

# NEW Countries
$db->query( "INSERT INTO `#__{vm}_country` (country_name, country_3_code, country_2_code)
VALUES
    ('East Timor', 'XET', 'XE'),
    ('Jersey', 'XJE', 'XJ'),
    ('St. Barthelemy', 'XSB', 'XB'),
    ('St. Eustatius', 'XSE', 'XU'),
    ('Canary Islands', 'XCA', 'XC');");

# STATES table; index changed and states modified; dropping to prevent UNIQUE index errors
# users should be notified of this... they will lose custom states
$db->query( "DROP TABLE `#__{vm}_state`");

## Table structure for table `#__{vm}_state`
$db->query( "CREATE TABLE IF NOT EXISTS `#__{vm}_state` (
  `state_id` int(11) NOT NULL auto_increment,
  `country_id` int(11) NOT NULL default '1',
  `state_name` varchar(64) default NULL,
  `state_3_code` char(3) default NULL,
  `state_2_code` char(2) default NULL,
  PRIMARY KEY  (`state_id`),
  UNIQUE KEY `state_3_code` (`country_id`,`state_3_code`),
  UNIQUE KEY `state_2_code` (`country_id`,`state_2_code`),
  KEY `idx_country_id` (`country_id`)
) TYPE=MyISAM COMMENT='States that are assigned to a country'; ");

## Dumping data for table `#__{vm}_state`
$db->query( "INSERT INTO `#__{vm}_state` VALUES (1, 223, 'Alabama', 'ALA', 'AL'),
(2, 223, 'Alaska', 'ALK', 'AK'),
(3, 223, 'Arizona', 'ARZ', 'AZ'),
(4, 223, 'Arkansas', 'ARK', 'AR'),
(5, 223, 'California', 'CAL', 'CA'),
(6, 223, 'Colorado', 'COL', 'CO'),
(7, 223, 'Connecticut', 'CCT', 'CT'),
(8, 223, 'Delaware', 'DEL', 'DE'),
(9, 223, 'District Of Columbia', 'DOC', 'DC'),
(10, 223, 'Florida', 'FLO', 'FL'),
(11, 223, 'Georgia', 'GEA', 'GA'),
(12, 223, 'Hawaii', 'HWI', 'HI'),
(13, 223, 'Idaho', 'IDA', 'ID'),
(14, 223, 'Illinois', 'ILL', 'IL'),
(15, 223, 'Indiana', 'IND', 'IN'),
(16, 223, 'Iowa', 'IOA', 'IA'),
(17, 223, 'Kansas', 'KAS', 'KS'),
(18, 223, 'Kentucky', 'KTY', 'KY'),
(19, 223, 'Louisiana', 'LOA', 'LA'),
(20, 223, 'Maine', 'MAI', 'ME'),
(21, 223, 'Maryland', 'MLD', 'MD'),
(22, 223, 'Massachusetts', 'MSA', 'MA'),
(23, 223, 'Michigan', 'MIC', 'MI'),
(24, 223, 'Minnesota', 'MIN', 'MN'),
(25, 223, 'Mississippi', 'MIS', 'MS'),
(26, 223, 'Missouri', 'MIO', 'MO'),
(27, 223, 'Montana', 'MOT', 'MT'),
(28, 223, 'Nebraska', 'NEB', 'NE'),
(29, 223, 'Nevada', 'NEV', 'NV'),
(30, 223, 'New Hampshire', 'NEH', 'NH'),
(31, 223, 'New Jersey', 'NEJ', 'NJ'),
(32, 223, 'New Mexico', 'NEM', 'NM'),
(33, 223, 'New York', 'NEY', 'NY'),
(34, 223, 'North Carolina', 'NOC', 'NC'),
(35, 223, 'North Dakota', 'NOD', 'ND'),
(36, 223, 'Ohio', 'OHI', 'OH'),
(37, 223, 'Oklahoma', 'OKL', 'OK'),
(38, 223, 'Oregon', 'ORN', 'OR'),
(39, 223, 'Pennsylvania', 'PEA', 'PA'),
(40, 223, 'Rhode Island', 'RHI', 'RI'),
(41, 223, 'South Carolina', 'SOC', 'SC'),
(42, 223, 'South Dakota', 'SOD', 'SD'),
(43, 223, 'Tennessee', 'TEN', 'TN'),
(44, 223, 'Texas', 'TXS', 'TX'),
(45, 223, 'Utah', 'UTA', 'UT'),
(46, 223, 'Vermont', 'VMT', 'VT'),
(47, 223, 'Virginia', 'VIA', 'VA'),
(48, 223, 'Washington', 'WAS', 'WA'),
(49, 223, 'West Virginia', 'WEV', 'WV'),
(50, 223, 'Wisconsin', 'WIS', 'WI'),
(51, 223, 'Wyoming', 'WYO', 'WY'),
(52, 38, 'Alberta', 'ALB', 'AB'),
(53, 38, 'British Columbia', 'BRC', 'BC'),
(54, 38, 'Manitoba', 'MAB', 'MB'),
(55, 38, 'New Brunswick', 'NEB', 'NB'),
(56, 38, 'Newfoundland and Labrador', 'NFL', 'NL'),
(57, 38, 'Northwest Territories', 'NWT', 'NT'),
(58, 38, 'Nova Scotia', 'NOS', 'NS'),
(59, 38, 'Nunavut', 'NUT', 'NU'),
(60, 38, 'Ontario', 'ONT', 'ON'),
(61, 38, 'Prince Edward Island', 'PEI', 'PE'),
(62, 38, 'Quebec', 'QEC', 'QC'),
(63, 38, 'Saskatchewan', 'SAK', 'SK'),
(64, 38, 'Yukon', 'YUT', 'YT'),
(65, 222, 'England', 'ENG', 'EN'),
(66, 222, 'Northern Ireland', 'NOI', 'NI'),
(67, 222, 'Scotland', 'SCO', 'SD'),
(68, 222, 'Wales', 'WLS', 'WS'),
(69, 13, 'Australian Capital Territory', 'ACT', 'AT'),
(70, 13, 'New South Wales', 'NSW', 'NW'),
(71, 13, 'Northern Territory', 'NOT', 'NT'),
(72, 13, 'Queensland', 'QLD', 'QL'),
(73, 13, 'South Australia', 'SOA', 'SA'),
(74, 13, 'Tasmania', 'TAS', 'TA'),
(75, 13, 'Victoria', 'VIC', 'VI'),
(76, 13, 'Western Australia', 'WEA', 'WA'),
(77, 138, 'Aguascalientes', 'AGS', 'AG'),
(78, 138, 'Baja California Norte', 'BCN', 'BN'),
(79, 138, 'Baja California Sur', 'BCS', 'BS'),
(80, 138, 'Campeche', 'CAM', 'CA'),
(81, 138, 'Chiapas', 'CHI', 'CS'),
(82, 138, 'Chihuahua', 'CHA', 'CH'),
(83, 138, 'Coahuila', 'COA', 'CO'),
(84, 138, 'Colima', 'COL', 'CM'),
(85, 138, 'Distrito Federal', 'DFM', 'DF'),
(86, 138, 'Durango', 'DGO', 'DO'),
(87, 138, 'Guanajuato', 'GTO', 'GO'),
(88, 138, 'Guerrero', 'GRO', 'GU'),
(89, 138, 'Hidalgo', 'HGO', 'HI'),
(90, 138, 'Jalisco', 'JAL', 'JA'),
(91, 138, 'M�xico (Estado de)', 'EDM', 'EM'),
(92, 138, 'Michoac�n', 'MCN', 'MI'),
(93, 138, 'Morelos', 'MOR', 'MO'),
(94, 138, 'Nayarit', 'NAY', 'NY'),
(95, 138, 'Nuevo Le�n', 'NUL', 'NL'),
(96, 138, 'Oaxaca', 'OAX', 'OA'),
(97, 138, 'Puebla', 'PUE', 'PU'),
(98, 138, 'Quer�taro', 'QRO', 'QU'),
(99, 138, 'Quintana Roo', 'QUR', 'QR'),
(100, 138, 'San Luis Potos�', 'SLP', 'SP'),
(101, 138, 'Sinaloa', 'SIN', 'SI'),
(102, 138, 'Sonora', 'SON', 'SO'),
(103, 138, 'Tabasco', 'TAB', 'TA'),
(104, 138, 'Tamaulipas', 'TAM', 'TM'),
(105, 138, 'Tlaxcala', 'TLX', 'TX'),
(106, 138, 'Veracruz', 'VER', 'VZ'),
(107, 138, 'Yucat�n', 'YUC', 'YU'),
(108, 138, 'Zacatecas', 'ZAC', 'ZA'),
(109, 30, 'Acre', 'ACR', 'AC'),
(110, 30, 'Alagoas', 'ALG', 'AL'),
(111, 30, 'Amap�', 'AMP', 'AP'),
(112, 30, 'Amazonas', 'AMZ', 'AM'),
(113, 30, 'Bah�a', 'BAH', 'BA'),
(114, 30, 'Cear�', 'CEA', 'CE'),
(115, 30, 'Distrito Federal', 'DFB', 'DF'),
(116, 30, 'Espirito Santo', 'ESS', 'ES'),
(117, 30, 'Goi�s', 'GOI', 'GO'),
(118, 30, 'Maranh�o', 'MAR', 'MA'),
(119, 30, 'Mato Grosso', 'MAT', 'MT'),
(120, 30, 'Mato Grosso do Sul', 'MGS', 'MS'),
(121, 30, 'Minas Gera�s', 'MIG', 'MG'),
(122, 30, 'Paran�', 'PAR', 'PR'),
(123, 30, 'Para�ba', 'PRB', 'PB'),
(124, 30, 'Par�', 'PAB', 'PA'),
(125, 30, 'Pernambuco', 'PER', 'PE'),
(126, 30, 'Piau�', 'PIA', 'PI'),
(127, 30, 'Rio Grande do Norte', 'RGN', 'RN'),
(128, 30, 'Rio Grande do Sul', 'RGS', 'RS'),
(129, 30, 'Rio de Janeiro', 'RDJ', 'RJ'),
(130, 30, 'Rond�nia', 'RON', 'RO'),
(131, 30, 'Roraima', 'ROR', 'RR'),
(132, 30, 'Santa Catarina', 'SAC', 'SC'),
(133, 30, 'Sergipe', 'SER', 'SE'),
(134, 30, 'S�o Paulo', 'SAP', 'SP'),
(135, 30, 'Tocantins', 'TOC', 'TO'),
(NULL, 44, 'Anhui', 'ANH', '34'),
(NULL, 44, 'Beijing', 'BEI', '11'),
(NULL, 44, 'Chongqing', 'CHO', '50'),
(NULL, 44, 'Fujian', 'FUJ', '35'),
(NULL, 44, 'Gansu', 'GAN', '62'),
(NULL, 44, 'Guangdong', 'GUA', '44'),
(NULL, 44, 'Guangxi Zhuang', 'GUZ', '45'),
(NULL, 44, 'Guizhou', 'GUI', '52'),
(NULL, 44, 'Hainan', 'HAI', '46'),
(NULL, 44, 'Hebei', 'HEB', '13'),
(NULL, 44, 'Heilongjiang', 'HEI', '23'),
(NULL, 44, 'Henan', 'HEN', '41'),
(NULL, 44, 'Hubei', 'HUB', '42'),
(NULL, 44, 'Hunan', 'HUN', '43'),
(NULL, 44, 'Jiangsu', 'JIA', '32'),
(NULL, 44, 'Jiangxi', 'JIX', '36'),
(NULL, 44, 'Jilin', 'JIL', '22'),
(NULL, 44, 'Liaoning', 'LIA', '21'),
(NULL, 44, 'Nei Mongol', 'NML', '15'),
(NULL, 44, 'Ningxia Hui', 'NIH', '64'),
(NULL, 44, 'Qinghai', 'QIN', '63'),
(NULL, 44, 'Shandong', 'SNG', '37'),
(NULL, 44, 'Shanghai', 'SHH', '31'),
(NULL, 44, 'Shaanxi', 'SHX', '61'),
(NULL, 44, 'Sichuan', 'SIC', '51'),
(NULL, 44, 'Tianjin', 'TIA', '12'),
(NULL, 44, 'Xinjiang Uygur', 'XIU', '65'),
(NULL, 44, 'Xizang', 'XIZ', '54'),
(NULL, 44, 'Yunnan', 'YUN', '53'),
(NULL, 44, 'Zhejiang', 'ZHE', '33'),
(NULL, 104, 'Gaza Strip', 'GZS', 'GZ'),
(NULL, 104, 'West Bank', 'WBK', 'WB'),
(NULL, 104, 'Other', 'OTH', 'OT'),
(NULL, 151, 'St. Maarten', 'STM', 'SM'),
(NULL, 151, 'Bonaire', 'BNR', 'BN'),
(NULL, 151, 'Curacao', 'CUR', 'CR'),
(NULL, 175, 'Alba', 'ABA', 'AB'),
(NULL, 175, 'Arad', 'ARD', 'AR'),
(NULL, 175, 'Arges', 'ARG', 'AG'),
(NULL, 175, 'Bacau', 'BAC', 'BC'),
(NULL, 175, 'Bihor', 'BIH', 'BH'),
(NULL, 175, 'Bistrita-Nasaud', 'BIS', 'BN'),
(NULL, 175, 'Botosani', 'BOT', 'BT'),
(NULL, 175, 'Braila', 'BRL', 'BR'),
(NULL, 175, 'Brasov', 'BRA', 'BV'),
(NULL, 175, 'Bucuresti', 'BUC', 'B'),
(NULL, 175, 'Buzau', 'BUZ', 'BZ'),
(NULL, 175, 'Calarasi', 'CAL', 'CL'),
(NULL, 175, 'Caras Severin', 'CRS', 'CS'),
(NULL, 175, 'Cluj', 'CLJ', 'CJ'),
(NULL, 175, 'Constanta', 'CST', 'CT'),
(NULL, 175, 'Covasna', 'COV', 'CV'),
(NULL, 175, 'Dambovita', 'DAM', 'DB'),
(NULL, 175, 'Dolj', 'DLJ', 'DJ'),
(NULL, 175, 'Galati', 'GAL', 'GL'),
(NULL, 175, 'Giurgiu', 'GIU', 'GR'),
(NULL, 175, 'Gorj', 'GOR', 'GJ'),
(NULL, 175, 'Hargita', 'HRG', 'HR'),
(NULL, 175, 'Hunedoara', 'HUN', 'HD'),
(NULL, 175, 'Ialomita', 'IAL', 'IL'),
(NULL, 175, 'Iasi', 'IAS', 'IS'),
(NULL, 175, 'Ilfov', 'ILF', 'IF'),
(NULL, 175, 'Maramures', 'MAR', 'MM'),
(NULL, 175, 'Mehedinti', 'MEH', 'MH'),
(NULL, 175, 'Mures', 'MUR', 'MS'),
(NULL, 175, 'Neamt', 'NEM', 'NT'),
(NULL, 175, 'Olt', 'OLT', 'OT'),
(NULL, 175, 'Prahova', 'PRA', 'PH'),
(NULL, 175, 'Salaj', 'SAL', 'SJ'),
(NULL, 175, 'Satu Mare', 'SAT', 'SM'),
(NULL, 175, 'Sibiu', 'SIB', 'SB'),
(NULL, 175, 'Suceava', 'SUC', 'SV'),
(NULL, 175, 'Teleorman', 'TEL', 'TR'),
(NULL, 175, 'Timis', 'TIM', 'TM'),
(NULL, 175, 'Tulcea', 'TUL', 'TL'),
(NULL, 175, 'Valcea', 'VAL', 'VL'),
(NULL, 175, 'Vaslui', 'VAS', 'VS'),
(NULL, 175, 'Vrancea', 'VRA', 'VN'),
(NULL, 105, 'Agrigento', 'AGR', 'AG'),
(NULL, 105, 'Alessandria', 'ALE', 'AL'),
(NULL, 105, 'Ancona', 'ANC', 'AN'), 
(NULL, 105, 'Aosta', 'AOS', 'AO'),
(NULL, 105, 'Arezzo', 'ARE', 'AR'),
(NULL, 105, 'Ascoli Piceno', 'API', 'AP'),
(NULL, 105, 'Asti', 'AST', 'AT'),
(NULL, 105, 'Avellino', 'AVE', 'AV'),
(NULL, 105, 'Bari', 'BAR', 'BA'),
(NULL, 105, 'Belluno', 'BEL', 'BL'),
(NULL, 105, 'Benevento', 'BEN', 'BN'),
(NULL, 105, 'Bergamo', 'BEG', 'BG'),
(NULL, 105, 'Biella', 'BIE', 'BI'),
(NULL, 105, 'Bologna', 'BOL', 'BO'),
(NULL, 105, 'Bolzano', 'BOZ', 'BZ'),
(NULL, 105, 'Brescia', 'BRE', 'BS'),
(NULL, 105, 'Brindisi', 'BRI', 'BR'),
(NULL, 105, 'Cagliari', 'CAG', 'CA'),
(NULL, 105, 'Caltanissetta', 'CAL', 'CL'),
(NULL, 105, 'Campobasso', 'CBO', 'CB'),
(NULL, 105, 'Carbonia-Iglesias', 'CAR', 'CI'),
(NULL, 105, 'Caserta', 'CAS', 'CE'),
(NULL, 105, 'Catania', 'CAT', 'CT'),
(NULL, 105, 'Catanzaro', 'CTZ', 'CZ'),
(NULL, 105, 'Chieti', 'CHI', 'CH'),
(NULL, 105, 'Como', 'COM', 'CO'),
(NULL, 105, 'Cosenza', 'COS', 'CS'),
(NULL, 105, 'Cremona', 'CRE', 'CR'),
(NULL, 105, 'Crotone', 'CRO', 'KR'),
(NULL, 105, 'Cuneo', 'CUN', 'CN'),
(NULL, 105, 'Enna', 'ENN', 'EN'),
(NULL, 105, 'Ferrara', 'FER', 'FE'),
(NULL, 105, 'Firenze', 'FIR', 'FI'),
(NULL, 105, 'Foggia', 'FOG', 'FG'),
(NULL, 105, 'Forli-Cesena', 'FOC', 'FC'),
(NULL, 105, 'Frosinone', 'FRO', 'FR'),
(NULL, 105, 'Genova', 'GEN', 'GE'),
(NULL, 105, 'Gorizia', 'GOR', 'GO'),
(NULL, 105, 'Grosseto', 'GRO', 'GR'),
(NULL, 105, 'Imperia', 'IMP', 'IM'),
(NULL, 105, 'Isernia', 'ISE', 'IS'),
(NULL, 105, 'L\'Aquila', 'AQU', 'AQ'),
(NULL, 105, 'La Spezia', 'LAS', 'SP'),
(NULL, 105, 'Latina', 'LAT', 'LT'),
(NULL, 105, 'Lecce', 'LEC', 'LE'),
(NULL, 105, 'Lecco', 'LCC', 'LC'),
(NULL, 105, 'Livorno', 'LIV', 'LI'),
(NULL, 105, 'Lodi', 'LOD', 'LO'),
(NULL, 105, 'Lucca', 'LUC', 'LU'),
(NULL, 105, 'Macerata', 'MAC', 'MC'),
(NULL, 105, 'Mantova', 'MAN', 'MN'),
(NULL, 105, 'Massa-Carrara', 'MAS', 'MS'),
(NULL, 105, 'Matera', 'MAA', 'MT'),
(NULL, 105, 'Medio Campidano', 'MED','VS'),
(NULL, 105, 'Messina', 'MES', 'ME'),
(NULL, 105, 'Milano', 'MIL', 'MI'),
(NULL, 105, 'Modena', 'MOD', 'MO'),
(NULL, 105, 'Napoli', 'NAP', 'NA'),
(NULL, 105, 'Novara', 'NOV', 'NO'),
(NULL, 105, 'Nuoro', 'NUR', 'NU'),
(NULL, 105, 'Ogliastra', 'OGL', 'OG'),
(NULL, 105, 'Olbia-Tempio', 'OLB', 'OT'),
(NULL, 105, 'Oristano', 'ORI', 'OR'),
(NULL, 105, 'Padova', 'PDA', 'PD'),
(NULL, 105, 'Palermo', 'PAL', 'PA'),
(NULL, 105, 'Parma', 'PAA', 'PR'),
(NULL, 105, 'Pavia', 'PAV', 'PV'),
(NULL, 105, 'Perugia', 'PER', 'PG'),
(NULL, 105, 'Pesaro e Urbino', 'PES', 'PU'),
(NULL, 105, 'Pescara', 'PSC', 'PE'),
(NULL, 105, 'Piacenza', 'PIA', 'PC'),
(NULL, 105, 'Pisa', 'PIS', 'PI'),
(NULL, 105, 'Pistoia', 'PIT', 'PT'),
(NULL, 105, 'Pordenone', 'POR', 'PN'),
(NULL, 105, 'Potenza', 'PTZ', 'PZ'),
(NULL, 105, 'Prato', 'PRA', 'PO'),
(NULL, 105, 'Ragusa', 'RAG', 'RG'),
(NULL, 105, 'Ravenna', 'RAV', 'RA'),
(NULL, 105, 'Reggio Calabria', 'REG', 'RC'),
(NULL, 105, 'Reggio Emilia', 'REE', 'RE'),
(NULL, 105, 'Rieti', 'RIE', 'RI'),
(NULL, 105, 'Rimini', 'RIM','RN'),
(NULL, 105, 'Roma', 'ROM', 'RM'),
(NULL, 105, 'Rovigo', 'ROV', 'RO'),
(NULL, 105, 'Salerno', 'SAL', 'SA'),
(NULL, 105, 'Sassari', 'SAS', 'SS'),
(NULL, 105, 'Savona', 'SAV', 'SV'),
(NULL, 105, 'Siena', 'SIE', 'SI'),
(NULL, 105, 'Siracusa', 'SIR', 'SR'),
(NULL, 105, 'Sondrio', 'SOO', 'SO'),
(NULL, 105, 'Taranto', 'TAR', 'TA'),
(NULL, 105, 'Teramo', 'TER', 'TE'),
(NULL, 105, 'Terni', 'TRN', 'TR'),
(NULL, 105, 'Torino', 'TOR', 'TO'),
(NULL, 105, 'Trapani', 'TRA', 'TP'),
(NULL, 105, 'Trento', 'TRE', 'TN'),
(NULL, 105, 'Treviso', 'TRV', 'TV'),
(NULL, 105, 'Trieste', 'TRI', 'TS'),
(NULL, 105, 'Udine', 'UDI', 'UD'),
(NULL, 105, 'Varese', 'VAR', 'VA'),
(NULL, 105, 'Venezia', 'VEN', 'VE'),
(NULL, 105, 'Verbano Cusio Ossola', 'VCO', 'VB'),
(NULL, 105, 'Vercelli', 'VER', 'VC'),
(NULL, 105, 'Verona', 'VRN', 'VR'),
(NULL, 105, 'Vibo Valenzia', 'VIV', 'VV'),
(NULL, 105, 'Vicenza', 'VII', 'VI'),
(NULL, 105, 'Viterbo', 'VIT', 'VT'),
(NULL, 195, 'A Coru�a', 'ACOR', '15'),
(NULL, 195, 'Alava', 'ALA', '01'),
(NULL, 195, 'Albacete', 'ALB', '02'),
(NULL, 195, 'Alicante', 'ALI', '03'),
(NULL, 195, 'Almeria', 'ALM', '04'),
(NULL, 195, 'Asturias', 'AST', '33'),
(NULL, 195, 'Avila', 'AVI', '05'),
(NULL, 195, 'Badajoz', 'BAD', '06'),
(NULL, 195, 'Baleares', 'BAL', '07'),
(NULL, 195, 'Barcelona', 'BAR', '08'),
(NULL, 195, 'Burgos', 'BUR', '09'),
(NULL, 195, 'Caceres', 'CAC', '10'),
(NULL, 195, 'Cadiz', 'CAD', '11'),
(NULL, 195, 'Cantabria', 'CAN', '39'),
(NULL, 195, 'Castellon', 'CAS', '12'),
(NULL, 195, 'Ceuta', 'CEU', '51'),
(NULL, 195, 'Ciudad Real', 'CIU', '13'),
(NULL, 195, 'Cordoba', 'COR', '14'),
(NULL, 195, 'Cuenca', 'CUE', '16'),
(NULL, 195, 'Girona', 'GIR', '17'),
(NULL, 195, 'Granada', 'GRA', '18'),
(NULL, 195, 'Guadalajara', 'GUA', '19'),
(NULL, 195, 'Guipuzcoa', 'GUI', '20'),
(NULL, 195, 'Huelva', 'HUL', '21'),
(NULL, 195, 'Huesca', 'HUS', '22'),
(NULL, 195, 'Jaen', 'JAE', '23'),
(NULL, 195, 'La Rioja', 'LRI', '26'),
(NULL, 195, 'Las Palmas', 'LPA', '35'),
(NULL, 195, 'Leon', 'LEO', '24'),
(NULL, 195, 'Lleida', 'LLE', '25'),
(NULL, 195, 'Lugo', 'LUG', '27'),
(NULL, 195, 'Madrid', 'MAD', '28'),
(NULL, 195, 'Malaga', 'MAL', '29'),
(NULL, 195, 'Melilla', 'MEL', '52'),
(NULL, 195, 'Murcia', 'MUR', '30'),
(NULL, 195, 'Navarra', 'NAV', '31'),
(NULL, 195, 'Ourense', 'OUR', '32'),
(NULL, 195, 'Palencia', 'PAL', '34'),
(NULL, 195, 'Pontevedra', 'PON', '36'),
(NULL, 195, 'Salamanca', 'SAL', '37'),
(NULL, 195, 'Santa Cruz de Tenerife', 'SCT', '38'),
(NULL, 195, 'Segovia', 'SEG', '40'),
(NULL, 195, 'Sevilla', 'SEV', '41'),
(NULL, 195, 'Soria', 'SOR', '42'),
(NULL, 195, 'Tarragona', 'TAR', '43'),
(NULL, 195, 'Teruel', 'TER', '44'),
(NULL, 195, 'Toledo', 'TOL', '45'),
(NULL, 195, 'Valencia', 'VAL', '46'),
(NULL, 195, 'Valladolid', 'VLL', '47'),
(NULL, 195, 'Vizcaya', 'VIZ', '48'),
(NULL, 195, 'Zamora', 'ZAM', '49'),
(NULL, 195, 'Zaragoza', 'ZAR', '50'),
(NULL, 11, 'Aragatsotn', 'ARG', 'AG'),
(NULL, 11, 'Ararat', 'ARR', 'AR'),
(NULL, 11, 'Armavir', 'ARM', 'AV'),
(NULL, 11, 'Gegharkunik', 'GEG', 'GR'),
(NULL, 11, 'Kotayk', 'KOT', 'KT'),
(NULL, 11, 'Lori', 'LOR', 'LO'),
(NULL, 11, 'Shirak', 'SHI', 'SH'),
(NULL, 11, 'Syunik', 'SYU', 'SU'),
(NULL, 11, 'Tavush', 'TAV', 'TV'),
(NULL, 11, 'Vayots-Dzor', 'VAD', 'VD'),
(NULL, 11, 'Yerevan', 'YER', 'ER'),
(NULL, 99, 'Andaman & Nicobar Islands', 'ANI', 'AI'),
(NULL, 99, 'Andhra Pradesh', 'AND', 'AN'),
(NULL, 99, 'Arunachal Pradesh', 'ARU', 'AR'),
(NULL, 99, 'Assam', 'ASS', 'AS'),
(NULL, 99, 'Bihar', 'BIH', 'BI'),
(NULL, 99, 'Chandigarh', 'CHA', 'CA'),
(NULL, 99, 'Chhatisgarh', 'CHH', 'CH'),
(NULL, 99, 'Dadra & Nagar Haveli', 'DAD', 'DD'),
(NULL, 99, 'Daman & Diu', 'DAM', 'DA'),
(NULL, 99, 'Delhi', 'DEL', 'DE'),
(NULL, 99, 'Goa', 'GOA', 'GO'),
(NULL, 99, 'Gujarat', 'GUJ', 'GU'),
(NULL, 99, 'Haryana', 'HAR', 'HA'),
(NULL, 99, 'Himachal Pradesh', 'HIM', 'HI'),
(NULL, 99, 'Jammu & Kashmir', 'JAM', 'JA'),
(NULL, 99, 'Jharkhand', 'JHA', 'JH'),
(NULL, 99, 'Karnataka', 'KAR', 'KA'),
(NULL, 99, 'Kerala', 'KER', 'KE'),
(NULL, 99, 'Lakshadweep', 'LAK', 'LA'),
(NULL, 99, 'Madhya Pradesh', 'MAD', 'MD'),
(NULL, 99, 'Maharashtra', 'MAH', 'MH'),
(NULL, 99, 'Manipur', 'MAN', 'MN'),
(NULL, 99, 'Meghalaya', 'MEG', 'ME'),
(NULL, 99, 'Mizoram', 'MIZ', 'MI'),
(NULL, 99, 'Nagaland', 'NAG', 'NA'),
(NULL, 99, 'Orissa', 'ORI', 'OR'),
(NULL, 99, 'Pondicherry', 'PON', 'PO'),
(NULL, 99, 'Punjab', 'PUN', 'PU'),
(NULL, 99, 'Rajasthan', 'RAJ', 'RA'),
(NULL, 99, 'Sikkim', 'SIK', 'SI'),
(NULL, 99, 'Tamil Nadu', 'TAM', 'TA'),
(NULL, 99, 'Tripura', 'TRI', 'TR'),
(NULL, 99, 'Uttaranchal', 'UAR', 'UA'),
(NULL, 99, 'Uttar Pradesh', 'UTT', 'UT'),
(NULL, 99, 'West Bengal', 'WES', 'WE'),
(NULL, 101, 'Ahmadi va Kohkiluyeh', 'BOK', 'BO'),
(NULL, 101, 'Ardabil', 'ARD', 'AR'),
(NULL, 101, 'Azarbayjan-e Gharbi', 'AZG', 'AG'),
(NULL, 101, 'Azarbayjan-e Sharqi', 'AZS', 'AS'),
(NULL, 101, 'Bushehr', 'BUS', 'BU'),
(NULL, 101, 'Chaharmahal va Bakhtiari', 'CMB', 'CM'),
(NULL, 101, 'Esfahan', 'ESF', 'ES'),
(NULL, 101, 'Fars', 'FAR', 'FA'),
(NULL, 101, 'Gilan', 'GIL', 'GI'),
(NULL, 101, 'Gorgan', 'GOR', 'GO'),
(NULL, 101, 'Hamadan', 'HAM', 'HA'),
(NULL, 101, 'Hormozgan', 'HOR', 'HO'),
(NULL, 101, 'Ilam', 'ILA', 'IL'),
(NULL, 101, 'Kerman', 'KER', 'KE'),
(NULL, 101, 'Kermanshah', 'BAK', 'BA'),
(NULL, 101, 'Khorasan-e Junoubi', 'KHJ', 'KJ'),
(NULL, 101, 'Khorasan-e Razavi', 'KHR', 'KR'),
(NULL, 101, 'Khorasan-e Shomali', 'KHS', 'KS'),
(NULL, 101, 'Khuzestan', 'KHU', 'KH'),
(NULL, 101, 'Kordestan', 'KOR', 'KO'),
(NULL, 101, 'Lorestan', 'LOR', 'LO'),
(NULL, 101, 'Markazi', 'MAR', 'MR'),
(NULL, 101, 'Mazandaran', 'MAZ', 'MZ'),
(NULL, 101, 'Qazvin', 'QAS', 'QA'),
(NULL, 101, 'Qom', 'QOM', 'QO'),
(NULL, 101, 'Semnan', 'SEM', 'SE'),
(NULL, 101, 'Sistan va Baluchestan', 'SBA', 'SB'),
(NULL, 101, 'Tehran', 'TEH', 'TE'),
(NULL, 101, 'Yazd', 'YAZ', 'YA'),
(NULL, 101, 'Zanjan', 'ZAN', 'ZA'); " );

# NEW Currencies
$db->query( "INSERT INTO `#__{vm}_currency` (currency_name, currency_code)
VALUES
    ('Armenian Dram', 'AMD'),
    ('Peruvian Nuevo Sol', 'PEN');");

# 10.04.2006
$db->query( "ALTER TABLE `#__{vm}_product_reviews` ADD `review_id` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;");
$db->query( "ALTER TABLE `#__{vm}_product_reviews` ADD `published` CHAR( 1 ) NOT NULL DEFAULT 'Y';");
$db->query( "ALTER TABLE `#__{vm}_product_reviews` ADD UNIQUE ( `product_id` , `userid` ); ");

$db->query( "ALTER TABLE `#__{vm}_product_votes` ADD PRIMARY KEY ( `product_id` )");
$db->query( "ALTER TABLE `#__{vm}_zone_shipping` DROP INDEX `zone_id` ");

$db->query( "ALTER TABLE `#__{vm}_product_attribute` ADD `attribute_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;");

# 02.05.2006 Multi-Currency Feature
$db->query( "ALTER TABLE `#__{vm}_vendor` ADD `vendor_accepted_currencies` TEXT NOT NULL " );

# 12.09.2006 improve category listing performance
$db->query( "ALTER TABLE `#__{vm}_category_xref` DROP INDEX `category_xref_category_child_id`;" );
$db->query( "ALTER TABLE `#__{vm}_category_xref` ADD PRIMARY KEY ( `category_child_id` ) ;" );

#13.09.2006 Allow Order Status Descriptions
$db->query( "ALTER TABLE `#__{vm}_order_status` ADD `order_status_description` TEXT NOT NULL AFTER `order_status_name`");

# 06.11.2006 Allow coupon code tracking
$db->query( "ALTER TABLE `#__{vm}_orders` ADD `coupon_code` VARCHAR( 32 ) NULL AFTER `coupon_discount`");

# 08.11.2006 Allowing new user groups
$db->query( "CREATE TABLE `#__{vm}_auth_group` (
	  `group_id` int(11) NOT NULL auto_increment,
	  `group_name` varchar(128) default NULL,
	  `group_level` int(11) default NULL,
	  PRIMARY KEY  (`group_id`)
	) TYPE=MyISAM AUTO_INCREMENT=5 COMMENT='Holds all the user groups' ;");

# these are the default user groups
$db->query( "INSERT INTO `#__{vm}_auth_group` (`group_id`, `group_name`, `group_level`) VALUES (1, 'admin', 0),(2, 'storeadmin', 250),(3, 'shopper', 500),(4, 'demo', 750);" );
		
$db->query( "CREATE TABLE `#__{vm}_auth_user_group` (
	  `user_id` int(11) NOT NULL default '0',
	  `group_id` int(11) default NULL,
	  PRIMARY KEY  (`user_id`)
	) TYPE=MyISAM COMMENT='Maps the user to user groups';");
# insert the user <=> group relationship
$db->query( "INSERT INTO `#__{vm}_auth_user_group` 
				SELECT user_id, 
					CASE `perms` 
					    WHEN 'admin' THEN 0
					    WHEN 'storeadmin' THEN 1
					    WHEN 'shopper' THEN 2
					    WHEN 'demo' THEN 3
					    ELSE 2 
					END
				FROM #__{vm}_user_info
				WHERE address_type='BT';");

$db->query( "INSERT INTO `#__{vm}_function` VALUES 
	(NULL, 1, 'usergroupAdd', 'usergroup.class', 'add', 'Add a new user group', 'admin'),
	(NULL, 1, 'usergroupUpdate', 'usergroup.class', 'update', 'Update an user group', 'admin'),
	(NULL, 1, 'usergroupDelete', 'usergroup.class', 'delete', 'Delete an user group', 'admin');" );

# Marks Child list options
$db->query( "ALTER TABLE `#__{vm}_product` ADD `child_options` varchar(45) default NULL" );
$db->query( "ALTER TABLE `#__{vm}_product` ADD `quantity_options` varchar(45) default NULL" );
$db->query( "ALTER TABLE `#__{vm}_product` ADD  `child_option_ids` varchar(45) default NULL" );
$db->query( "ALTER TABLE `#__{vm}_product` ADD  `product_order_levels` varchar(45) default NULL" );
# Update module and function permissions directly from the lists
$db->query( "INSERT INTO `#__{vm}_function` (`function_id`, `module_id`, `function_name`, `function_class`, `function_method`, `function_description`, `function_perms`) VALUES (null, 1, 'setModulePermissions', 'ps_module', 'update_permissions', '', 'admin'),
(null, 1, 'setFunctionPermissions', 'ps_function', 'update_permissions', '', 'admin')");

# Re-enable downloads and resend Download ID
$db->query( "INSERT INTO `#__{vm}_function` (`function_id`, `module_id`, `function_name`, `function_class`, `function_method`, `function_description`, `function_perms`) 
				VALUES 
				(NULL, 2, 'insertDownloadsForProduct', 'ps_order', 'insert_downloads_for_product', '', 'admin'),
				(NULL, 5, 'mailDownloadId', 'ps_order', 'mail_download_id', '', 'storeadmin,admin')" );

# 12.04.2007 Cart Storage for registered users
$db->query( "CREATE TABLE IF NOT EXISTS `#__{vm}_cart` (
`user_id` INT( 11 ) NOT NULL ,
`cart_content` TEXT NOT NULL ,
`last_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
PRIMARY KEY ( `user_id` )
) TYPE = MYISAM COMMENT = 'Stores the cart contents of a user'" );

$db->query( "ALTER TABLE `#__{vm}_product_reviews` CHANGE `product_id` `product_id` INT( 11 ) NOT NULL ");

# 25.07.2007: Allow to set address and date format
$db->query( "ALTER TABLE `#__{vm}_vendor` 
				ADD `vendor_address_format` TEXT NOT NULL ,
				ADD `vendor_date_format` VARCHAR( 255 ) NOT NULL ;" );
$db->query( "UPDATE `#__{vm}_vendor` SET
			`vendor_address_format` = '{storename}\n{address_1}\n{address_2}\n{city}, {zip}',
			`vendor_date_format` = '%A, %d %B %Y %H:%M'
			WHERE vendor_id=1;");

# VirtueMart Updater Functions
$db->query( "INSERT INTO `#__{vm}_function` (`function_id` ,`module_id` ,`function_name` ,`function_class` ,`function_method` ,`function_description` ,`function_perms`)
VALUES ( NULL , '1', 'getupdatepackage', 'update.class', 'getPatchPackage', 'Retrieves the Patch Package from the virtuemart.net Servers.', 'admin'), 
(NULL , '1', 'applypatchpackage', 'update.class', 'applyPatch', 'Applies the Patch using the instructions from the update.xml file in the downloaded patch.', 'admin'),
(NULL, 1, 'removePatchPackage', 'update.class', 'removePackageFile', 'Removes  a Patch Package File and its extracted contents.', 'admin')");

#  Incorrect Product Type parameter separator in product_type table [http://dev.virtuemart.net/cb/issue/1648]
$db->query( 'SELECT pt.product_type_id, tp.parameter_name
						FROM `#__{vm}_product_type` pt
						LEFT JOIN `#__{vm}_product_type_parameter` tp ON pt.product_type_id = tp.product_type_id
						ORDER BY pt.product_type_id');
$dbpt = new ps_DB();
while( $db->next_record()) {
	// Replace commas with semicolons in every product type parameter's value list
	$dbpt->query( 'UPDATE `#__{vm}_product_type_'.(int)$db->f('product_type_id').'` 
							SET `'.$db->f('parameter_name', false).'`= REPLACE(`'.$db->f('parameter_name', false).'`, \',\', \';\');' );
}

$db->query( "UPDATE `#__components` SET `params` = 'RELEASE=1.1.0\nDEV_STATUS=RC3' WHERE `name` = 'virtuemart_version'");

?>

-- VirtueMart table SQL script 
-- This will install all the tables need to run VirtueMart

-- --------------------------------------------------------


--
-- Table structure for table `#__virtuemart_calcs`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_calcs` (
  `virtuemart_calc_id` SERIAL,
  `virtuemart_vendor_id` int(11) NOT NULL COMMENT 'Belongs to vendor',
  `calc_name` text NOT NULL COMMENT 'Name of the rule',
  `calc_descr` text NOT NULL COMMENT 'Description',
  `calc_kind` text NOT NULL COMMENT 'Discount/Tax/Margin/Commission',
  `calc_value_mathop` text NOT NULL COMMENT 'the mathematical operation like (+,-,+%,-%)',
  `calc_value` float NOT NULL DEFAULT '0' COMMENT 'The Amount',
  `calc_currency` char(3) NOT NULL DEFAULT '0' COMMENT 'Currency of the Rule',
  `ordering` tinyint(2) NOT NULL,
  `calc_shopper_published` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Visible for Shoppers',
  `calc_vendor_published` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Visible for Vendors',
  `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Startdate if nothing is set = permanent',
  `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Enddate if nothing is set = permanent',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `calc_qualify` int(11) NOT NULL DEFAULT '0' COMMENT 'qualifying productId''s',
  `calc_affected` int(11) NOT NULL DEFAULT '0' COMMENT 'affected productId''s',
  `calc_amount_cond` float NOT NULL COMMENT 'Number of affected products',
  `calc_amount_dimunit` text NOT NULL COMMENT 'The dimension, kg, m, €',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `shared` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Affects all vendors',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`virtuemart_calc_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_calc_categories`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_calc_categories` (
  `id` SERIAL,
  `virtuemart_calc_id` int(11) NOT NULL DEFAULT '0',
  `virtuemart_category_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_virtuemart_calc_id` (`virtuemart_calc_id`,`virtuemart_category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_calc_shoppergroups`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_calc_shoppergroups` (
  `id` SERIAL,
  `virtuemart_calc_id` int(11) NOT NULL DEFAULT '0',
  `virtuemart_shoppergroup_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_virtuemart_calc_id` (`virtuemart_calc_id`,`virtuemart_shoppergroup_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_calc_countries`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_calc_countries` (
  `id` SERIAL,
  `virtuemart_calc_id` int(11) NOT NULL DEFAULT '0',
  `virtuemart_country_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_virtuemart_calc_id` (`virtuemart_calc_id`,`virtuemart_country_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_state_ids`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_state_ids` (
  `id` SERIAL,
  `virtuemart_calc_id` int(11) NOT NULL DEFAULT '0',
  `virtuemart_state_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_virtuemart_calc_id` (`virtuemart_calc_id`,`virtuemart_state_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_carts`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_carts` (
  `virtuemart_user_id` int(11) NOT NULL,
  `cart_content` text NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`virtuemart_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores the cart contents of a user';

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_categories`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_categories` (
  `virtuemart_category_id` SERIAL,
  `vendor_id` int(11) NOT NULL DEFAULT '0',
  `category_name` varchar(128) NOT NULL DEFAULT '',
  `category_description` text,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `category_template` varchar(255) DEFAULT NULL,
  `category_layout` varchar(255) DEFAULT NULL,
  `category_product_layout` varchar(255) DEFAULT NULL,
  `products_per_row` tinyint(2) NOT NULL DEFAULT '1',
  `ordering` int(11) DEFAULT '0',
  `limit_list_start` int(11) DEFAULT NULL,
  `limit_list_step` int(11) DEFAULT NULL,
  `limit_list_max` int(11) DEFAULT NULL,
  `limit_list_initial` int(11) DEFAULT NULL,
  `metadesc` text NOT NULL,
  `metakey` text NOT NULL,
  `metarobot` text NOT NULL,
  `metaauthor` text NOT NULL,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`virtuemart_category_id`),
  KEY `idx_category_vendor_id` (`vendor_id`),
  KEY `idx_category_name` (`category_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Product Categories are stored here' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_category_categories`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_category_categories` (
  `category_parent_id` int(11) NOT NULL DEFAULT '0',
  `category_child_id` int(11) NOT NULL DEFAULT '0',
  `category_list` int(11) DEFAULT NULL,
  `category_shared` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`category_child_id`),
  KEY `category_xref_category_parent_id` (`category_parent_id`),
  KEY `idx_category_xref_category_list` (`category_list`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Category child-parent relation list';

--
-- Table structure for table `#__virtuemart_category_medias`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_category_medias` (
  `id` SERIAL,
  `virtuemart_category_id` int(11) NOT NULL DEFAULT '0',
  `file_ids` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_configs`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_configs` (
  `config_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `config` text,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`config_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds configuration settings' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_countries`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_countries` (
  `virtuemart_country_id` SERIAL,
  `zone_id` int(11) NOT NULL DEFAULT '1',
  `country_name` varchar(64) DEFAULT NULL,
  `country_3_code` char(3) DEFAULT NULL,
  `country_2_code` char(2) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`virtuemart_country_id`),
  KEY `idx_country_name` (`country_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Country records' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_coupons`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_coupons` (
  `coupon_id` int(16) NOT NULL AUTO_INCREMENT,
  `coupon_code` varchar(32) NOT NULL DEFAULT '',
  `percent_or_total` enum('percent','total') NOT NULL DEFAULT 'percent',
  `coupon_type` enum('gift','permanent') NOT NULL DEFAULT 'gift',
  `coupon_value` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `coupon_start_date` datetime DEFAULT NULL,
  `coupon_expiry_date` datetime DEFAULT NULL,
  `coupon_value_valid` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`coupon_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Used to store coupon codes' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_creditcards`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_creditcards` (
  `creditcard_id` SERIAL,
  `vendor_id` int(11) NOT NULL DEFAULT '0',
  `creditcard_name` varchar(70) NOT NULL DEFAULT '',
  `creditcard_code` varchar(30) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`creditcard_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Used to store credit card types' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_currencies`
-- 


CREATE TABLE IF NOT EXISTS `#__virtuemart_currencies` (
  `currency_id` SERIAL,
  `vendor_id` int(11) NOT NULL DEFAULT '1',
  `currency_name` varchar(64) DEFAULT NULL,
  `currency_code_2` char(2) DEFAULT NULL,
  `currency_code` char(3) DEFAULT NULL,
  `currency_numeric_code` int(4) NOT NULL,
  `currency_symbol` char(4) DEFAULT NULL,
  `exchange_rate` float DEFAULT NULL,
  `display_style` varchar(128) DEFAULT NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `shared` tinyint(1) NOT NULL DEFAULT '1',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`currency_id`),
  KEY `idx_currency_code` (`currency_code`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Used to store currencies';
-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_customs`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_customs` (
 
  `custom_id` SERIAL,
  `custom_parent_id` int(11) NOT NULL DEFAULT '0',
  `admin_only` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:Display in admin only',
  `custom_title` char(255) NOT NULL COMMENT 'field title',
  `custom_tip` char(255) NOT NULL COMMENT 'tip',
  `custom_value` char(255) DEFAULT NULL COMMENT 'defaut value',
  `custom_field_desc` char(255) DEFAULT NULL COMMENT 'description or unit',
  `field_type` char(1) NOT NULL DEFAULT 'S' COMMENT 'S:string,I:int,P:parent, B:bool,D:date,T:time,H:hidden',
  `is_list` tinyint(1) DEFAULT '0' COMMENT 'list of values',
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1:hidden',
  `is_cart_attribute` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Add attributes to cart',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`custom_id`),
  KEY `idx_custom_parent_id` (`custom_parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='custom fields definition' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure `#__virtuemart_customfields`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_customfields` (
  `custom_field_id` SERIAL COMMENT 'field id',
  `custom_id` int(11) NOT NULL COMMENT 'custom group id',
  `custom_value` char(255) DEFAULT NULL COMMENT 'field value',
  `custom_price` char(255) DEFAULT NULL COMMENT 'price',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`custom_field_id`),
  KEY `idx_custom_id` (`custom_id`),
  KEY `idx_custom_value` (`custom_value`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='custom fields' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
--
-- Table structure `#__virtuemart_product_customfields`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_customfields` (
  `id` SERIAL,
  `product_id` int(11) NOT NULL,
  `custom_field_id` int(11) NOT NULL COMMENT 'field ref to product',
  `ordering` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`custom_field_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='custom fields Xref to product' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_manufacturers`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_manufacturers` (
  `manufacturer_id` SERIAL,
  `mf_name` varchar(64) DEFAULT NULL,
  `mf_email` varchar(255) DEFAULT NULL,
  `mf_desc` text,
  `mf_category_id` int(11) DEFAULT NULL,
  `mf_url` varchar(255) NOT NULL DEFAULT '',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`manufacturer_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Manufacturers are those who create products' AUTO_INCREMENT=1 ;

--
-- Table structure for table `#__virtuemart_manufacturer_medias`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_manufacturer_medias` (
  `id` SERIAL,
  `manufacturer_id` int(11) NOT NULL DEFAULT '0',
  `file_ids` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_manufacturer_categories`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_manufacturer_categories` (
  `mf_category_id` SERIAL,
  `mf_category_name` varchar(64) DEFAULT NULL,
  `mf_category_desc` text,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`mf_category_id`),
  KEY `idx_manufacturer_category_category_name` (`mf_category_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Manufacturers are assigned to these categories' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_adminmenuentries`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_adminmenuentries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int(10) unsigned NOT NULL COMMENT 'The ID of the VM Module, this Item is assigned to',
  `parent_id` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `link` text NOT NULL,
  `depends` text NOT NULL COMMENT 'Names of the Parameters, this Item depends on',
  `icon_class` varchar(255) NOT NULL,
  `ordering` tinyint(4) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `tooltip` text NOT NULL,
  `view` varchar(255) DEFAULT NULL,
  `task` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Administration Menu Items' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_modules`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_modules` (
  `module_id` SERIAL,
  `module_name` varchar(255) DEFAULT NULL,
  `module_description` text,
  `module_perms` varchar(255) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `is_admin` enum('0','1') NOT NULL,
  `list_order` int(11) DEFAULT NULL,
  PRIMARY KEY (`module_id`),
  KEY `idx_module_name` (`module_name`),
  KEY `idx_module_list_order` (`list_order`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='VirtueMart Core Modules, not: Joomla modules' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_orders`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_orders` (
  `order_id` SERIAL,
  `virtuemart_user_id` int(11) NOT NULL DEFAULT '0',
  `vendor_id` int(11) NOT NULL DEFAULT '0',
  `order_number` varchar(32) DEFAULT NULL,
  `order_pass` varchar(8) DEFAULT NULL,
  `user_info_id` varchar(32) DEFAULT NULL,
  `order_total` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_subtotal` decimal(15,5) DEFAULT NULL,
  `order_tax` decimal(10,5) DEFAULT NULL,
  `order_tax_details` text NOT NULL,
  `order_shipping` decimal(10,2) DEFAULT NULL,
  `order_shipping_tax` decimal(10,5) DEFAULT NULL,
  `coupon_discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `coupon_code` varchar(32) DEFAULT NULL,
  `order_discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `order_currency` varchar(16) DEFAULT NULL,
  `order_status` char(1) DEFAULT NULL,
  `user_currency_id` INT(11) DEFAULT NULL,
  `user_currency_rate` DECIMAL(10,5) NOT NULL DEFAULT '1.0',
  `payment_method_id` INT(11) NOT NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `ship_method_id` varchar(255) DEFAULT NULL,
  `customer_note` text NOT NULL,
  `ip_address` varchar(15) NOT NULL DEFAULT '',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_id`),
  KEY `idx_orders_virtuemart_user_id` (`virtuemart_user_id`),
  KEY `idx_orders_vendor_id` (`vendor_id`),
  KEY `idx_orders_order_number` (`order_number`),
  KEY `idx_orders_user_info_id` (`user_info_id`),
  KEY `idx_orders_ship_method_id` (`ship_method_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Used to store all orders' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_order_history`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_order_history` (
  `order_status_history_id` SERIAL,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `order_status_code` char(1) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `customer_notified` int(1) DEFAULT '0',
  `comments` text,
  PRIMARY KEY (`order_status_history_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Stores all actions and changes that occur to an order' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_order_items`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_order_items` (
  `order_item_id` SERIAL,
  `order_id` int(11) DEFAULT NULL,
  `user_info_id` varchar(32) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `order_item_sku` varchar(64) NOT NULL DEFAULT '',
  `order_item_name` varchar(64) NOT NULL DEFAULT '',
  `product_quantity` int(11) DEFAULT NULL,
  `product_item_price` decimal(15,5) DEFAULT NULL,
  `product_final_price` decimal(15,5) NOT NULL DEFAULT '0.00000',
  `order_item_currency` varchar(16) DEFAULT NULL,
  `order_status` char(1) DEFAULT NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `product_attribute` text,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_item_id`),
  KEY `idx_order_item_order_id` (`order_id`),
  KEY `idx_order_item_user_info_id` (`user_info_id`),
  KEY `idx_order_item_vendor_id` (`vendor_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Stores all items (products) which are part of an order' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_orderstates`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_orderstates` (
  `order_status_id` SERIAL,
  `order_status_code` char(1) NOT NULL DEFAULT '',
  `order_status_name` varchar(64) DEFAULT NULL,
  `order_status_description` text NOT NULL,
  `ordering` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_status_id`),
  KEY `idx_order_status_list_order` (`ordering`),
  KEY `idx_order_status_vendor_id` (`vendor_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='All available order statuses' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_order_userinfos`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_order_userinfos` (
  `order_info_id` SERIAL,
  `order_id` int(11) NOT NULL DEFAULT '0',
  `virtuemart_user_id` int(11) NOT NULL DEFAULT '0',
  `address_type` char(2) DEFAULT NULL,
  `address_type_name` varchar(32) DEFAULT NULL,
  `company` varchar(64) DEFAULT NULL,
  `title` varchar(32) DEFAULT NULL,
  `last_name` varchar(32) DEFAULT NULL,
  `first_name` varchar(32) DEFAULT NULL,
  `middle_name` varchar(32) DEFAULT NULL,
  `phone_1` varchar(32) DEFAULT NULL,
  `phone_2` varchar(32) DEFAULT NULL,
  `fax` varchar(32) DEFAULT NULL,
  `address_1` varchar(64) NOT NULL DEFAULT '',
  `address_2` varchar(64) DEFAULT NULL,
  `city` varchar(32) NOT NULL DEFAULT '',
  `state` varchar(32) NOT NULL DEFAULT '',
  `country` varchar(32) NOT NULL DEFAULT 'US',
  `zip` varchar(32) NOT NULL DEFAULT '',
  `email` varchar(255) DEFAULT NULL,
  `extra_field_1` varchar(255) DEFAULT NULL,
  `extra_field_2` varchar(255) DEFAULT NULL,
  `extra_field_3` varchar(255) DEFAULT NULL,
  `extra_field_4` char(1) DEFAULT NULL,
  `extra_field_5` char(1) DEFAULT NULL,
  `bank_account_nr` varchar(32) NOT NULL DEFAULT '',
  `bank_name` varchar(32) NOT NULL DEFAULT '',
  `bank_sort_code` varchar(16) NOT NULL DEFAULT '',
  `bank_iban` varchar(64) NOT NULL DEFAULT '',
  `bank_account_holder` varchar(48) NOT NULL DEFAULT '',
  `bank_account_type` enum('Checking','Business Checking','Savings') NOT NULL DEFAULT 'Checking',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_info_id`),
  KEY `idx_order_info_order_id` (`order_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Stores the BillTo and ShipTo Information at order time' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_paymentmethods`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_paymentmethods` (
  `paym_id` SERIAL,
  `paym_vendor_id` int(11) NOT NULL DEFAULT '1',
  `paym_jplugin_id` int(11) NOT NULL,
  `paym_name` varchar(255) NOT NULL DEFAULT '',
  `paym_element` varchar(50) NOT NULL DEFAULT '',
  `discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_is_percentage` tinyint(1) NOT NULL DEFAULT '0',
  `discount_max_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount_min_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `paym_extra_info` text NOT NULL,
  `paym_secret_key` blob NOT NULL,
  `paym_params` text NOT NULL,
  `shared` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'valide for all vendors?',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`paym_id`),
  KEY `idx_payment_method_vendor_id` (`paym_vendor_id`),
  KEY `idx_payment_method_name` (`paym_name`),
  KEY `idx_payment_method_list_order` (`ordering`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='The payment methods of your store' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_paymentmethod_creditcards`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_paymentmethod_creditcards` (
  `id` SERIAL,
  `paym_id` int(11) NOT NULL DEFAULT '0',
  `paym_accepted_credit_card` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_paym_id` (`paym_id`,`paym_accepted_credit_card`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_paymentmethod_shoppergroups`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_paymentmethod_shoppergroups` (
  `id` SERIAL,
  `paym_id` int(11) NOT NULL DEFAULT '0',
  `paym_shopper_group` int(11) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_paym_id` (`paym_id`,`paym_shopper_group`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='xref table for paymentmethods to shoppergroup' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_products`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_products` (
  `product_id` SERIAL,
  `vendor_id` int(11) NOT NULL DEFAULT '0',
  `product_parent_id` int(11) NOT NULL DEFAULT '0',
  `product_sku` varchar(64) NOT NULL DEFAULT '',
  `product_s_desc` varchar(255) DEFAULT NULL,
  `product_desc` text,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `product_weight` decimal(10,4) DEFAULT NULL,
  `product_weight_uom` varchar(32) DEFAULT 'pounds.',
  `product_length` decimal(10,4) DEFAULT NULL,
  `product_width` decimal(10,4) DEFAULT NULL,
  `product_height` decimal(10,4) DEFAULT NULL,
  `product_lwh_uom` varchar(32) DEFAULT 'inches',
  `product_url` varchar(255) DEFAULT NULL,
  `product_in_stock` int(11) NOT NULL DEFAULT '0',
  `low_stock_notification` int(11) NOT NULL DEFAULT '0',
  `product_available_date` int(11) DEFAULT NULL,
  `product_availability` varchar(56) NOT NULL DEFAULT '',
  `product_special` char(1) DEFAULT NULL,
  `ship_code_id` int(11) DEFAULT NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `product_name` varchar(64) DEFAULT NULL,
  `product_sales` int(11) NOT NULL DEFAULT '0',
  `attribute` text,
  `custom_attribute` text NOT NULL,
  `product_unit` varchar(32) DEFAULT NULL,
  `product_packaging` int(11) DEFAULT NULL,
  `product_order_levels` varchar(45) DEFAULT NULL,
  `intnotes` text,
  `metadesc` text NOT NULL,
  `metakey` text NOT NULL,
  `metarobot` text NOT NULL,
  `metaauthor` text NOT NULL,
  `layout` varchar(255) NOT NULL,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_id`),
  KEY `idx_product_vendor_id` (`vendor_id`),
  KEY `idx_product_product_parent_id` (`product_parent_id`),
  KEY `idx_product_sku` (`product_sku`),
  KEY `idx_product_ship_code_id` (`ship_code_id`),
  KEY `idx_product_name` (`product_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='All products are stored here.' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_product_categories`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_categories` (
  `virtuemart_category_id` int(11) NOT NULL DEFAULT '0',
  `product_id` int(11) NOT NULL DEFAULT '0',
  `product_list` int(11) DEFAULT NULL,
  KEY `idx_product_category_xref_virtuemart_category_id` (`virtuemart_category_id`),
  KEY `idx_product_category_xref_product_id` (`product_id`),
  KEY `idx_product_category_xref_product_list` (`product_list`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Maps Products to Categories';

-- --------------------------------------------------------


--
-- Table structure for table `#__virtuemart_product_downloads`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_downloads` (
  `product_id` int(11) NOT NULL DEFAULT '0',
  `virtuemart_user_id` int(11) NOT NULL DEFAULT '0',
  `order_id` int(11) NOT NULL DEFAULT '0',
  `end_date` int(11) NOT NULL DEFAULT '0',
  `download_max` int(11) NOT NULL DEFAULT '0',
  `download_id` varchar(32) NOT NULL DEFAULT '',
  `file_name` varchar(255) NOT NULL DEFAULT '',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`download_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Active downloads for selling downloadable goods';

--
-- Table structure for table `#__virtuemart_product_medias`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_medias` (
  `id` SERIAL,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `file_ids` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_medias` (was  `#__virtuemart_product_files`)
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_medias` (
  `virtuemart_media_id` int(19) NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) NOT NULL,
  `file_title` varchar(126) NOT NULL DEFAULT '',
  `file_description` varchar(254) NOT NULL,
  `file_meta` varchar(254) NOT NULL,
  `file_mimetype` varchar(64) NOT NULL,
  `file_url` varchar(254) NOT NULL,
  `file_url_thumb` varchar(254) NOT NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `file_is_product_image` tinyint(1) NOT NULL,
  `file_is_downloadable` tinyint(1) NOT NULL,
  `file_is_forSale` tinyint(1) NOT NULL,
  `shared` tinyint(1) NOT NULL,
  `file_params` text,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`virtuemart_media_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Additional Images and Files which are assigned to products' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_product_manufacturers`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_manufacturers` (
  `product_id` int(11) DEFAULT NULL,
  `manufacturer_id` int(11) DEFAULT NULL,
  KEY `idx_product_mf_xref_product_id` (`product_id`),
  KEY `idx_product_mf_xref_manufacturer_id` (`manufacturer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Maps a product to a manufacturer';

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_product_prices`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_prices` (
  `product_price_id` SERIAL,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `product_price` decimal(15,5) DEFAULT NULL,
  `override` tinyint(1) NOT NULL DEFAULT '0',
  `product_override_price` decimal(15,5) NOT NULL,
  `product_tax_id` int(11) DEFAULT NULL,
  `product_discount_id` int(11) DEFAULT NULL,
  `product_currency` char(16) DEFAULT NULL,
  `product_price_vdate` int(11) DEFAULT NULL,
  `product_price_edate` int(11) DEFAULT NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `shopper_group_id` int(11) DEFAULT NULL,
  `price_quantity_start` int(11) unsigned NOT NULL DEFAULT '0',
  `price_quantity_end` int(11) unsigned NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_price_id`),
  KEY `idx_product_price_product_id` (`product_id`),
  KEY `idx_product_price_shopper_group_id` (`shopper_group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds price records for a product' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_product_producttypes`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_producttypes` (
  `product_id` int(11) NOT NULL DEFAULT '0',
  `product_type_id` int(11) NOT NULL DEFAULT '0',
  KEY `idx_product_product_type_xref_product_id` (`product_id`),
  KEY `idx_product_product_type_xref_product_type_id` (`product_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Maps products to a product type';

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_product_relations`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_relations` (
  `product_id` int(11) NOT NULL DEFAULT '0',
  `related_products` text,
  PRIMARY KEY (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_product_reviews`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_reviews` (
  `review_id` SERIAL,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `userid` int(11) NOT NULL DEFAULT '0',
  `time` int(11) NOT NULL DEFAULT '0',
  `user_rating` tinyint(1) NOT NULL DEFAULT '0',
  `review_ok` int(11) NOT NULL DEFAULT '0',
  `review_votes` int(11) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `product_id` (`product_id`,`userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_product_reviews`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_product_reviews` (
  `product_id` int(255) NOT NULL DEFAULT '0',
  `votes` text NOT NULL,
  `allvotes` int(11) NOT NULL DEFAULT '0',
  `rating` tinyint(1) NOT NULL DEFAULT '0',
  `lastip` varchar(50) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores all votes for a product';

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_producttypes`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_producttypes` (
  `product_type_id` SERIAL,
  `product_type_name` varchar(255) NOT NULL DEFAULT '',
  `product_type_description` text,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `product_type_layout` varchar(255) DEFAULT NULL,
  `ordering` int(11) DEFAULT NULL,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_producttype_customfields`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_producttype_customfields` (
  `product_type_id` int(11) NOT NULL DEFAULT '0',
  `parameter_name` varchar(255) NOT NULL DEFAULT '',
  `parameter_label` varchar(255) NOT NULL DEFAULT '',
  `parameter_description` text,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `parameter_type` char(1) NOT NULL DEFAULT 'T',
  `parameter_values` varchar(255) DEFAULT NULL,
  `parameter_multiselect` char(1) DEFAULT NULL,
  `parameter_default` varchar(255) DEFAULT NULL,
  `parameter_unit` varchar(32) DEFAULT NULL,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_type_id`,`parameter_name`),
  KEY `idx_product_type_parameter_product_type_id` (`product_type_id`),
  KEY `idx_product_type_parameter_parameter_order` (`ordering`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Parameters which are part of a product type';


-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_shippingcarriers`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_shippingcarriers` (
  `shipping_carrier_id` SERIAL,
  `shipping_carrier_jplugin_id` int(11) NOT NULL,
  `shipping_carrier_name` char(80) NOT NULL DEFAULT '',
  `shipping_carrier_list_order` int(11) NOT NULL DEFAULT '0',
  `shipping_carrier_vendor_id` int(11) NOT NULL DEFAULT '1',
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`shipping_carrier_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Shipping Carriers created from the shipper plugins' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_shippingrates`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_shippingrates` (
  `shipping_rate_id` SERIAL,
  `shipping_rate_name` varchar(255) NOT NULL DEFAULT '',
  `shipping_rate_carrier_id` int(11) NOT NULL DEFAULT '0',
  `shipping_rate_country` text NOT NULL,
  `shipping_rate_zip_start` varchar(32) NOT NULL DEFAULT '',
  `shipping_rate_zip_end` varchar(32) NOT NULL DEFAULT '',
  `shipping_rate_weight_start` decimal(10,3) NOT NULL DEFAULT '0.000',
  `shipping_rate_weight_end` decimal(10,3) NOT NULL DEFAULT '0.000',
  `shipping_rate_value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `shipping_rate_package_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `shipping_rate_currency_id` int(11) NOT NULL DEFAULT '0',
  `shipping_rate_vat_id` int(11) NOT NULL DEFAULT '0',
  `shipping_rate_list_order` int(11) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`shipping_rate_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Shipping Rates for each carrier' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_shoppergroups`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_shoppergroups` (
  `shopper_group_id` SERIAL,
  `vendor_id` int(11) DEFAULT NULL,
  `shopper_group_name` varchar(32) DEFAULT NULL,
  `shopper_group_desc` text,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`shopper_group_id`),
  KEY `idx_shopper_group_vendor_id` (`vendor_id`),
  KEY `idx_shopper_group_name` (`shopper_group_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Shopper Groups that users can be assigned to' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------


--
-- Table structure for table `#__virtuemart_states`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_states` (
  `virtuemart_state_id` SERIAL,
  `virtuemart_country_id` int(11) NOT NULL DEFAULT '1',
  `zone_id` int(4) NOT NULL,
  `state_name` varchar(64) DEFAULT NULL,
  `state_3_code` char(3) DEFAULT NULL,
  `state_2_code` char(2) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`virtuemart_state_id`),
  UNIQUE KEY `state_3_code` (`virtuemart_country_id`,`state_3_code`),
  UNIQUE KEY `state_2_code` (`virtuemart_country_id`,`state_2_code`),
  KEY `idx_virtuemart_country_id` (`virtuemart_country_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='States that are assigned to a country' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_users`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_users` (
	`virtuemart_user_id` SERIAL,
	`user_is_vendor` tinyint(1) NOT NULL DEFAULT '0',
	`vendor_id` tinyint(1) NOT NULL DEFAULT '0',
	`customer_number` varchar(32) DEFAULT NULL,
	`perms` varchar(40) NOT NULL DEFAULT 'shopper',
       `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
       `locked_by` int(11) NOT NULL DEFAULT 0,
	PRIMARY KEY (`virtuemart_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds the unique user data' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_user_shoppergroups`
-- 

CREATE TABLE IF NOT EXISTS `#__virtuemart_user_shoppergroups` (
  `id` SERIAL,
  `virtuemart_user_id` int(11) NOT NULL DEFAULT '0',
  `shopper_group_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `i_virtuemart_user_id` (`virtuemart_user_id`,`shopper_group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='xref table for users to shopper group' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `#__virtuemart_permgroups` (
  `group_id` SERIAL,
  `group_name` varchar(128) DEFAULT NULL,
  `group_level` int(11) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds all the user groups' AUTO_INCREMENT=1 ;


-- --------------------------------------------------------
--
-- Table structure for table `#__virtuemart_userfields`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_userfields` (
  `fieldid` SERIAL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT '',
  `maxlength` int(11) DEFAULT NULL,
  `size` int(11) DEFAULT NULL,
  `required` tinyint(4) DEFAULT '0',
  `ordering` int(11) DEFAULT NULL,
  `cols` int(11) DEFAULT NULL,
  `rows` int(11) DEFAULT NULL,
  `value` varchar(50) DEFAULT NULL,
  `default` int(11) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '1',
  `registration` tinyint(1) NOT NULL DEFAULT '0',
  `shipping` tinyint(1) NOT NULL DEFAULT '0',
  `account` tinyint(1) NOT NULL DEFAULT '1',
  `readonly` tinyint(1) NOT NULL DEFAULT '0',
  `calculated` tinyint(1) NOT NULL DEFAULT '0',
  `sys` tinyint(4) NOT NULL DEFAULT '0',
  `vendor_id` int(11) DEFAULT NULL,
  `params` mediumtext,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`fieldid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds the fields for the user information' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_userfields_values`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_userfields_values` (
  `fieldvalueid` SERIAL,
  `fieldid` int(11) NOT NULL DEFAULT '0',
  `fieldtitle` varchar(255) NOT NULL DEFAULT '',
  `fieldvalue` varchar(255) NOT NULL,
  `ordering` int(11) NOT NULL DEFAULT '0',
  `sys` tinyint(4) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`fieldvalueid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Holds the different values for dropdown and radio lists' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_userinfos`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_userinfos` (
  `user_info_id` varchar(32) NOT NULL DEFAULT '',
  `virtuemart_user_id` int(11) NOT NULL DEFAULT '0',
--  `user_is_vendor` tinyint(1) NOT NULL DEFAULT '0',
  `address_type` char(2) DEFAULT NULL,
  `address_type_name` varchar(32) DEFAULT '',
  `company` varchar(64) DEFAULT NULL,
  `title` varchar(32) DEFAULT NULL,
  `last_name` varchar(32) DEFAULT NULL,
  `first_name` varchar(32) DEFAULT NULL,
  `middle_name` varchar(32) DEFAULT NULL,
  `phone_1` varchar(32) DEFAULT NULL,
  `phone_2` varchar(32) DEFAULT NULL,
  `fax` varchar(32) DEFAULT NULL,
  `address_1` varchar(64) NOT NULL DEFAULT '',
  `address_2` varchar(64) DEFAULT NULL,
  `city` varchar(32) NOT NULL DEFAULT '',
  `virtuemart_state_id` int(5) NOT NULL DEFAULT '0',
  `virtuemart_country_id` int(5) NOT NULL DEFAULT '0',
  `zip` varchar(32) NOT NULL DEFAULT '',
  `extra_field_1` varchar(255) DEFAULT NULL,
  `extra_field_2` varchar(255) DEFAULT NULL,
  `extra_field_3` varchar(255) DEFAULT NULL,
  `extra_field_4` char(1) DEFAULT NULL,
  `extra_field_5` char(1) DEFAULT NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
--  `perms` varchar(40) NOT NULL DEFAULT 'shopper',
  PRIMARY KEY (`user_info_id`),
  KEY `idx_user_info_virtuemart_user_id` (`virtuemart_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Customer Information, BT = BillTo and ST = ShipTo';

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_vendors`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_vendors` (
  `vendor_id` SERIAL,
  `vendor_name` varchar(64) DEFAULT NULL,
  `vendor_phone` varchar(32) DEFAULT NULL,
  `vendor_store_name` varchar(128) NOT NULL DEFAULT '',
  `vendor_store_desc` text,
  `vendor_currency` varchar(16) DEFAULT NULL,
  `created_on` datetime NOT NULL default '0000-00-00 00:00:00',
  `created_by` int(11) NOT NULL DEFAULT 0,
  `modified_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 0,
  `vendor_image_path` varchar(255) DEFAULT NULL,
  `vendor_terms_of_service` text NOT NULL,
  `vendor_url` varchar(255) NOT NULL DEFAULT '',
  `vendor_min_pov` decimal(10,2) DEFAULT NULL,
  `vendor_freeshipping` decimal(10,2) NOT NULL DEFAULT '0.00',
  `vendor_currency_display_style` varchar(64) NOT NULL DEFAULT '',
  `vendor_accepted_currencies` text NOT NULL,
  `vendor_address_format` text NOT NULL,
  `vendor_date_format` varchar(255) NOT NULL,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`vendor_id`),
  KEY `idx_vendor_name` (`vendor_name`)
--  KEY `idx_vendor_category_id` (`vendor_category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Vendors manage their products in your store' AUTO_INCREMENT=1 ;


--
-- Table structure for table `#__virtuemart_vendor_medias`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_vendor_medias` (
  `id` SERIAL,
  `vendor_id` int(11) NOT NULL DEFAULT '0',
  `file_ids` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_waitingusers`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_waitingusers` (
  `waiting_list_id` SERIAL,
  `product_id` int(11) NOT NULL DEFAULT '0',
  `virtuemart_user_id` int(11) NOT NULL DEFAULT '0',
  `notify_email` varchar(150) NOT NULL DEFAULT '',
  `notified` enum('0','1') DEFAULT '0',
  `notify_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`waiting_list_id`),
  KEY `product_id` (`product_id`),
  KEY `notify_email` (`notify_email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores notifications, users waiting f. products out of stock' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `#__virtuemart_shippingzones`
--

CREATE TABLE IF NOT EXISTS `#__virtuemart_shippingzones` (
  `zone_id` SERIAL,
  `zone_name` varchar(255) DEFAULT NULL,
  `zone_cost` decimal(10,2) DEFAULT NULL,
  `zone_limit` decimal(10,2) DEFAULT NULL,
  `zone_description` text NOT NULL,
  `zone_tax_rate` int(11) NOT NULL DEFAULT '0',
  `locked_on` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `locked_by` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`zone_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='The Zones managed by the Zone Shipping Module' AUTO_INCREMENT=1 ;


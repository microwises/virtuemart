-- VirtueMart table data SQL script
-- This will insert all required data into the VirtueMart tables


--
--  Dumping data for `#__virtuemart_calcs`
--

INSERT IGNORE INTO `#__virtuemart_calcs` (`virtuemart_calc_id`, `virtuemart_vendor_id`, `calc_name`, `calc_descr`, `calc_kind`, `calc_value_mathop`, `calc_value`, `calc_currency`, `ordering`, `calc_shopper_published`, `calc_vendor_published`, `publish_up`, `publish_down`, `created_on`, `modified_on`, `published`, `shared`) VALUES
(1, 1, 'Tax 9.25%', 'A simple tax for all products regardless the category', 'Tax', '+%', 9.25, '47', 0, 1, 1, '2010-02-21 00:00:00', NULL, NULL, NULL,  1, 0),
(2, 1, 'Discount for all Hand Tools', 'Discount for all Hand Tools 2 euro', 'DATax', '-', 2, '47', 1, 1, 1, '2010-02-21 00:00:00', NULL, NULL, NULL, 1, 0),
(3, 1, 'Duty for Powertools', 'Ah tax that only effects a certain category, Power Tools, and Shoppergroup', 'Tax', '+%', 20, '47', 0, 1, 1, '2010-02-21 00:00:00', NULL, NULL, NULL, 1, 0);


--
-- Dumping data for table `#__virtuemart_calc_categories`
--

INSERT IGNORE INTO `#__virtuemart_calc_categories` (`id`, `virtuemart_calc_id`, `virtuemart_category_id`) VALUES
(NULL, 3, 2);


--
-- Dumping data for table `#__virtuemart_calc_shoppergroups`
--

INSERT IGNORE INTO `#__virtuemart_calc_shoppergroups` (`id`, `virtuemart_calc_id`, `virtuemart_shoppergroup_id`) VALUES
(NULL, 2, 2);


--
-- Dumping data for table `#__virtuemart_categories`
--

INSERT INTO `#__virtuemart_categories` (`virtuemart_category_id`, `virtuemart_vendor_id`,`published`, `created_on`, `modified_on`, `category_template`, `category_layout`, `category_product_layout`, `products_per_row`, `ordering`, `limit_list_start`, `limit_list_step`, `limit_list_max`, `limit_list_initial`, `metarobot`, `metaauthor`) VALUES
(1, 1, 1, NULL, NULL, '0', 'default', 'default', 3, 1, 0, 10, 0, 10, '', ''),
(2, 1, 1, NULL, NULL, '', '', '', 4, 2, NULL, NULL, NULL, NULL, '', ''),
(3, 1, 1, NULL, NULL, '', '', '', 2, 3, NULL, NULL, NULL, NULL, '', ''),
(4, 1, 1, NULL, NULL, '', '', '', 1, 4, NULL, NULL, NULL, NULL, '', ''),
(5, 1, 1, NULL, NULL, '', '', '', 1, 5, NULL, NULL, NULL, NULL, '', '');

INSERT INTO `#__virtuemart_categories_XLANG` (`virtuemart_category_id`, `category_name`, `category_description`, `metadesc`, `metakey`, `slug`) VALUES
	(1, 'Hand Tools', 'Hand Tools', '', '', 'handtools'),
	(2, 'Power Tools', 'Power Tools', '', '', 'powertools'),
	(3, 'Garden Tools', 'Garden Tools', '', '', 'gardentools'),
	(4, 'Outdoor Tools', 'Outdoor Tools', '', '', 'outdoortools'),
	(5, 'Indoor Tools', 'Indoor Tools', '', '', 'indoortools');

--
-- Dumping data for table `#__virtuemart_category_categories`
--

INSERT IGNORE INTO `#__virtuemart_category_categories` (`category_parent_id`, `category_child_id`) VALUES
( 0, 1),
( 0, 2),
( 0, 3),
( 2, 4),
( 2, 5);

--
-- Dumping data for table `#__virtuemart_category_medias`
--

INSERT IGNORE INTO `#__virtuemart_category_medias` (`id`,`virtuemart_category_id`, `virtuemart_media_id`) VALUES
(NULL, 1, 1),
(NULL, 2, 2),
(NULL, 3, 3),
(NULL, 4, 4),
(NULL, 5, 5);

--
-- Dumping data for table `#__virtuemart_customs`
--

INSERT INTO `#__virtuemart_customs` (`virtuemart_custom_id`, `custom_parent_id`, `virtuemart_vendor_id`, `custom_title`, `custom_tip`, `custom_value`, `custom_field_desc`, `field_type`, `is_cart_attribute`, `layout_pos`, `custom_params`, `published`) VALUES
(3, 1, 1, 'Integer', 'Make a choice', '100', 'number', 'I', 0, NULL, NULL, 1),
(4, 1, 1, 'Yes or no ?', 'Boolean', '0', 'Only 2 choices', 'B', 0, NULL, NULL, 1),
(7, 0, 1, 'Photo', 'Give a media ID as defaut', '1', 'Add a photo', 'M', 0, NULL, NULL, 1),
(9, 0, 1, 'Size', 'Change the size', '30', 'CM', 'V', 1, NULL, NULL, 1),
(11, 0, 1, 'Group of fields', 'Add fields to this parent and they are added all at once', 'I\'m a parent', 'Add many fields', 'P', 0, NULL, NULL, 1),
(12, 1, 1, 'I\'m a string', 'Here you can add some text', 'Please enter a text', 'Comment', 'S', 0, NULL, NULL, 1),
(13, 0, 1, 'Color', '', 'Choose a color', 'Colors', 'S', 1, NULL, NULL, 1),
(14, 0, 1, 'add a showel', 'The best choice', '', 'Showels', 'M', 1, NULL, NULL, 1),
(15, 0, 1, 'Automatic Child Variant', '', '', '', 'A', 0, 'ontop', '0', 1);

--
-- Dumping data for table  `#__virtuemart_product_customfields`
--

INSERT INTO `#__virtuemart_product_customfields` (`virtuemart_product_id`,`virtuemart_custom_id`,`custom_value`,`custom_price`,`custom_param`,`published`,`created_on`,`created_by`,`modified_on`,`modified_by`,`locked_on`,`locked_by`,`ordering`) VALUES
(6,4,'0','',NULL,0,'2011-06-27 00:19:47',62,'2011-06-27 00:19:47',62,'0000-00-00 00:00:00',0,0),
(6,3,'100','',NULL,0,'2011-06-27 00:19:47',62,'2011-06-27 00:19:47',62,'0000-00-00 00:00:00',0,0),
(6,2,'Plz enter a text','',NULL,0,'2011-06-27 00:19:47',62,'2011-06-27 00:19:47',62,'0000-00-00 00:00:00',0,0),
(6,7,'1','',NULL,0,'2011-06-27 00:19:47',62,'2011-06-27 00:19:47',62,'0000-00-00 00:00:00',0,0),
(8,11,'7','',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,11,'8','',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,12,'4','',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,12,'2','',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,14,'13','8',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,14,'4','20',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,14,'3','12',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,14,'1','15',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,13,'yellow','0.75',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,13,'red','0.5',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,13,'Blue','0',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,9,'150','60',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,9,'100','50',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,9,'60','40',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(8,9,'50','20',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0),
(1,15,'','',NULL,0,'2011-05-26 11:17:36',62,'2011-05-26 11:17:36',62,'0000-00-00 00:00:00',0,0);

--
-- Dumping data for table `#__virtuemart_manufacturers`
--

INSERT INTO `#__virtuemart_manufacturers` (`virtuemart_manufacturer_id`, `virtuemart_manufacturercategories_id`, `published`) VALUES
(1, 1, 1);

INSERT INTO `#__virtuemart_manufacturers_XLANG` (`virtuemart_manufacturer_id`, `mf_name`, `mf_email`, `mf_desc`, `mf_url`, `slug`) VALUES
	(1, 'Manufacturer', ' manufacturer@example.org', 'An example for a manufacturer', 'http://www.example.org', 'manufacturer-example');


--
-- Dumping data for table `#__virtuemart_manufacturercategories`
--

INSERT INTO `#__virtuemart_manufacturercategories` (`virtuemart_manufacturercategories_id`, `published`) VALUES
(1, 1);

INSERT INTO `#__virtuemart_manufacturercategories_XLANG` (`virtuemart_manufacturercategories_id`, `mf_category_name`, `mf_category_desc`, `slug`) VALUES
	(1, '-default-', 'This is the default manufacturer category', '-default-');

--
-- Dumping data for table `#__virtuemart_manufacturer_medias`
--

INSERT IGNORE INTO `#__virtuemart_manufacturer_medias` (`id`,`virtuemart_manufacturer_id`, `virtuemart_media_id`) VALUES
(NULL, 1, 14);

--
-- Dumping data for table `#__virtuemart_medias`
--

INSERT INTO `#__virtuemart_medias` (`virtuemart_media_id`, `virtuemart_vendor_id`, `file_title`, `file_description`, `file_meta`, `file_mimetype`, `file_type`, `file_url`, `file_url_thumb`, `created_on`, `modified_on`, `published`, `file_is_product_image`, `file_is_downloadable`, `file_is_forSale`, `shared`, `file_params`) VALUES
(1, 1, 'black shovel', '', '', 'image/jpeg', 'category', 'images/stories/virtuemart/category/fc2f001413876a374484df36ed9cf775.jpg', '', NULL, NULL, 1, 0, 0, 0, 0, ''),
(2, 1, 'fe2f63f4c46023e3b33404c80bdd2bfe.jpg', '', '', 'image/jpeg','category', 'images/stories/virtuemart/category/fe2f63f4c46023e3b33404c80bdd2bfe.jpg', '', NULL, NULL, 1, 0, 0, 0, 0, ''),
(3, 1, 'green shovel', '', '', 'image/jpeg', 'category', 'images/stories/virtuemart/category/756ff6d140e11079caf56955060f1162.jpg', '', NULL, NULL, 1, 0, 0, 0, 0, ''),
(4, 1, 'wooden shovel', '', '', 'image/jpeg', 'category', 'images/stories/virtuemart/category/1b0c96d67abdbea648cd0ea96fd6abcb.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(5, 1, 'black shovel', 'the', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/520efefd6d7977f91b16fac1149c7438.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(6, 1, '480655b410d98a5cc3bef3927e786866.jpg', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/480655b410d98a5cc3bef3927e786866.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(7, 1, 'nice saw', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/e614ba08c3ee0c2adc62fd9e5b9440eb.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(8, 1, 'our ladder', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/8cb8d644ef299639b7eab25829d13dbc.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(9, 1, 'Hamma', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/578563851019e01264a9b40dcf1c4ab6.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(10, 1, 'drill', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/1ff5f2527907ca86103288e1b7cc3446.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(11, 1, 'circular saw', 'for the fine cut', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/9a4448bb13e2f7699613b2cfd7cd51ad.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(12, 1, 'chain saw', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/8716aefc3b0dce8870360604e6eb8744.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(13, 1, 'hand shovel', '', '', 'image/jpeg', 'product', 'images/stories/virtuemart/product/cca3cd5db813ee6badf6a3598832f2fc.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(14, 1, 'manufacturer', '', '', 'image/jpeg', 'manufacturer', 'images/stories/virtuemart/manufacturer/manufacturersample.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(15, 1, 'Washupito', '', '', 'image/jpeg', 'vendor', 'images/stories/virtuemart/vendor/washupito.gif', '', NULL, NULL, 1, 1, 0, 0, 0, '');



INSERT INTO `#__virtuemart_products` (`virtuemart_product_id`, `virtuemart_vendor_id`, `product_parent_id`, `product_sku`, `product_weight`, `product_weight_uom`, `product_length`, `product_width`, `product_height`, `product_lwh_uom`, `product_url`, `product_in_stock`, `product_ordered`, `low_stock_notification`, `product_available_date`, `product_availability`, `product_special`, `product_sales`, `product_unit`, `product_packaging`, `product_params`, `hits`, `intnotes`, `metarobot`, `metaauthor`, `layout`, `published`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES
	(1, 1, 0, 'G01', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 10, 0, 5, '2010-02-21 00:00:00', '48h.gif', 1, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(2, 1, 0, 'G02', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 76, 0, 5, '2010-02-21 00:00:00', '3-5d.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(3, 1, 0, 'G03', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 32, 0, 5, '2010-02-21 00:00:00', '7d.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(4, 1, 0, 'G04', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 98, 0, 5, '2010-02-21 00:00:00', 'on-order.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(5, 1, 0, 'H01', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 32, 0, 5, '2010-02-21 00:00:00', '1-4w.gif', 1, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(6, 1, 0, 'H02', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 500, 0, 5, '2011-12-21 00:00:00', '24h.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(7, 1, 0, 'P01', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 45, 0, 5, '2011-12-21 00:00:00', '48h.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(8, 1, 0, 'P02', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 33, 0, 5, '2010-12-21 00:00:00', '3-5d.gif', 1, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(9, 1, 0, 'P03', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 3, 0, 5, '2011-07-21 00:00:00', '2-3d.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(10, 1, 0, 'P04', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 2, 0, 5, '2010-12-21 00:00:00', '1-2m.gif', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(11, 1, 1, 'G01-01', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 0, 0, 5, '0000-00-00 00:00:00', '', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(12, 1, 1, 'G01-02', 10.0000, '', 0.0000, 0.0000, 0.0000, '', '', 0, 0, 5, '0000-00-00 00:00:00', '', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(13, 1, 1, 'G01-03', 10.0000, '', 0.0000, 0.0000, 0.0000, '', '', 0, 0, 5, '0000-00-00 00:00:00', '', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(14, 1, 2, 'L01', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 22, 0, 5, '2011-12-21 00:00:00', '', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(15, 1, 2, 'L02', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 0, 0, 5, '0000-00-00 00:00:00', '', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0),
	(16, 1, 2, 'L03', 10.0000, 'KG', 0.0000, 0.0000, 0.0000, 'M', '', 0, 0, 5, '0000-00-00 00:00:00', '', 0, 0, '', 0, 'min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|min_order_level=d:0;|max_order_level=d:0;|', 0, '', '', '', '', 1, '0000-00-00 00:00:00', 0, '2011-11-22 11:26:51', 62, '0000-00-00 00:00:00', 0);

INSERT INTO `#__virtuemart_products_XLANG` (`virtuemart_product_id`, `product_name`, `product_s_desc`, `product_desc`, `metadesc`, `metakey`, `slug`) VALUES
	(1, 'Hand Shovel', '<p>Nice hand shovel to dig with in the yard.</p>\r\n', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '', '', 'hand-shovel'),
	(2, 'Ladder', 'A really long ladder to reach high places.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '', '', 'ladder'),
	(3, 'Shovel', 'Nice shovel.  You can dig your way to China with this one.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '', '', 'shovel'),
	(4, 'Smaller Shovel', 'This shovel is smaller but you\'ll be able to dig real quick.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '', '', 'smaller-shovel'),
	(5, 'Nice Saw', 'This saw is great for getting cutting through downed limbs.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '', '', 'nice-saw'),
	(6, 'Hammer', 'A great hammer to hammer away with.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '', '', 'hammer'),
	(7, 'Chain Saw', 'Don\'t do it with an axe.  Get a chain saw.', '\r\n<ul>  <li>Tool-free tensioner for easy, convenient chain adjustment  </li><li>3-Way Auto Stop; stops chain a fraction of a second  </li><li>Automatic chain oiler regulates oil for proper chain lubrication  </li><li>Small radius guide bar reduces kick-back  </li></ul>  <br />  <b>Specifications</b><br />  12.5 AMPS   <br />   16" Bar Length   <br />   3.5 HP   <br />   8.05 LBS. Weight   <br />\r\n', '', '', 'chain-saw'),
	(8, 'Circular Saw', 'Cut rings around wood.  This saw can handle the most delicate projects.', '\r\n<ul>  <li>Patented Sightline; Window provides maximum visibility for straight cuts  </li><li>Adjustable dust chute for cleaner work area  </li><li>Bail handle for controlled cutting in 90ÔøΩ to 45ÔøΩ applications  </li><li>1-1/2 to 2-1/2 lbs. lighter and 40% less noise than the average circular saw                     </li><li><b>Includes:</b>Carbide blade  </li></ul>  <br />  <b>Specifications</b><br />  10.0 AMPS   <br />   4,300 RPM   <br />   Capacity: 2-1/16" at 90ÔøΩ, 1-3/4" at 45ÔøΩ<br />\r\n', '', '', 'circular-saw'),
	(9, 'Drill', 'Drill through anything.  This drill has the power you need for those demanding hole boring duties.', '\r\n<font color="#000000" size="3"><ul><li>High power motor and double gear reduction for increased durability and improved performance  </li><li>Mid-handle design and two finger trigger for increased balance and comfort  </li><li>Variable speed switch with lock-on button for continuous use  </li><li><b>Includes:</b> Chuck key &amp; holder  </li></ul>  <br />  <b>Specifications</b><br />  4.0 AMPS   <br />   0-1,350 RPM   <br />   Capacity: 3/8" Steel, 1" Wood   <br /><br />  </font>\r\n', '', '', 'drill'),
	(10, 'Power Sander', 'Blast away that paint job from the past.  Use this power sander to really show them you mean business.', '\r\n<ul>  <li>Lever activated paper clamps for simple sandpaper changes  </li><li>Dust sealed rocker switch extends product life and keeps dust out of motor  </li><li>Flush sands on three sides to get into corners  </li><li>Front handle for extra control  </li><li>Dust extraction port for cleaner work environment   </li></ul>  <br />  <b>Specifications</b><br />  1.2 AMPS    <br />   10,000 OPM    <br />\r\n', '', '', 'power-sander'),
	(11, 'Hand Shovel cheap', '', '', '', '', 'hand-shovel-g01'),
	(12, 'Hand Shovel enforced', '', '', '', '', 'hand-shovel-g02'),
	(13, 'Hand Shovel heavy duty', '', '', '', '', 'hand-shovel-g03'),
	(14, 'Metal Ladder', '', '', '', '', 'metal-ladder'),
	(15, 'Wooden Ladder', '', '', '', '', 'wooden-ladder'),
	(16, 'Plastic Ladder', '', '', '', '', 'plastic-ladder');

INSERT IGNORE INTO `#__virtuemart_product_medias` (`id`,`virtuemart_product_id`, `virtuemart_media_id`) VALUES
(NULL, 1, 13),
(NULL, 2, 8),
(NULL, 3, 5),
(NULL, 4, 4),
(NULL, 5, 7),
(NULL, 6, 9),
(NULL, 7, 12),
(NULL, 8, 11),
(NULL, 9, 10),
(NULL, 10, 6);

INSERT IGNORE INTO `#__virtuemart_vendor_medias` (`id`,`virtuemart_vendor_id`, `virtuemart_media_id`) VALUES
(NULL, 1, 15);
--
-- Dumping data for table `#__virtuemart_product_categories`
--

INSERT IGNORE INTO `#__virtuemart_product_categories` (`virtuemart_category_id`, `virtuemart_product_id`, `ordering`) VALUES
(1, 1, NULL),
(3, 2, NULL),
(3, 3, NULL),
(3, 4, NULL),
(1, 5, NULL),
(1, 6, NULL),
(4, 7, NULL),
(2, 8, NULL),
(5, 9, NULL),
(2, 10, NULL);


--
-- Dumping data for table `#__virtuemart_product_manufacturers`
--

INSERT IGNORE INTO `#__virtuemart_product_manufacturers` (`virtuemart_product_id`, `virtuemart_manufacturer_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1);

--
-- Dumping data for table `#__virtuemart_product_prices`
--

INSERT INTO `#__virtuemart_product_prices` (`virtuemart_product_price_id`, `virtuemart_product_id`, `product_price`, `override`, `product_override_price`, `product_tax_id`, `product_discount_id`, `product_currency`, `product_price_vdate`, `product_price_edate`, `virtuemart_shoppergroup_id`, `price_quantity_start`, `price_quantity_end`) VALUES
(1, 5, '24.99000', 0, '0.00000', NULL, NULL, '144', 0, 0,  5, 0, 0),
(2, 1, '4.49000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(3, 2, '39.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(4, 3, '24.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(5, 4, '17.99000', 1, '77.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(6, 6, '4.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(7, 7, '149.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(8, 8, '220.90000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(9, 9, '48.12000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(10, 10, '74.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(11, 11, '2.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 6, 0, 0),
(12, 12, '14.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(13, 13, '79.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(14, 14, '49.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(15, 15, '59.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 5, 0, 0),
(16, 16, '3.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 6, 0, 0);

--
-- Dumping data for table `#__virtuemart_shoppergroups`
--

INSERT IGNORE INTO `#__virtuemart_shoppergroups` (`virtuemart_shoppergroup_id`, `virtuemart_vendor_id`, `shopper_group_name`, `shopper_group_desc`, `default`) VALUES
(NULL, 1, 'Gold Level', 'Gold Level Shoppers.', 0),
(NULL, 1, 'Wholesale', 'Shoppers that can buy at wholesale.', 0);

--
-- Dumping data for table `#__virtuemart_worldzones`
--

INSERT INTO `#__virtuemart_worldzones` (`virtuemart_worldzone_id`, `zone_name`, `zone_cost`, `zone_limit`, `zone_description`, `zone_tax_rate`) VALUES
(1, 'Default', '6.00', '35.00', 'This is the default Shipment Zone. This is the zone information that all countries will use until you assign each individual country to a Zone.', 2),
(2, 'Zone 1', '1000.00', '10000.00', 'This is a zone example', 2),
(3, 'Zone 2', '2.00', '22.00', 'This is the second zone. You can use this for notes about this zone', 2),
(4, 'Zone 3', '11.00', '64.00', 'Another usefull thing might be details about this zone or special instructions.', 2);

INSERT INTO `#__virtuemart_userfield_values` (`virtuemart_userfield_value_id`, `virtuemart_userfield_id`, `fieldtitle`, `fieldvalue`, `sys`, `ordering`, `created_on`, `created_by`, `modified_on`, `modified_by`, `locked_on`, `locked_by`) VALUES
(null, 10, 'Mr', 'Mr', 0, 0, '', 0, '', 0, '', 0),
(null, 10, 'Mrs', 'Mrs', 0, 1, '', 0, '', 0, '', 0);
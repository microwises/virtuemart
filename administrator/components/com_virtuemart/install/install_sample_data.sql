-- VirtueMart table data SQL script 
-- This will insert all required data into the VirtueMart tables


--
--  Dumping data for `#__virtuemart_calcs`
--

INSERT IGNORE INTO `#__virtuemart_calcs` (`virtuemart_calc_id`, `virtuemart_vendor_id`, `calc_name`, `calc_descr`, `calc_kind`, `calc_value_mathop`, `calc_value`, `calc_currency`, `ordering`, `calc_shopper_published`, `calc_vendor_published`, `publish_up`, `publish_down`, `created_on`, `modified_on`, `calc_qualify`, `calc_affected`, `calc_amount_cond`, `calc_amount_dimunit`, `published`, `shared`) VALUES
(1, 1, 'Tax 9.25%', 'A simple tax for all products regardless the category', 'Tax', '+%', 9.25, '47', 0, 1, 1, '2010-02-21 00:00:00', NULL, NULL, NULL, 0, 0, 0, '', 1, 0),
(2, 1, 'Discount for all Hand Tools', 'Discount for all Hand Tools 2 euro', 'DBTax', '-', 2, '47', 1, 1, 1, '2010-02-21 00:00:00', NULL, NULL, NULL, 0, 0, 0, '', 1, 0),
(3, 1, 'Duty for Powertools', 'Ah tax that only effects a certain category, Power Tools, and Shoppergroup', 'Tax', '+%', 20, '47', 0, 1, 1, '2010-02-21 00:00:00', NULL, NULL, NULL, 0, 0, 0, '', 1, 0);


--
-- Dumping data for table `#__virtuemart_calc_categories`
--

INSERT IGNORE INTO `#__virtuemart_calc_categories` (`id`, `virtuemart_calc_id`, `virtuemart_category_id`) VALUES
(2, 3, 1),
(5, 4, 2);


--
-- Dumping data for table `#__virtuemart_calc_shoppergroups`
--

INSERT IGNORE INTO `#__virtuemart_calc_shoppergroups` (`id`, `virtuemart_calc_id`, `virtuemart_shoppergroup_id`) VALUES
(11, 0, 5);


--
-- Dumping data for table `#__virtuemart_categories`
--

INSERT INTO `#__virtuemart_categories` (`virtuemart_category_id`, `vendor_id`, `category_name`, `category_description`, `published`, `created_on`, `modified_on`, `category_template`, `category_layout`, `category_product_layout`, `products_per_row`, `ordering`, `limit_list_start`, `limit_list_step`, `limit_list_max`, `limit_list_initial`, `metadesc`, `metakey`, `metarobot`, `metaauthor`) VALUES
(1, 1, 'Hand Tools', 'Hand Tools', 1, NULL, NULL, '0', 'default', 'default', 3, 1, 0, 10, 0, 10, '', '', '', ''),
(2, 1, 'Power Tools', 'Power Tools', 1, NULL, NULL, '', '', '', 4, 2, NULL, NULL, NULL, NULL, '', '', '', ''),
(3, 1, 'Garden Tools', 'Garden Tools', 1, NULL, NULL, '', '', '', 2, 3, NULL, NULL, NULL, NULL, '', '', '', ''),
(4, 1, 'Outdoor Tools', 'Outdoor Tools', 1, NULL, NULL, '', '', '', 1, 4, NULL, NULL, NULL, NULL, '', '', '', ''),
(5, 1, 'Indoor Tools', 'Indoor Tools', 1, NULL, NULL, '', '', '', 1, 5, NULL, NULL, NULL, NULL, '', '', '', '');

--
-- Dumping data for table `#__virtuemart_category_categories`
--

INSERT IGNORE INTO `#__virtuemart_category_categories` (`category_parent_id`, `category_child_id`, `category_list`, `category_shared`) VALUES
(0, 1, NULL, 1),
(0, 2, NULL, 1),
(0, 3, NULL, 1),
(2, 4, NULL, 1),
(2, 5, NULL, 1);


--
-- Dumping data for table `#__virtuemart_category_medias`
--

INSERT IGNORE INTO `#__virtuemart_category_medias` (`id`,`virtuemart_category_id`, `file_ids`) VALUES
(NULL, 1, 1),
(NULL, 2, 2),
(NULL, 3, 3),
(NULL, 4, 4),
(NULL, 5, 5);

--
-- Dumping data for table `#__virtuemart_customs`
--

INSERT INTO `#__virtuemart_customs` (`custom_id`, `custom_parent_id`, `admin_only`, `custom_title`, `custom_tip`, `custom_value`, `custom_field_desc`, `field_type`, `is_list`, `is_hidden`, `is_cart_attribute`, `published`) VALUES
(1, 0, 0, 'Group of fields', 'Add fields to this parent and they are added all at once', 'I''m a parent', 'Add many fields', 'P', 0, 0, 0, 1),
(2, 1, 0, 'I''m a string', 'Here you can add some text', 'Please enter a text', 'Comment', 'S', 0, 0, 0, 1),
(3, 1, 0, 'Integer', 'Make a choice', '100', 'number', 'I', 0, 0, 0, 1),
(4, 1, 0, 'Yes or no ?', 'Boolean', '0', 'Only 2 choices', 'B', 0, 0, 0, 1),
(5, 0, 0, 'I''m a Child link', 'Add a child to me', '', 'link to child', 'C', 0, 0, 0, 1),
(7, 0, 0, 'PHOTO', 'Give a media ID as defaut', '1', 'Add a photo', 'i', 0, 0, 0, 1),
(9, 0, 0, 'Size', 'Change the size', '30', 'CM', 'V', 0, 0, 1, 1),
(10, 0, 0, 'User Comment', 'Add a personnal Text', 'Your text Here', 'comment', 'U', 0, 0, 1, 1);

--
-- Dumping data for table  `#__virtuemart_customfields`
--

INSERT INTO `#__virtuemart_customfields` (`custom_field_id`, `custom_id`, `custom_value`, `custom_price`, `published`) VALUES
(2, 7, '1', NULL, 0),
(4, 2, 'Plz enter a text', NULL, 0),
(5, 3, '100', NULL, 0),
(6, 4, '0', NULL, 0);

--
-- Dumping data for table  `#__virtuemart_product_customfields`
--

INSERT INTO `#__virtuemart_product_customfields` (`id`, `virtuemart_product_id`, `custom_field_id`, `ordering`, `published`) VALUES
(NULL, 6, 2, 0, 0),
(NULL, 6, 4, 0, 0),
(NULL, 6, 5, 0, 0),
(NULL, 6, 6, 0, 0);
--
-- Dumping data for table `#__virtuemart_manufacturers`
--

INSERT INTO `#__virtuemart_manufacturers` (`manufacturer_id`, `mf_name`, `mf_email`, `mf_desc`, `mf_category_id`, `mf_url`, `published`) VALUES
(1, 'Manufacturer', ' manufacturer@example.org', 'An example for a manufacturer', 1, 'http://www.example.org', 1);


--
-- Dumping data for table `#__virtuemart_manufacturer_categories`
--

INSERT INTO `#__virtuemart_manufacturer_categories` (`mf_category_id`, `mf_category_name`, `mf_category_desc`) VALUES
(1, '-default-', 'This is the default manufacturer category');

--
-- Dumping data for table `#__virtuemart_manufacturer_medias`
--

INSERT IGNORE INTO `#__virtuemart_manufacturer_medias` (`id`,`manufacturer_id`, `file_ids`) VALUES
(NULL, 1, 1);

--
-- Dumping data for table `#__virtuemart_medias`
--

INSERT INTO `#__virtuemart_medias` (`virtuemart_media_id`, `vendor_id`, `file_title`, `file_description`, `file_meta`, `file_mimetype`, `file_url`, `file_url_thumb`, `created_on`, `modified_on`, `published`, `file_is_product_image`, `file_is_downloadable`, `file_is_forSale`, `shared`, `file_params`) VALUES
(1, 1, 'black shovel', '', '', 'image/jpeg', 'images/stories/virtuemart/category/fc2f001413876a374484df36ed9cf775.jpg', '', NULL, NULL, 1, 0, 0, 0, 0, ''),
(2, 1, 'fe2f63f4c46023e3b33404c80bdd2bfe.jpg', '', '', 'image/jpeg', 'images/stories/virtuemart/category/fe2f63f4c46023e3b33404c80bdd2bfe.jpg', '', NULL, NULL, 1, 0, 0, 0, 0, ''),
(3, 1, 'green shovel', '', '', 'image/jpeg', 'images/stories/virtuemart/category/756ff6d140e11079caf56955060f1162.jpg', '', NULL, NULL, 1, 0, 0, 0, 0, ''),
(4, 1, 'wooden shovel', '', '', 'image/jpeg', 'images/stories/virtuemart/product/1b0c96d67abdbea648cd0ea96fd6abcb.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(5, 1, 'black shovel', 'the', '', 'image/jpeg', 'images/stories/virtuemart/product/520efefd6d7977f91b16fac1149c7438.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(6, 1, '480655b410d98a5cc3bef3927e786866.jpg', '', '', 'image/jpeg', 'images/stories/virtuemart/product/480655b410d98a5cc3bef3927e786866.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(7, 1, 'nice saw', '', '', 'image/jpeg', 'images/stories/virtuemart/product/e614ba08c3ee0c2adc62fd9e5b9440eb.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(8, 1, 'our ladder', '', '', 'image/jpeg', 'images/stories/virtuemart/product/8cb8d644ef299639b7eab25829d13dbc.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(9, 1, 'Hamma', '', '', 'image/jpeg', 'images/stories/virtuemart/product/578563851019e01264a9b40dcf1c4ab6.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(10, 1, 'drill', '', '', 'image/jpeg', 'images/stories/virtuemart/product/1ff5f2527907ca86103288e1b7cc3446.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(11, 1, 'circular saw', 'for the fine cut', '', 'image/jpeg', 'images/stories/virtuemart/product/9a4448bb13e2f7699613b2cfd7cd51ad.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(12, 1, 'chain saw', '', '', 'image/jpeg', 'images/stories/virtuemart/product/8716aefc3b0dce8870360604e6eb8744.jpg', '', NULL, NULL, 1, 1, 0, 0, 0, ''),
(13, 1, 'hand shovel', '', '', 'image/jpeg', 'images/stories/virtuemart/product/cca3cd5db813ee6badf6a3598832f2fc.jpg', 'images/stories/virtuemart/product/resized/cca3cd5db813ee6badf6a3598832f2fc_90x90.jpg', NULL, NULL, 1, 1, 0, 0, 0, '');

--
-- Dumping data for table `#__virtuemart_products`
--

INSERT INTO `#__virtuemart_products` (`virtuemart_product_id`, `vendor_id`, `product_parent_id`, `product_sku`, `product_s_desc`, `product_desc`, `published`, `product_weight`, `product_weight_uom`, `product_length`, `product_width`, `product_height`, `product_lwh_uom`, `product_url`, `product_in_stock`, `low_stock_notification`, `product_available_date`, `product_availability`, `product_special`, `ship_code_id`, `created_on`, `modified_on`, `product_name`, `product_sales`, `attribute`, `custom_attribute`, `product_unit`, `product_packaging`, `product_order_levels`, `intnotes`, `metadesc`, `metakey`, `metarobot`, `metaauthor`, `layout`) VALUES
(1, 1, 0, 'G01', '<p>Nice hand shovel to dig with in the yard.</p>\r\n', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 10, 5, 1072911600, '48h.gif', 'Y', NULL, NULL, NULL, 'Hand Shovel', 0, '', '', '', 0, NULL, NULL, '', '', '', '', ''),
(2, 1, 0, 'G02', 'A really long ladder to reach high places.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 76, 5, 1072911600, '3-5d.gif', 'N', NULL, NULL, NULL, 'Ladder', 0, '', '', '', 0, NULL, NULL, '', '', '', '', ''),
(3, 1, 0, 'G03', 'Nice shovel.  You can dig your way to China with this one.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 32, 5, 1072911600, '7d.gif', 'N', NULL, NULL, NULL, 'Shovel', 0, 'Size,XL[+1.99],M,S[-2.99];Colour,Red,Green,Yellow,ExpensiveColor[=24.00]', '', '', 0, NULL, NULL, '', '', '', '', ''),
(4, 1, 0, 'G04', 'This shovel is smaller but you''ll be able to dig real quick.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 98, 5, 1088632800, 'on-order.gif', 'N', NULL, NULL, NULL, 'Smaller Shovel', 0, 'Size,big[+2.99],medium;Color,red[+0.99],green[-0.99]', '', '', 0, NULL, NULL, '', '', '', '', ''),
(5, 1, 0, 'H01', 'This saw is great for getting cutting through downed limbs.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 32, 5, 1072911600, '1-4w.gif', 'Y', NULL, NULL, NULL, 'Nice Saw', 0, 'Size,big,small,medium;Power,100W,200W,500W', '', '', 0, NULL, NULL, '', '', '', '', ''),
(6, 1, 0, 'H02', 'A great hammer to hammer away with.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 500, 5, 1072911600, '24h.gif', 'N', NULL, NULL, NULL, 'Hammer', 0, 'Size,big,medium,small;Material,wood and metal,plastic and metal[-0.99]', '', '', 0, NULL, NULL, '', '', '', '', ''),
(7, 1, 0, 'P01', 'Don''t do it with an axe.  Get a chain saw.', '\r\n<ul>  <li>Tool-free tensioner for easy, convenient chain adjustment  </li><li>3-Way Auto Stop; stops chain a fraction of a second  </li><li>Automatic chain oiler regulates oil for proper chain lubrication  </li><li>Small radius guide bar reduces kick-back  </li></ul>  <br />  <b>Specifications</b><br />  12.5 AMPS   <br />   16" Bar Length   <br />   3.5 HP   <br />   8.05 LBS. Weight   <br />\r\n', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 45, 5, 1088632800, '48h.gif', 'N', NULL, NULL, NULL, 'Chain Saw', 0, '', '', '', 0, NULL, NULL, '', '', '', '', ''),
(8, 1, 0, 'P02', 'Cut rings around wood.  This saw can handle the most delicate projects.', '\r\n<ul>  <li>Patented Sightline; Window provides maximum visibility for straight cuts  </li><li>Adjustable dust chute for cleaner work area  </li><li>Bail handle for controlled cutting in 90� to 45� applications  </li><li>1-1/2 to 2-1/2 lbs. lighter and 40% less noise than the average circular saw                     </li><li><b>Includes:</b>Carbide blade  </li></ul>  <br />  <b>Specifications</b><br />  10.0 AMPS   <br />   4,300 RPM   <br />   Capacity: 2-1/16" at 90�, 1-3/4" at 45�<br />\r\n', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 33, 5, 1072911600, '3-5d.gif', 'Y', NULL, NULL, NULL, 'Circular Saw', 0, 'Size,XL[+1],M,S[-2];Power,strong,middle,poor[=24]', '', '', 0, NULL, NULL, '', '', '', '', ''),
(9, 1, 0, 'P03', 'Drill through anything.  This drill has the power you need for those demanding hole boring duties.', '\r\n<font color="#000000" size="3"><ul><li>High power motor and double gear reduction for increased durability and improved performance  </li><li>Mid-handle design and two finger trigger for increased balance and comfort  </li><li>Variable speed switch with lock-on button for continuous use  </li><li><b>Includes:</b> Chuck key &amp; holder  </li></ul>  <br />  <b>Specifications</b><br />  4.0 AMPS   <br />   0-1,350 RPM   <br />   Capacity: 3/8" Steel, 1" Wood   <br /><br />  </font>\r\n', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 3, 5, 1072911600, '2-3d.gif', 'N', NULL, NULL, NULL, 'Drill', 0, '', '', '', 0, NULL, NULL, '', '', '', '', ''),
(10, 1, 0, 'P04', 'Blast away that paint job from the past.  Use this power sander to really show them you mean business.', '\r\n<ul>  <li>Lever activated paper clamps for simple sandpaper changes  </li><li>Dust sealed rocker switch extends product life and keeps dust out of motor  </li><li>Flush sands on three sides to get into corners  </li><li>Front handle for extra control  </li><li>Dust extraction port for cleaner work environment   </li></ul>  <br />  <b>Specifications</b><br />  1.2 AMPS    <br />   10,000 OPM    <br />\r\n', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 2, 5, 1072911600, '1-2m.gif', 'N', NULL, NULL, NULL, 'Power Sander', 0, 'Size,big,medium,small;Power,100W,200W,300W', '', '', 0, NULL, NULL, '', '', '', '', ''),
(11, 1, 1, 'G01-01', '', '', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 0, 5, 0, '', '', NULL, NULL, NULL, 'Hand Shovel', 0, NULL, '', '', 0, NULL, NULL, '', '', '', '', ''),
(12, 1, 1, 'G01-02', '', '', 1, '10.0000', '', '0.0000', '0.0000', '0.0000', '', '', 0, 5, 0, '', '', NULL, NULL, NULL, 'Hand Shovel', 0, NULL, '', '', 0, NULL, NULL, '', '', '', '', ''),
(13, 1, 1, 'G01-03', '', '', 1, '10.0000', '', '0.0000', '0.0000', '0.0000', '', '', 0, 5, 0, '', '', NULL, NULL, NULL, 'Hand Shovel', 0, NULL, '', '', 0, NULL, NULL, '', '', '', '', ''),
(14, 1, 2, 'L01', '', '', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 22, 5, 1072911600, '', 'N', NULL, NULL, NULL, 'Metal Ladder', 0, NULL, '', '', 0, NULL, NULL, '', '', '', '', ''),
(15, 1, 2, 'L02', '', '', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 0, 5, 0, '', '', NULL, NULL, NULL, 'Wooden Ladder', 0, NULL, '', '', 0, NULL, NULL, '', '', '', '', ''),
(16, 1, 2, 'L03', '', '', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 0, 5, 0, '', '', NULL, NULL, NULL, 'Plastic Ladder', 0, NULL, '', '', 0, NULL, NULL, '', '', '', '', '');


INSERT IGNORE INTO `#__virtuemart_product_medias` (`id`,`virtuemart_product_id`, `file_ids`) VALUES
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


--
-- Dumping data for table `#__virtuemart_product_categories`
--

INSERT IGNORE INTO `#__virtuemart_product_categories` (`virtuemart_category_id`, `virtuemart_product_id`, `product_list`) VALUES
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

INSERT IGNORE INTO `#__virtuemart_product_manufacturers` (`virtuemart_product_id`, `manufacturer_id`) VALUES
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

INSERT INTO `#__virtuemart_product_prices` (`product_price_id`, `virtuemart_product_id`, `product_price`, `override`, `product_override_price`, `product_tax_id`, `product_discount_id`, `product_currency`, `product_price_vdate`, `product_price_edate`, `created_on`, `modified_on`, `shopper_group_id`, `price_quantity_start`, `price_quantity_end`) VALUES
(1, 5, '24.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(2, 1, '4.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(3, 2, '49.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(4, 3, '24.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(5, 4, '17.99000', 1, '77.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(6, 6, '1.00000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(7, 7, '149.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(8, 8, '220.90000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(9, 9, '48.12000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(10, 10, '74.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(11, 1, '2.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 6, 0, 0),
(12, 13, '14.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(13, 14, '79.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(14, 15, '49.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(15, 16, '59.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 5, 0, 0),
(16, 7, '2.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, NULL, NULL, 6, 0, 0);

--
-- Dumping data for table `#__virtuemart_shoppergroups`
--

INSERT IGNORE INTO `#__virtuemart_shoppergroups` (`shopper_group_id`, `vendor_id`, `shopper_group_name`, `shopper_group_desc`, `default`) VALUES
(NULL, 1, 'Gold Level', 'Gold Level Shoppers.', 0),
(NULL, 1, 'Wholesale', 'Shoppers that can buy at wholesale.', 0);

--
-- Dumping data for table `#__virtuemart_shippingzones`
--

INSERT INTO `#__virtuemart_shippingzones` (`zone_id`, `zone_name`, `zone_cost`, `zone_limit`, `zone_description`, `zone_tax_rate`) VALUES
(1, 'Default', '6.00', '35.00', 'This is the default Shipping Zone. This is the zone information that all countries will use until you assign each individual country to a Zone.', 2),
(2, 'Zone 1', '1000.00', '10000.00', 'This is a zone example', 2),
(3, 'Zone 2', '2.00', '22.00', 'This is the second zone. You can use this for notes about this zone', 2),
(4, 'Zone 3', '11.00', '64.00', 'Another usefull thing might be details about this zone or special instructions.', 2);
-- VirtueMart table data SQL script
-- This will insert all required data into the VirtueMart tables


--
--  Dumping data for `#__vm_calc`
--

INSERT IGNORE INTO `#__vm_calc` (`calc_id`, `calc_vendor_id`, `calc_name`, `calc_descr`, `calc_kind`, `calc_value_mathop`, `calc_value`, `calc_currency`, `ordering`, `calc_shopper_published`, `calc_vendor_published`, `publish_up`, `publish_down`, `modified`, `calc_qualify`, `calc_affected`, `calc_amount_cond`, `calc_amount_dimunit`, `published`, `shared`) VALUES
(2, 1, 'Tax 9.25%', 'A simpel tax for all products regardless the category', 'Tax', '+%', 9.25, '', 0, 1, 1, '2010-02-21 00:00:00', '0000-00-00 00:00:00', '2010-02-21 18:26:27', 0, 0, 0, '', 1, 0),
(3, 1, 'Discount for all Hand Tools', 'Discount for all Hand Tools 2 euro', 'DBTax', '-', 2, '', 1, 1, 1, '2010-02-21 00:00:00', '0000-00-00 00:00:00', '2010-02-21 22:51:39', 0, 0, 0, '', 1, 0),
(4, 1, 'Duty for Powertools', 'Ah tax that only effects a certain category, Power Tools, and Shoppergroup', 'Tax', '+%', 20, '', 0, 1, 1, '2010-02-21 00:00:00', '0000-00-00 00:00:00', '2010-02-21 23:07:33', 0, 0, 0, '', 1, 0);


--
-- Daten für Tabelle `#__vm_calc_category_xref`
--

INSERT IGNORE INTO `#__vm_calc_category_xref` (`id`, `calc_rule_id`, `calc_category`) VALUES
(2, 3, 1),
(5, 4, 2);


--
-- Daten für Tabelle `#__vm_calc_shoppergroup_xref`
--

INSERT IGNORE INTO `#__vm_calc_shoppergroup_xref` (`id`, `calc_rule_id`, `calc_shopper_group`) VALUES
(11, 0, 5);


--
-- Dumping data for table `#__vm_category`
--

INSERT IGNORE INTO `#__vm_category` (`category_id`, `vendor_id`, `category_name`, `category_description`, `category_thumb_image`, `category_full_image`, `published`, `cdate`, `mdate`, `category_browsepage`, `products_per_row`, `category_flypage`, `ordering`) VALUES
(1, 1, 'Hand Tools', 'Hand Tools', 'ee024e46399e792cc8ba4bf097d0fa6a.jpg', 'fc2f001413876a374484df36ed9cf775.jpg', 1, 950319905, 960304194, 'browse_3', 3, '', 1),
(2, 1, 'Power Tools', 'Power Tools', 'fc8802c7eaa1149bde98a541742217de.jpg', 'fe2f63f4c46023e3b33404c80bdd2bfe.jpg', 1, 950319916, 960304104, 'browse_4', 4, '', 2),
(3, 1, 'Garden Tools', 'Garden Tools', '702168cd91e8b7bbb7a36be56f86e9be.jpg', '756ff6d140e11079caf56955060f1162.jpg', 1, 950321122, 960304338, 'browse_2', 2, 'shop.garden_flypage', 3),
(4, 1, 'Outdoor Tools', 'Outdoor Tools', NULL, NULL, 1, 955626629, 958889528, 'browse_1', 1, NULL, 4),
(5, 1, 'Indoor Tools', 'Indoor Tools', NULL, NULL, 1, 958892894, 958892894, 'browse_1', 1, NULL, 5);

--
-- Dumping data for table `#__vm_category_xref`
--

INSERT IGNORE INTO `#__vm_category_xref` (`category_parent_id`, `category_child_id`, `category_list`, `category_shared`) VALUES
(0, 1, NULL, 1),
(0, 2, NULL, 1),
(0, 3, NULL, 1),
(2, 4, NULL, 1),
(2, 5, NULL, 1);


--
-- Dumping data for table `#__vm_product`
--

INSERT IGNORE INTO `#__vm_product` (`product_id`, `vendor_id`, `product_parent_id`, `product_sku`, `product_s_desc`, `product_desc`, `product_thumb_image`, `product_full_image`, `published`, `product_weight`, `product_weight_uom`, `product_length`, `product_width`, `product_height`, `product_lwh_uom`, `product_url`, `product_in_stock`, `low_stock_notification`, `product_available_date`, `product_availability`, `product_special`, `product_discount_id`, `ship_code_id`, `cdate`, `mdate`, `product_name`, `product_sales`, `attribute`, `custom_attribute`, `product_tax_id`, `product_unit`, `product_packaging`, `product_order_levels`, `intnotes`) VALUES
(1, 1, 0, 'G01', '<p>Nice hand shovel to dig with in the yard.</p>\r\n', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '8d886c5855770cc01a3b8a2db57f6600.jpg', 'cca3cd5db813ee6badf6a3598832f2fc.jpg', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 10, 5, 1072911600, '48h.gif', 'Y', 1, NULL, 950320117, 1084907592, 'Hand Shovel', 0, '', '', 2, '', 0, NULL, NULL),
(2, 1, 0, 'G02', 'A really long ladder to reach high places.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', 'ffd5d5ace2840232c8c32de59553cd8d.jpg', '8cb8d644ef299639b7eab25829d13dbc.jpg', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 76, 5, 1072911600, '3-5d.gif', 'N', 0, NULL, 950320180, 1084907618, 'Ladder', 0, '', '', 2, '', 0, NULL, NULL),
(3, 1, 0, 'G03', 'Nice shovel.  You can dig your way to China with this one.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '8147a3a9666aec0296525dbd81f9705e.jpg', '520efefd6d7977f91b16fac1149c7438.jpg', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 32, 5, 1072911600, '7d.gif', 'N', 0, NULL, 950320243, 1084907765, 'Shovel', 0, 'Size,XL[+1.99],M,S[-2.99];Colour,Red,Green,Yellow,ExpensiveColor[=24.00]', '', 2, '', 0, NULL, NULL),
(4, 1, 0, 'G04', 'This shovel is smaller but you''ll be able to dig real quick.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', 'a04395a8aefacd9c1659ebca4dbfd4ba.jpg', '1b0c96d67abdbea648cd0ea96fd6abcb.jpg', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 98, 5, 1088632800, 'on-order.gif', 'N', 0, NULL, 950320378, 1084907867, 'Smaller Shovel', 0, 'Size,big[+2.99],medium;Color,red[+0.99],green[-0.99]', '', 2, '', 0, NULL, NULL),
(5, 1, 0, 'H01', 'This saw is great for getting cutting through downed limbs.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '1aa8846d3cfe3504b2ccaf7c23bb748f.jpg', 'e614ba08c3ee0c2adc62fd9e5b9440eb.jpg', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 32, 5, 1072911600, '1-4w.gif', 'Y', 2, NULL, 950321256, 1084907669, 'Nice Saw', 0, 'Size,big,small,medium;Power,100W,200W,500W', '', 2, '', 0, NULL, NULL),
(6, 1, 0, 'H02', 'A great hammer to hammer away with.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', 'dccb8223891a17d752bfc1477d320da9.jpg', '578563851019e01264a9b40dcf1c4ab6.jpg', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 500, 5, 1072911600, '24h.gif', 'N', 0, NULL, 950321631, 1084907947, 'Hammer', 0, 'Size,big,medium,small;Material,wood and metal,plastic and metal[-0.99]', '', 2, '', 0, NULL, NULL),
(7, 1, 0, 'P01', 'Don''t do it with an axe.  Get a chain saw.', '\r\n<ul>  <li>Tool-free tensioner for easy, convenient chain adjustment  </li><li>3-Way Auto Stop; stops chain a fraction of a second  </li><li>Automatic chain oiler regulates oil for proper chain lubrication  </li><li>Small radius guide bar reduces kick-back  </li></ul>  <br />  <b>Specifications</b><br />  12.5 AMPS   <br />   16" Bar Length   <br />   3.5 HP   <br />   8.05 LBS. Weight   <br />\r\n', '8716aefc3b0dce8870360604e6eb8744.jpg', 'c3a5bf074da14f30c849d13a2dd87d2c.jpg', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 45, 5, 1088632800, '48h.gif', 'N', 0, NULL, 950321725, 1084907512, 'Chain Saw', 0, '', '', 2, '', 0, NULL, NULL),
(8, 1, 0, 'P02', 'Cut rings around wood.  This saw can handle the most delicate projects.', '\r\n<ul>  <li>Patented Sightline; Window provides maximum visibility for straight cuts  </li><li>Adjustable dust chute for cleaner work area  </li><li>Bail handle for controlled cutting in 90� to 45� applications  </li><li>1-1/2 to 2-1/2 lbs. lighter and 40% less noise than the average circular saw                     </li><li><b>Includes:</b>Carbide blade  </li></ul>  <br />  <b>Specifications</b><br />  10.0 AMPS   <br />   4,300 RPM   <br />   Capacity: 2-1/16" at 90�, 1-3/4" at 45�<br />\r\n', 'b4a748303d0d996b29d5a1e1d1112537.jpg', '9a4448bb13e2f7699613b2cfd7cd51ad.jpg', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 33, 5, 1072911600, '3-5d.gif', 'Y', 1, NULL, 950321795, 1084907537, 'Circular Saw', 0, 'Size,XL[+1],M,S[-2];Power,strong,middle,poor[=24]', '', 2, '', 0, NULL, NULL),
(9, 1, 0, 'P03', 'Drill through anything.  This drill has the power you need for those demanding hole boring duties.', '\r\n<font color="#000000" size="3"><ul><li>High power motor and double gear reduction for increased durability and improved performance  </li><li>Mid-handle design and two finger trigger for increased balance and comfort  </li><li>Variable speed switch with lock-on button for continuous use  </li><li><b>Includes:</b> Chuck key &amp; holder  </li></ul>  <br />  <b>Specifications</b><br />  4.0 AMPS   <br />   0-1,350 RPM   <br />   Capacity: 3/8" Steel, 1" Wood   <br /><br />  </font>\r\n', 'c70a3f47baf9a4020aeeee919eb3fda4.jpg', '1ff5f2527907ca86103288e1b7cc3446.jpg', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 3, 5, 1072911600, '2-3d.gif', 'N', 0, NULL, 950321879, 1084907557, 'Drill', 0, '', '', 2, '', 0, NULL, NULL),
(10, 1, 0, 'P04', 'Blast away that paint job from the past.  Use this power sander to really show them you mean business.', '\r\n<ul>  <li>Lever activated paper clamps for simple sandpaper changes  </li><li>Dust sealed rocker switch extends product life and keeps dust out of motor  </li><li>Flush sands on three sides to get into corners  </li><li>Front handle for extra control  </li><li>Dust extraction port for cleaner work environment   </li></ul>  <br />  <b>Specifications</b><br />  1.2 AMPS    <br />   10,000 OPM    <br />\r\n', '7a36a05526e93964a086f2ddf17fc609.jpg', '480655b410d98a5cc3bef3927e786866.jpg', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 2, 5, 1072911600, '1-2m.gif', 'N', 2, NULL, 950321963, 1084907719, 'Power Sander', 0, 'Size,big,medium,small;Power,100W,200W,300W', '', 2, '', 0, NULL, NULL),
(11, 1, 1, 'G01-01', '', '', '', '', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 0, 5, 0, '', '', 0, NULL, 955696949, 960372163, 'Hand Shovel', 0, NULL, '', 0, '', 0, NULL, NULL),
(12, 1, 1, 'G01-02', '', '', '', '', '1', '10.0000', '', '0.0000', '0.0000', '0.0000', '', '', 0, 5, 0, '', '', 0, NULL, 955697006, 960372187, 'Hand Shovel', 0, NULL, '', 0, '', 0, NULL, NULL),
(13, 1, 1, 'G01-03', '', '', '', '', '1', '10.0000', '', '0.0000', '0.0000', '0.0000', '', '', 0, 5, 0, '', '', 0, NULL, 955697044, 960372206, 'Hand Shovel', 0, NULL, '', 0, '', 0, NULL, NULL),
(14, 1, 2, 'L01', '', '', '', '', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 22, 5, 1072911600, '', 'N', 0, NULL, 962351149, 1084902820, 'Metal Ladder', 0, NULL, '', 2, '', 0, NULL, NULL),
(15, 1, 2, 'L02', '', '', '', '', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 0, 5, 0, '', '', 0, NULL, 962351165, 962351165, 'Wooden Ladder', 0, NULL, '', 0, '', 0, NULL, NULL),
(16, 1, 2, 'L03', '', '', '', '', '1', '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 0, 5, 0, '', '', 0, NULL, 962351180, 962351180, 'Plastic Ladder', 0, NULL, '', 0, '', 0, NULL, NULL);

--
-- Dumping data for table `#__vm_product_attribute`
--

INSERT IGNORE INTO `#__vm_product_attribute` (`attribute_id`, `product_id`, `attribute_name`, `attribute_value`) VALUES
(1, 11, 'Color', 'Red'),
(2, 12, 'Color', 'Green'),
(3, 13, 'Color', 'Blue'),
(4, 11, 'Size', 'Small'),
(5, 12, 'Size', 'Medium'),
(6, 13, 'Size', 'Large'),
(7, 14, 'Material', 'Metal'),
(8, 15, 'Material', 'Wood'),
(9, 16, 'Material', 'Plastic');

--
-- Dumping data for table `#__vm_product_attribute_sku`
--

INSERT IGNORE INTO `#__vm_product_attribute_sku` (`product_id`, `attribute_name`, `attribute_list`) VALUES
(1, 'Color', 1),
(1, 'Size', 2),
(2, 'Material', 1);

--
-- Dumping data for table `#__vm_product_category_xref`
--

INSERT IGNORE INTO `#__vm_product_category_xref` (`category_id`, `product_id`, `product_list`) VALUES
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
-- Dumping data for table `#__vm_product_discount`
--

INSERT IGNORE INTO `#__vm_product_discount` (`discount_id`, `amount`, `is_percent`, `start_date`, `end_date`) VALUES
(1, '20.00', 1, 1097704800, 1194390000),
(2, '2.00', 0, 1098655200, 0);

--
-- Dumping data for table `#__vm_product_mf_xref`
--

INSERT IGNORE INTO `#__vm_product_mf_xref` (`product_id`, `manufacturer_id`) VALUES
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
-- Dumping data for table `#__vm_product_price`
--

INSERT IGNORE INTO `#__vm_product_price` (`product_price_id`, `product_id`, `product_price`, `product_currency`, `product_price_vdate`, `product_price_edate`, `cdate`, `mdate`, `shopper_group_id`, `price_quantity_start`, `price_quantity_end`) VALUES
(1, 5, '24.99000', 'USD', 0, 0, 950321309, 950321309, 5, 0, 0),
(2, 1, '4.99000', 'USD', 0, 0, 950321324, 950321324, 5, 0, 0),
(3, 2, '49.99000', 'USD', 0, 0, 950321340, 950321340, 5, 0, 0),
(4, 3, '24.99000', 'USD', 0, 0, 950321368, 950321368, 5, 0, 0),
(5, 4, '19.99000', 'USD', 0, 0, 950321385, 950321385, 5, 0, 0),
(6, 6, '1.00000', 'USD', 0, 0, 950321686, 963808699, 5, 0, 0),
(7, 7, '149.99000', 'USD', 0, 0, 950321754, 966506270, 5, 0, 0),
(8, 8, '220.90000', 'USD', 0, 0, 950321833, 955614388, 5, 0, 0),
(9, 9, '48.12000', 'USD', 0, 0, 950321933, 950321933, 5, 0, 0),
(10, 10, '74.99000', 'USD', 0, 0, 950322005, 950322005, 5, 0, 0),
(11, 1, '2.99000', 'USD', 0, 0, 955626841, 955626841, 6, 0, 0),
(12, 13, '14.99000', 'USD', 0, 0, 955697213, 955697213, 5, 0, 0),
(13, 14, '79.99000', 'USD', 0, 0, 962351197, 962351271, 5, 0, 0),
(14, 15, '49.99000', 'USD', 0, 0, 962351233, 962351233, 5, 0, 0),
(15, 16, '59.99000', 'USD', 0, 0, 962351259, 962351259, 5, 0, 0),
(16, 7, '2.99000', 'USD', 0, 0, 966589140, 966589140, 6, 0, 0);

--
-- Dumping data for table `#__vm_shopper_group`
--

INSERT IGNORE INTO `#__vm_shopper_group` (`shopper_group_id`, `vendor_id`, `shopper_group_name`, `shopper_group_desc`, `default`) VALUES
(6, 1, 'Gold Level', 'Gold Level Shoppers.', 0),
(7, 1, 'Wholesale', 'Shoppers that can buy at wholesale.', 0);

--
-- Dumping data for table `#__vm_tax_rate`
--

INSERT IGNORE INTO `#__vm_tax_rate` (`tax_rate_id`, `vendor_id`, `tax_state`, `tax_country`, `mdate`, `tax_rate`) VALUES
(2, 1, 'CA', 'USA', 964565926, '0.0925');

--
-- Dumping data for table `#__vm_orders`
--

INSERT IGNORE INTO `#__vm_orders` (`order_id`, `user_id`, `vendor_id`, `order_number`, `user_info_id`, `order_total`, `order_subtotal`, `order_tax`, `order_tax_details`, `order_shipping`, `order_shipping_tax`, `coupon_discount`, `coupon_code`, `order_discount`, `order_currency`, `order_status`, `cdate`, `mdate`, `ship_method_id`, `customer_note`, `ip_address`) VALUES
(1, 62, 1, CONCAT((SELECT `user_info_id` FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" AND `address_type` = "BT" LIMIT 1), '_cc021a6d408b33bc728eae21ca529'), (SELECT `user_info_id` FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" AND `address_type` = "BT" LIMIT 1), '17.47000', '15.99085', '1.48', 'a:0:{}', '0.00', '0.00', '0.00', '', '0.00', '', 'P', 1272282774, 1272282774, 'flex|STD|Standard Shipping over |0', '', '127.0.0.1'),
(2, 62, 1, CONCAT((SELECT `user_info_id` FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" AND `address_type` = "BT" LIMIT 1), '_6cd114591454cc44b9d691c73fdc9'), (SELECT `user_info_id` FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" AND `address_type` = "BT" LIMIT 1), '253.15000', '231.71625', '21.43', 'a:0:{}', '0.00', '0.00', '0.00', '', '0.00', '', 'P', 1274812633, 1274812633, 'flex|STD|Standard Shipping over |0', '', '::1');

--
-- Dumping data for table `#__vm_order_history`
--

INSERT IGNORE INTO `#__vm_order_history` (`order_status_history_id`, `order_id`, `order_status_code`, `date_added`, `customer_notified`, `comments`) VALUES
(1, 1, 'P', '2010-04-26 13:52:54', 1, ''),
(2, 2, 'P', '2010-05-25 20:37:13', 1, '');

--
-- Dumping data for table `#__vm_order_item`
--

INSERT IGNORE INTO `#__vm_order_item` (`order_item_id`, `order_id`, `user_info_id`, `vendor_id`, `product_id`, `order_item_sku`, `order_item_name`, `product_quantity`, `product_item_price`, `product_final_price`, `order_item_currency`, `order_status`, `cdate`, `mdate`, `product_attribute`) VALUES
(1, 1, (SELECT `user_info_id` FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" AND `address_type` = "BT" LIMIT 1), 1, 5, 'H01', 'Nice Saw', 1, '15.98739', '17.47', '', 'P', 1272282774, 1272282774, 'Size: big<br/> Power: 100W'),
(2, 2, (SELECT `user_info_id` FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" AND `address_type` = "BT" LIMIT 1), 1, 4, 'G04', 'Smaller Shovel', 2, '15.69177', '17.14', '', 'P', 1274812633, 1274812633, 'Size: medium<br/> Color: green (- 1)'),
(3, 2, (SELECT `user_info_id` FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" AND `address_type` = "BT" LIMIT 1), 1, 5, 'H01', 'Nice Saw', 1, '18.94734', '20.70', '', 'P', 1274812633, 1274812633, 'Size: big<br/> Power: 100W'),
(4, 2, (SELECT `user_info_id` FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" AND `address_type` = "BT" LIMIT 1), 1, 8, 'P02', 'Circular Saw', 1, '181.39420', '198.17', '', 'P', 1274812633, 1274812633, 'Size: XL (+ 1)<br/> Power: strong');

--
-- Dumping data for table `#__vm_order_payment`
--

INSERT IGNORE INTO `#__vm_order_payment` (`order_id`, `payment_method_id`, `order_payment_code`, `order_payment_number`, `order_payment_expire`, `order_payment_name`, `order_payment_log`, `order_payment_trans_id`) VALUES
(1, 1, '', NULL, NULL, NULL, NULL, NULL),
(2, 1, '', NULL, NULL, NULL, NULL, NULL);

--
-- Dumping data for table `#__vm_order_user_info`
--

INSERT IGNORE INTO `#__vm_order_user_info` (`order_info_id`, `order_id`, `user_id`, `address_type`, `address_type_name`, `company`, `title`, `last_name`, `first_name`, `middle_name`, `phone_1`, `phone_2`, `fax`, `address_1`, `address_2`, `city`, `state`, `country`, `zip`, `extra_field_1`, `extra_field_2`, `extra_field_3`, `extra_field_4`, `extra_field_5`, `bank_account_nr`, `bank_name`, `bank_sort_code`, `bank_iban`, `bank_account_holder`, `bank_account_type`) VALUES
(1, 1, (SELECT `user_id` FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" LIMIT 1), 'BT', '-default-', "Washupito's the User" ,'Mr.' ,'upito' ,'Wash' ,'the cheapest' ,'555-555-555' ,'' ,'' ,'vendorra road 8' ,'' ,'Canangra' ,'' ,'13' ,'1234' ,'' ,'' ,'' ,NULL ,NULL ,'' ,'' ,'' ,'' ,'' ,'Checking'),
(2, 2, (SELECT `user_id` FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" LIMIT 1), 'BT', '-default-', "Washupito's the User" ,'Mr.' ,'upito' ,'Wash' ,'the cheapest' ,'555-555-555' ,'' ,'' ,'vendorra road 8' ,'' ,'Canangra' ,'' ,'13' ,'1234' ,'' ,'' ,'' ,NULL ,NULL ,'' ,'' ,'' ,'' ,'' ,'Checking');

-- Hmm.... what am I doint wrong here?
-- INSERT IGNORE INTO `#__vm_order_user_info` (`order_info_id`, `order_id`, `user_id`, `address_type`, `address_type_name`, `company`, `title`, `last_name`, `first_name`, `middle_name`, `phone_1`, `phone_2`, `fax`, `address_1`, `address_2`, `city`, `state`, `country`, `zip`, `extra_field_1`, `extra_field_2`, `extra_field_3`, `extra_field_4`, `extra_field_5`, `bank_account_nr`, `bank_name`, `bank_sort_code`, `bank_iban`, `bank_account_holder`, `bank_account_type`) VALUES
-- (1, 1, (SELECT `user_id` FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" LIMIT 1), 'BT', '-default-', (SELECT QUOTE(`company`), QUOTE(`title`), QUOTE(`last_name`), QUOTE(`first_name`), QUOTE(`middle_name`), QUOTE(`phone_1`), QUOTE(`phone_2`), QUOTE(`fax`), QUOTE(`address_1`), QUOTE(`address_2`), QUOTE(`city`), QUOTE(`state_id`), QUOTE(`country_id`), QUOTE(`zip`), QUOTE(`extra_field_1`), QUOTE(`extra_field_2`), QUOTE(`extra_field_3`), QUOTE(`extra_field_4`), QUOTE(`extra_field_5`), QUOTE(`bank_account_nr`), QUOTE(`bank_name`), QUOTE(`bank_sort_code`), QUOTE(`bank_iban`), QUOTE(`bank_account_holder`), QUOTE(`bank_account_type`) FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" LIMIT 1)),
-- (2, 2, (SELECT `user_id` FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" LIMIT 1), 'BT', '-default-', (SELECT QUOTE(`company`), QUOTE(`title`), QUOTE(`last_name`), QUOTE(`first_name`), QUOTE(`middle_name`), QUOTE(`phone_1`), QUOTE(`phone_2`), QUOTE(`fax`), QUOTE(`address_1`), QUOTE(`address_2`), QUOTE(`city`), QUOTE(`state_id`), QUOTE(`country_id`), QUOTE(`zip`), QUOTE(`extra_field_1`), QUOTE(`extra_field_2`), QUOTE(`extra_field_3`), QUOTE(`extra_field_4`), QUOTE(`extra_field_5`), QUOTE(`bank_account_nr`), QUOTE(`bank_name`), QUOTE(`bank_sort_code`), QUOTE(`bank_iban`), QUOTE(`bank_account_holder`), QUOTE(`bank_account_type`) FROM `#__vm_user_info` WHERE `company` = "Washupito's the User" LIMIT 1));

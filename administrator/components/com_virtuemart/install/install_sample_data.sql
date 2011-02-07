-- VirtueMart table data SQL script
-- This will insert all required data into the VirtueMart tables


--
--  Dumping data for `#__vm_calc`
--

INSERT IGNORE INTO `#__vm_calc` (`calc_id`, `calc_vendor_id`, `calc_name`, `calc_descr`, `calc_kind`, `calc_value_mathop`, `calc_value`, `calc_currency`, `ordering`, `calc_shopper_published`, `calc_vendor_published`, `publish_up`, `publish_down`, `modified`, `calc_qualify`, `calc_affected`, `calc_amount_cond`, `calc_amount_dimunit`, `published`, `shared`) VALUES
(1, 1, 'Tax 9.25%', 'A simpel tax for all products regardless the category', 'Tax', '+%', 9.25, '47', 0, 1, 1, '2010-02-21 00:00:00', '0000-00-00 00:00:00', '2010-02-21 18:26:27', 0, 0, 0, '', 1, 0),
(2, 1, 'Discount for all Hand Tools', 'Discount for all Hand Tools 2 euro', 'DBTax', '-', 2, '47', 1, 1, 1, '2010-02-21 00:00:00', '0000-00-00 00:00:00', '2010-02-21 22:51:39', 0, 0, 0, '', 1, 0),
(3, 1, 'Duty for Powertools', 'Ah tax that only effects a certain category, Power Tools, and Shoppergroup', 'Tax', '+%', 20, '47', 0, 1, 1, '2010-02-21 00:00:00', '0000-00-00 00:00:00', '2010-02-21 23:07:33', 0, 0, 0, '', 1, 0);


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

INSERT INTO `#__vm_category` (`category_id`, `vendor_id`, `category_name`, `category_description`, `category_thumb_image`, `category_full_image`, `published`, `cdate`, `mdate`, `category_template`, `category_layout`, `category_product_layout`, `products_per_row`, `ordering`, `limit_list_start`, `limit_list_step`, `limit_list_max`, `limit_list_initial`, `metadesc`, `metakey`, `metarobot`, `metaauthor`) VALUES
(1, 1, 'Hand Tools', 'Hand Tools', 'ee024e46399e792cc8ba4bf097d0fa6a.jpg', 'fc2f001413876a374484df36ed9cf775.jpg', 1, 950319905, 960304194, '', '', '', 3, 1, NULL, NULL, NULL, NULL, '', '', '', ''),
(2, 1, 'Power Tools', 'Power Tools', 'fc8802c7eaa1149bde98a541742217de.jpg', 'fe2f63f4c46023e3b33404c80bdd2bfe.jpg', 1, 950319916, 960304104, '', '', '', 4, 2, NULL, NULL, NULL, NULL, '', '', '', ''),
(3, 1, 'Garden Tools', 'Garden Tools', '702168cd91e8b7bbb7a36be56f86e9be.jpg', '756ff6d140e11079caf56955060f1162.jpg', 1, 950321122, 960304338, '', '', '', 2, 3, NULL, NULL, NULL, NULL, '', '', '', ''),
(4, 1, 'Outdoor Tools', 'Outdoor Tools', NULL, NULL, 1, 955626629, 958889528, '', '', '', 1, 4, NULL, NULL, NULL, NULL, '', '', '', ''),
(5, 1, 'Indoor Tools', 'Indoor Tools', NULL, NULL, 1, 958892894, 958892894, '', '', '', 1, 5, NULL, NULL, NULL, NULL, '', '', '', '');
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
-- Dumping data for table `#__vm_manufacturer`
--

INSERT INTO `#__vm_manufacturer` (`manufacturer_id`, `mf_name`, `mf_email`, `mf_desc`, `mf_category_id`, `mf_url`, `mf_thumb_image`, `mf_full_image`) VALUES
(1, 'Manufacturer', 'info@manufacturer.com', 'An example for a manufacturer', 1, 'http://www.a-url.com', '', '');


--
-- Dumping data for table `#__vm_manufacturer_category`
--

INSERT INTO `#__vm_manufacturer_category` (`mf_category_id`, `mf_category_name`, `mf_category_desc`) VALUES
(1, '-default-', 'This is the default manufacturer category');


--
-- Dumping data for table `#__vm_product`
--

INSERT INTO `#__vm_product` (`product_id`, `vendor_id`, `product_parent_id`, `product_sku`, `product_s_desc`, `product_desc`, `product_thumb_image`, `product_full_image`, `published`, `product_weight`, `product_weight_uom`, `product_length`, `product_width`, `product_height`, `product_lwh_uom`, `product_url`, `product_in_stock`, `low_stock_notification`, `product_available_date`, `product_availability`, `product_special`, `ship_code_id`, `cdate`, `mdate`, `product_name`, `product_sales`, `attribute`, `custom_attribute`, `product_unit`, `product_packaging`, `product_order_levels`, `intnotes`, `metadesc`, `metakey`, `metarobot`, `metaauthor`, `layout`) VALUES
(1, 1, 0, 'G01', '<p>Nice hand shovel to dig with in the yard.</p>\r\n', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '8d886c5855770cc01a3b8a2db57f6600.jpg', 'cca3cd5db813ee6badf6a3598832f2fc.jpg', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 10, 5, 1072911600, '48h.gif', 'Y', NULL, 950320117, 1084907592, 'Hand Shovel', 0, '', '', '', 0, NULL, NULL, '', '', '', '', ''),
(2, 1, 0, 'G02', 'A really long ladder to reach high places.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', 'ffd5d5ace2840232c8c32de59553cd8d.jpg', '8cb8d644ef299639b7eab25829d13dbc.jpg', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 76, 5, 1072911600, '3-5d.gif', 'N', NULL, 950320180, 1084907618, 'Ladder', 0, '', '', '', 0, NULL, NULL, '', '', '', '', ''),
(3, 1, 0, 'G03', 'Nice shovel.  You can dig your way to China with this one.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '8147a3a9666aec0296525dbd81f9705e.jpg', '520efefd6d7977f91b16fac1149c7438.jpg', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 32, 5, 1072911600, '7d.gif', 'N', NULL, 950320243, 1084907765, 'Shovel', 0, 'Size,XL[+1.99],M,S[-2.99];Colour,Red,Green,Yellow,ExpensiveColor[=24.00]', '', '', 0, NULL, NULL, '', '', '', '', ''),
(4, 1, 0, 'G04', 'This shovel is smaller but you''ll be able to dig real quick.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', 'a04395a8aefacd9c1659ebca4dbfd4ba.jpg', '1b0c96d67abdbea648cd0ea96fd6abcb.jpg', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 98, 5, 1088632800, 'on-order.gif', 'N', NULL, 950320378, 1084907867, 'Smaller Shovel', 0, 'Size,big[+2.99],medium;Color,red[+0.99],green[-0.99]', '', '', 0, NULL, NULL, '', '', '', '', ''),
(5, 1, 0, 'H01', 'This saw is great for getting cutting through downed limbs.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', '1aa8846d3cfe3504b2ccaf7c23bb748f.jpg', 'e614ba08c3ee0c2adc62fd9e5b9440eb.jpg', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 32, 5, 1072911600, '1-4w.gif', 'Y', NULL, 950321256, 1084907669, 'Nice Saw', 0, 'Size,big,small,medium;Power,100W,200W,500W', '', '', 0, NULL, NULL, '', '', '', '', ''),
(6, 1, 0, 'H02', 'A great hammer to hammer away with.', '\r\n<ul>  <li>Hand crafted handle with maximum grip torque  </li><li>Titanium tipped shovel platter  </li><li>Half degree offset for less accidents  </li><li>Includes HowTo Video narrated by Bob Costas  </li></ul>    <b>Specifications</b><br />  5" Diameter<br />  Tungsten handle tip with 5 point loft<br />\r\n', 'dccb8223891a17d752bfc1477d320da9.jpg', '578563851019e01264a9b40dcf1c4ab6.jpg', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 500, 5, 1072911600, '24h.gif', 'N', NULL, 950321631, 1084907947, 'Hammer', 0, 'Size,big,medium,small;Material,wood and metal,plastic and metal[-0.99]', '', '', 0, NULL, NULL, '', '', '', '', ''),
(7, 1, 0, 'P01', 'Don''t do it with an axe.  Get a chain saw.', '\r\n<ul>  <li>Tool-free tensioner for easy, convenient chain adjustment  </li><li>3-Way Auto Stop; stops chain a fraction of a second  </li><li>Automatic chain oiler regulates oil for proper chain lubrication  </li><li>Small radius guide bar reduces kick-back  </li></ul>  <br />  <b>Specifications</b><br />  12.5 AMPS   <br />   16" Bar Length   <br />   3.5 HP   <br />   8.05 LBS. Weight   <br />\r\n', '8716aefc3b0dce8870360604e6eb8744.jpg', 'c3a5bf074da14f30c849d13a2dd87d2c.jpg', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 45, 5, 1088632800, '48h.gif', 'N', NULL, 950321725, 1084907512, 'Chain Saw', 0, '', '', '', 0, NULL, NULL, '', '', '', '', ''),
(8, 1, 0, 'P02', 'Cut rings around wood.  This saw can handle the most delicate projects.', '\r\n<ul>  <li>Patented Sightline; Window provides maximum visibility for straight cuts  </li><li>Adjustable dust chute for cleaner work area  </li><li>Bail handle for controlled cutting in 90ï¿½ to 45ï¿½ applications  </li><li>1-1/2 to 2-1/2 lbs. lighter and 40% less noise than the average circular saw                     </li><li><b>Includes:</b>Carbide blade  </li></ul>  <br />  <b>Specifications</b><br />  10.0 AMPS   <br />   4,300 RPM   <br />   Capacity: 2-1/16" at 90ï¿½, 1-3/4" at 45ï¿½<br />\r\n', 'b4a748303d0d996b29d5a1e1d1112537.jpg', '9a4448bb13e2f7699613b2cfd7cd51ad.jpg', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 33, 5, 1072911600, '3-5d.gif', 'Y', NULL, 950321795, 1084907537, 'Circular Saw', 0, 'Size,XL[+1],M,S[-2];Power,strong,middle,poor[=24]', '', '', 0, NULL, NULL, '', '', '', '', ''),
(9, 1, 0, 'P03', 'Drill through anything.  This drill has the power you need for those demanding hole boring duties.', '\r\n<font color="#000000" size="3"><ul><li>High power motor and double gear reduction for increased durability and improved performance  </li><li>Mid-handle design and two finger trigger for increased balance and comfort  </li><li>Variable speed switch with lock-on button for continuous use  </li><li><b>Includes:</b> Chuck key &amp; holder  </li></ul>  <br />  <b>Specifications</b><br />  4.0 AMPS   <br />   0-1,350 RPM   <br />   Capacity: 3/8" Steel, 1" Wood   <br /><br />  </font>\r\n', 'c70a3f47baf9a4020aeeee919eb3fda4.jpg', '1ff5f2527907ca86103288e1b7cc3446.jpg', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 3, 5, 1072911600, '2-3d.gif', 'N', NULL, 950321879, 1084907557, 'Drill', 0, '', '', '', 0, NULL, NULL, '', '', '', '', ''),
(10, 1, 0, 'P04', 'Blast away that paint job from the past.  Use this power sander to really show them you mean business.', '\r\n<ul>  <li>Lever activated paper clamps for simple sandpaper changes  </li><li>Dust sealed rocker switch extends product life and keeps dust out of motor  </li><li>Flush sands on three sides to get into corners  </li><li>Front handle for extra control  </li><li>Dust extraction port for cleaner work environment   </li></ul>  <br />  <b>Specifications</b><br />  1.2 AMPS    <br />   10,000 OPM    <br />\r\n', '7a36a05526e93964a086f2ddf17fc609.jpg', '480655b410d98a5cc3bef3927e786866.jpg', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 2, 5, 1072911600, '1-2m.gif', 'N', NULL, 950321963, 1084907719, 'Power Sander', 0, 'Size,big,medium,small;Power,100W,200W,300W', '', '', 0, NULL, NULL, '', '', '', '', ''),
(11, 1, 1, 'G01-01', '', '', '', '', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 0, 5, 0, '', '', NULL, 955696949, 960372163, 'Hand Shovel', 0, NULL, '', '', 0, NULL, NULL, '', '', '', '', ''),
(12, 1, 1, 'G01-02', '', '', '', '', 1, '10.0000', '', '0.0000', '0.0000', '0.0000', '', '', 0, 5, 0, '', '', NULL, 955697006, 960372187, 'Hand Shovel', 0, NULL, '', '', 0, NULL, NULL, '', '', '', '', ''),
(13, 1, 1, 'G01-03', '', '', '', '', 1, '10.0000', '', '0.0000', '0.0000', '0.0000', '', '', 0, 5, 0, '', '', NULL, 955697044, 960372206, 'Hand Shovel', 0, NULL, '', '', 0, NULL, NULL, '', '', '', '', ''),
(14, 1, 2, 'L01', '', '', '', '', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 22, 5, 1072911600, '', 'N', NULL, 962351149, 1084902820, 'Metal Ladder', 0, NULL, '', '', 0, NULL, NULL, '', '', '', '', ''),
(15, 1, 2, 'L02', '', '', '', '', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 0, 5, 0, '', '', NULL, 962351165, 962351165, 'Wooden Ladder', 0, NULL, '', '', 0, NULL, NULL, '', '', '', '', ''),
(16, 1, 2, 'L03', '', '', '', '', 1, '10.0000', 'pounds', '0.0000', '0.0000', '0.0000', 'inches', '', 0, 5, 0, '', '', NULL, 962351180, 962351180, 'Plastic Ladder', 0, NULL, '', '', 0, NULL, NULL, '', '', '', '', '');


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

INSERT INTO `#__vm_product_price` (`product_price_id`, `product_id`, `product_price`, `override`, `product_override_price`, `product_tax_id`, `product_discount_id`, `product_currency`, `product_price_vdate`, `product_price_edate`, `cdate`, `mdate`, `shopper_group_id`, `price_quantity_start`, `price_quantity_end`) VALUES
(1, 5, '24.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 950321309, 950321309, 5, 0, 0),
(2, 1, '4.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 950321324, 950321324, 5, 0, 0),
(3, 2, '49.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 950321340, 950321340, 5, 0, 0),
(4, 3, '24.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 950321368, 950321368, 5, 0, 0),
(5, 4, '17.99000', 1, '77.00000', NULL, NULL, '144', 0, 0, 950321385, 950321385, 5, 0, 0),
(6, 6, '1.00000', 0, '0.00000', NULL, NULL, '144', 0, 0, 950321686, 963808699, 5, 0, 0),
(7, 7, '149.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 950321754, 966506270, 5, 0, 0),
(8, 8, '220.90000', 0, '0.00000', NULL, NULL, '144', 0, 0, 950321833, 955614388, 5, 0, 0),
(9, 9, '48.12000', 0, '0.00000', NULL, NULL, '144', 0, 0, 950321933, 950321933, 5, 0, 0),
(10, 10, '74.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 950322005, 950322005, 5, 0, 0),
(11, 1, '2.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 955626841, 955626841, 6, 0, 0),
(12, 13, '14.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 955697213, 955697213, 5, 0, 0),
(13, 14, '79.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 962351197, 962351271, 5, 0, 0),
(14, 15, '49.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 962351233, 962351233, 5, 0, 0),
(15, 16, '59.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 962351259, 962351259, 5, 0, 0),
(16, 7, '2.99000', 0, '0.00000', NULL, NULL, '144', 0, 0, 966589140, 966589140, 6, 0, 0);

--
-- Dumping data for table `#__vm_shopper_group`
--

INSERT IGNORE INTO `#__vm_shopper_group` (`shopper_group_id`, `vendor_id`, `shopper_group_name`, `shopper_group_desc`, `default`) VALUES
(NULL, 1, 'Gold Level', 'Gold Level Shoppers.', 0),
(NULL, 1, 'Wholesale', 'Shoppers that can buy at wholesale.', 0);

--
-- Dumping data for table `#__vm_zone_shipping`
--

INSERT INTO `#__vm_zone_shipping` (`zone_id`, `zone_name`, `zone_cost`, `zone_limit`, `zone_description`, `zone_tax_rate`) VALUES
(1, 'Default', '6.00', '35.00', 'This is the default Shipping Zone. This is the zone information that all countries will use until you assign each individual country to a Zone.', 2),
(2, 'Zone 1', '1000.00', '10000.00', 'This is a zone example', 2),
(3, 'Zone 2', '2.00', '22.00', 'This is the second zone. You can use this for notes about this zone', 2),
(4, 'Zone 3', '11.00', '64.00', 'Another usefull thing might be details about this zone or special instructions.', 2);
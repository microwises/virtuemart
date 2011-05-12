 -- VirtueMart table data SQL script
-- This will insert all essential data into the VirtueMart tables


--
-- Configuration data has been moved to virtuemart_defaults.cfg
--

--
-- Dumping data for table `#__virtuemart_adminmenuentries`
--
INSERT INTO `#__virtuemart_adminmenuentries` (`id`, `module_id`, `parent_id`, `name`, `link`, `depends`, `icon_class`, `ordering`, `published`, `tooltip`, `view`, `task`) VALUES
(null, 1, 0, 'COM_VIRTUEMART_CATEGORY_LIST_MNU', '', '', 'vmicon vmicon-16-content', 1, 1, '', 'category', ''),
(null, 1, 0, 'COM_VIRTUEMART_PRODUCT_LIST_MNU', '', '', 'vmicon vmicon-16-content', 2, 1, '', 'product', 'product'),
(null, 1, 0, 'COM_VIRTUEMART_PRODUCT_CUSTOM_LIST_MNU', '', '', 'vmicon vmicon-16-content', 5, 1, '', 'custom', ''),
(null, 1, 0, 'COM_VIRTUEMART_PRODUCT_FILES_LIST_MNU', '', '', 'vmicon vmicon-16-content', 6, 1, '', 'media', 'media'),
(null, 1, 0, 'COM_VIRTUEMART_PRODUCT_INVENTORY_MNU', '', '', 'vmicon vmicon-16-install', 7, 1, '', 'inventory', 'inventory'),
(null, 1, 0, 'COM_VIRTUEMART_CALCULATOR_MNU', '', '', 'vmicon vmicon-16-content', 8, 1, '', 'calc', ''),
(null, 1, 0, 'COM_VIRTUEMART_REVIEWS_MNU', '', '', 'vmicon vmicon-16-content', 9, 1, '', 'ratings', 'ratings'),
(null, 1, 0, 'COM_VIRTUEMART_SPECIAL_PRODUCTS', '', '', 'vmicon vmicon-16-content', 10, 0, '', 'productspecial', 'productSpecial'),
(null, 2, 0, 'COM_VIRTUEMART_ORDER_LIST_MNU', '', '', 'vmicon vmicon-16-content', 1, 1, '', 'orders', 'orders'),
(null, 2, 0, 'COM_VIRTUEMART_COUPON_LIST_MNU', '', '', 'vmicon vmicon-16-content', 2, 1, '', 'coupon', ''),
(null, 2, 0, 'COM_VIRTUEMART_REPORT_BASIC_MNU', '', '', 'vmicon vmicon-16-info', 3, 1, '', 'report', 'basic'),
(null, 2, 0, 'COM_VIRTUEMART_SHOPPERS_MNU', '', '', 'vmicon vmicon-16-user', 4, 1, '', 'user', ''),
(null, 2, 0, 'COM_VIRTUEMART_SHOPPER_GROUP_LIST_MNU', '', '', 'vmicon vmicon-16-content', 5, 1, '', 'shoppergroup', ''),
(null, 3, 0, 'COM_VIRTUEMART_MANUFACTURER_LIST_MNU', '', '', 'vmicon vmicon-16-content', 1, 1, '', 'manufacturer', ''),
(null, 3, 0, 'COM_VIRTUEMART_MANUFACTURER_CAT_LIST_MNU', '', '', 'vmicon vmicon-16-content', 2, 1, '', 'manufacturercategories', ''),
(null, 4, 0, 'COM_VIRTUEMART_STORE_FORM_MNU', '', '', 'vmicon vmicon-16-config', 1, 1, '', 'user', 'editshop'),
(null, 4, 0, 'COM_VIRTUEMART_PAYMENT_METHOD_LIST_MNU', '', '', 'vmicon vmicon-16-content', 2, 1, '', 'paymentmethod', ''),
(null, 4, 0, 'COM_VIRTUEMART_CARRIER_LIST_MNU', '', '', 'vmicon vmicon-16-content', 3, 1, '', 'shippingcarrier', ''),
(null, 4, 0, 'COM_VIRTUEMART_RATE_LIST_MNU', '', '', 'vmicon vmicon-16-content', 4, 1, '', 'shippingrate', ''),
(null, 5, 0, 'COM_VIRTUEMART_CONFIG_MNU', '', '', 'vmicon vmicon-16-config', 1, 1, '', 'config', ''),
(null, 5, 0, 'COM_VIRTUEMART_CURRENCY_LIST_MNU', '', '', 'vmicon vmicon-16-content', 2, 1, '', 'currency', ''),
(null, 5, 0, 'COM_VIRTUEMART_CREDITCARD_LIST_MNU', '', '', 'vmicon vmicon-16-content', 3, 1, '', 'creditcard', ''),
(null, 5, 0, 'COM_VIRTUEMART_COUNTRY_LIST_MNU', '', '', 'vmicon vmicon-16-content', 4, 1, '', 'country', ''),
(null, 5, 0, 'COM_VIRTUEMART_MANAGE_USER_FIELDS_MNU', '', '', 'vmicon vmicon-16-content', 5, 1, '', 'userfields', ''),
(null, 5, 0, 'COM_VIRTUEMART_ORDER_STATUS_LIST_MNU', '', '', 'vmicon vmicon-16-orderstatus', 6, 1, '', 'orderstatus', ''),
(null, 11, 0, 'COM_VIRTUEMART_CHECK_UPDATES_MNU', '', '', 'vmicon vmicon-16-content', 1, 1, '', 'updatesmigration', ''),
(null, 11, 0, 'COM_VIRTUEMART_ABOUT', '', '', 'vmicon vmicon-16-info', 2, 1, '', '', ''),
(null, 11, 0, 'COM_VIRTUEMART_HELP_TOPICS', 'http://virtuemart.net/', '', 'vmicon vmicon-16-help', 4, 1, '', '', ''),
(null, 11, 0, 'COM_VIRTUEMART_COMMUNITY_FORUM', 'http://forum.virtuemart.net/', '', 'vmicon vmicon-16-language', 6, 1, '', '', ''),
(null, 14, 0, 'COM_VIRTUEMART_STATISTIC_SUMMARY', '', '', 'vmicon vmicon-16-info', 1, 1, '', 'virtuemart', ''),
(null, 77, 0, 'COM_VIRTUEMART_USERGROUP_LBL', '', '', 'vmicon vmicon-16-user', 2, 1, '', 'usergroups', '');

--
-- Dumping data for table `#__virtuemart_modules`
--

INSERT INTO `#__virtuemart_modules` (`module_id`, `module_name`, `module_description`, `module_perms`, `published`, `is_admin`, `list_order`) VALUES
(1, 'product', 'Here you can administer your online catalog of products.  Categories , Products (view=product), Attributes  ,Product Types      Product Files (view=media), Inventory  , Calculation Rules ,Customer Reviews  ', 'storeadmin,admin', 1, '1', 1),
(2, 'order', 'View Order and Update Order Status:    Orders , Coupons , Revenue Report ,Shopper , Shopper Groups ', 'admin,storeadmin', 1, '1', 2),
(3, 'manufacturer', 'Manage the manufacturers of products in your store.', 'storeadmin,admin', 1, '1', 3),
(4, 'store', 'Store Configuration: Store Information, Payment Methods , Shipper, Shipping Rates', 'storeadmin,admin', 1, '1', 4),
(5, 'configuration', 'Configuration: shop configuration , currencies (view=currency), Credit Card List, Countries, userfields, order status  ', 'admin,storeadmin', 1, '1', 5),
(6, 'msgs', 'This module is unprotected an used for displaying system messages to users.  We need to have an area that does not require authorization when things go wrong.', 'none', 0, '0', 99),
(7, 'shop', 'This is the Washupito store module.  This is the demo store included with the VirtueMart distribution.', 'none', 1, '0', 99),
(8, 'store', 'Store Configuration: Store Information, Payment Methods , Shipper, Shipping Rates', 'storeadmin,admin', 1, '1', 4),
(9, 'account', 'This module allows shoppers to update their account information and view previously placed orders.', 'shopper,storeadmin,admin,demo', 1, '0', 99),
(10, 'checkout', '', 'none', 0, '0', 99),
(11, 'tools', 'Tools', 'admin', 1, '1', 8),
(13, 'zone', 'This is the zone-shipping module. Here you can manage your shipping costs according to Zones.', 'admin,storeadmin', 0, '1', 11);

--
-- Dumping data for table `#__virtuemart_orderstates`
--

INSERT INTO `#__virtuemart_orderstates` (`order_status_id`, `order_status_code`, `order_status_name`, `order_status_description`, `ordering`, `vendor_id`) VALUES
(null, 'P', 'Pending', '', 1, 1),
(null, 'C', 'Confirmed', '', 2, 1),
(null, 'X', 'Cancelled', '', 3, 1),
(null, 'R', 'Refunded', '', 4, 1),
(null, 'S', 'Shipped', '', 5, 1);


--
-- Dumping data for table `#__virtuemart_userfields`
--

INSERT INTO `#__virtuemart_userfields` (`fieldid`, `name`, `title`, `description`, `type`, `maxlength`, `size`, `required`, `ordering`, `cols`, `rows`, `value`, `default`, `published`, `registration`, `shipping`, `account`, `readonly`, `calculated`, `sys`, `vendor_id`, `params`) VALUES
(null, 'email', 'COM_VIRTUEMART_REGISTER_EMAIL', '', 'emailaddress', 100, 30, 1, 2, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL),
(null, 'title', 'COM_VIRTUEMART_SHOPPER_FORM_TITLE', '', 'select', 0, 0, 0, 8, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL),
(null, 'password', 'COM_VIRTUEMART_SHOPPER_FORM_PASSWORD_1', '', 'password', 25, 30, 1, 4, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL),
(null, 'password2', 'COM_VIRTUEMART_SHOPPER_FORM_PASSWORD_2', '', 'password', 25, 30, 1, 5, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL),
(null, 'company', 'COM_VIRTUEMART_SHOPPER_FORM_COMPANY_NAME', '', 'text', 64, 30, 0, 7, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'delimiter_billto', 'COM_VIRTUEMART_USER_FORM_BILLTO_LBL', '', 'delimiter', 25, 30, 0, 6, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 0, 1, NULL),
(null, 'username', 'COM_VIRTUEMART_REGISTER_UNAME', '', 'text', 25, 30, 1, 3, 0, 0, '', 0, 1, 1, 0, 1, 0, 0, 1, 1, ''),
(null, 'address_type_name', 'COM_VIRTUEMART_USER_FORM_ADDRESS_LABEL', '', 'text', 32, 30, 1, 6, NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0, 1, 1, NULL),
(null, 'first_name', 'COM_VIRTUEMART_SHOPPER_FORM_FIRST_NAME', '', 'text', 32, 30, 1, 9, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'last_name', 'COM_VIRTUEMART_SHOPPER_FORM_LAST_NAME', '', 'text', 32, 30, 1, 10, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'middle_name', 'COM_VIRTUEMART_SHOPPER_FORM_MIDDLE_NAME', '', 'text', 32, 30, 0, 11, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'address_1', 'COM_VIRTUEMART_SHOPPER_FORM_ADDRESS_1', '', 'text', 64, 30, 1, 12, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'address_2', 'COM_VIRTUEMART_SHOPPER_FORM_ADDRESS_2', '', 'text', 64, 30, 0, 13, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'city', 'COM_VIRTUEMART_SHOPPER_FORM_CITY', '', 'text', 32, 30, 1, 14, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'zip', 'COM_VIRTUEMART_SHOPPER_FORM_ZIP', '', 'text', 32, 30, 1, 15, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'country_id', 'COM_VIRTUEMART_SHOPPER_FORM_COUNTRY', '', 'select', 0, 0, 1, 16, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'state_id', 'COM_VIRTUEMART_SHOPPER_FORM_STATE', '', 'select', 0, 0, 1, 17, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'phone_1', 'COM_VIRTUEMART_SHOPPER_FORM_PHONE', '', 'text', 32, 30, 1, 18, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'phone_2', 'COM_VIRTUEMART_SHOPPER_FORM_PHONE2', '', 'text', 32, 30, 0, 19, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'fax', 'COM_VIRTUEMART_SHOPPER_FORM_FAX', '', 'text', 32, 30, 0, 20, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'delimiter_sendregistration', 'COM_VIRTUEMART_BUTTON_SEND_REG', '', 'delimiter', 25, 30, 0, 28, NULL, NULL, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, 1, NULL),
(null, 'agreed', 'COM_VIRTUEMART_I_AGREE_TO_TOS', '', 'checkbox', NULL, NULL, 1, 29, NULL, NULL, NULL, NULL, 1, 1, 0, 0, 0, 0, 1, 1, NULL),
(null, 'delimiter_userinfo', 'COM_VIRTUEMART_ORDER_PRINT_CUST_INFO_LBL', '', 'delimiter', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 0, 1, NULL),
(null, 'extra_field_1', 'COM_VIRTUEMART_SHOPPER_FORM_EXTRA_FIELD_1', '', 'text', 255, 30, 0, 31, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL),
(null, 'extra_field_2', 'COM_VIRTUEMART_SHOPPER_FORM_EXTRA_FIELD_2', '', 'text', 255, 30, 0, 32, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL),
(null, 'extra_field_3', 'COM_VIRTUEMART_SHOPPER_FORM_EXTRA_FIELD_3', '', 'text', 255, 30, 0, 33, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL),
(null, 'extra_field_4', 'COM_VIRTUEMART_SHOPPER_FORM_EXTRA_FIELD_4', '', 'select', 1, 1, 0, 34, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL),
(null, 'extra_field_5', 'COM_VIRTUEMART_SHOPPER_FORM_EXTRA_FIELD_5', '', 'select', 1, 1, 0, 35, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL);


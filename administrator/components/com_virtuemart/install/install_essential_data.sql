 -- VirtueMart table data SQL script
-- This will insert all essential data into the VirtueMart tables


--
-- Dumping data for table `#__vm_config`
--
INSERT INTO `#__vm_config` (`config_id`, `config`) VALUES
(null, 'secureurl=\ndebug=0\ndebug_ip_enabled=0\ndebug_ip_address=\nenable_cookie_check=0\nregistration_type=NORMAL_REGISTRATION\nshow_remember_me_box=0\nproxy_url=\nproxy_port=\nproxy_user=\nproxy_pass=\nenable_logfile=0\nlogfile_name=\nlogfile_level=PEAR_LOG_TIP\nlogfile_format=\nshop_is_offline=0\noffline_message=Our Shop is currently down for maintenance.Please check back again soon.\nuse_as_catalog=0\nshow_out_of_stock_products=0\ncurrency_converter_module=convertECB.php\norder_mail_html=0\ncontent_plugins_enable=0\ncoupons_enable=0\nallow_reviews=0\nreviews_autopublish=0\ncomment_min_length=\ncomment_max_length=\nagree_to_tos_onorder=0\noncheckout_show_legal_info=0\ngenerally_prevent_https=0\nencrypt_function=ENCODE\nencode_key=bcd54d81dae74e9ae31c53cb4e6a6040\nstore_creditcard_data=0\nallow_frontendadmin_for_nonbackenders=0\nerrorpage=\npdf_button_enable=0\nshow_emailfriend=0\nshow_printicon=0\nshow_top_pagenav=0\nbrowse_orderby_field=product_list\nbrowse_orderby_fields=0|0|0|0|0|0\nshow_products_in_category=0\nno_image=noimage.gif\nshow_footer=0\nvmtemplate=rhuk_milkyway\nvmlayout=Default\ncategorytemplate=JA_Purity\ncategorylayout=Default\nproductlayout=Default\ncategories_per_row=\nproducts_per_row=\nassets_general_path=components/com_virtuemart/assets/\nmedia_category_path=images/stories/virtuemart/category/\nmedia_product_path=images/stories/virtuemart/product/\nimg_resize_enable=1\nimg_width=90\nimg_height=90\ndateformat=%m/%d/%y\nshow_prices=1\nprice_access_level_enabled=0\nprice_show_packaging_pricelabel=0\nbasePrice=1\nvariantModification=1\nbasePriceVariant=1\nbasePriceWithTax=1\ndiscountedPriceWithoutTax=1\nsalesPriceWithDiscount=1\nsalesPrice=1\npriceWithoutTax=1\ndiscountAmount=1\ntaxAmount=1\nenable_downloads=0\nenable_download_status=X\ndisable_download_status=X\ndownload_root=\ndownload_max=\ndownload_expire=\ndownloadable_products_keep_stocklevel=0\nfeed_enabled=0\nfeed_cache=0\nfeed_cachetime=1800\nfeed_title=\nfeed_title_categories=\nfeed_show_images=0\nfeed_show_prices=0\nfeed_show_description=0\nfeed_description_type=product_s_desc\nfeed_limittext=0\nfeed_max_text_length=500\ntask=apply\noption=com_virtuemart\nview=config\nno_image_set=noimage.gif\nno_image_found=warning.png\n\n');
-- (null, 'secureurl=\ndebug=0\ndebug_ip_enabled=0\ndebug_ip_address=\nenable_cookie_check=0\nregistration_type=NORMAL_REGISTRATION\nshow_remember_me_box=0\nproxy_url=\nproxy_port=\nproxy_user=\nproxy_pass=\nenable_logfile=0\nlogfile_name=\nlogfile_level=PEAR_LOG_TIP\nlogfile_format=\nshop_is_offline=0\noffline_message=Our Shop is currently down for maintenance.Please check back again soon.\nuse_as_catalog=0\nshow_out_of_stock_products=0\ncurrency_converter_module=convertECB.php\norder_mail_html=0\ncontent_plugins_enable=0\ncoupons_enable=0\nallow_reviews=0\nreviews_autopublish=0\ncomment_min_length=\ncomment_max_length=\nagree_to_tos_onorder=0\noncheckout_show_legal_info=0\ngenerally_prevent_https=0\nencrypt_function=ENCODE\nencode_key=bcd54d81dae74e9ae31c53cb4e6a6040\nstore_creditcard_data=0\nallow_frontendadmin_for_nonbackenders=0\nerrorpage=\npdf_button_enable=0\nshow_emailfriend=0\nshow_printicon=0\nshow_top_pagenav=0\nbrowse_orderby_field=product_list\nbrowse_orderby_fields=0|0|0|0|0|0\nshow_products_in_category=0\nno_image=noimage.gif\nshow_footer=0\nvmtemplate=rhuk_milkyway\nvmlayout=Default\ncategorytemplate=JA_Purity\ncategorylayout=Default\nproductlayout=Default\ncategories_per_row=\nproducts_per_row=\nassets_general_path=components/com_virtuemart/assets/\nmedia_category_path=images/stories/virtuemart/category/\nmedia_product_path=images/stories/virtuemart/product/\nimg_resize_enable=1\nimg_width=90\nimg_height=90\ndateformat=%m/%d/%y\nshow_prices=1\nprice_access_level_enabled=0\nprice_show_packaging_pricelabel=0\nbasePrice=1\nvariantModification=1\nbasePriceVariant=1\nbasePriceWithTax=1\ndiscountedPriceWithoutTax=1\nsalesPriceWithDiscount=1\nsalesPrice=1\npriceWithoutTax=1\ndiscountAmount=1\ntaxAmount=1\nenable_downloads=0\nenable_download_status=X\ndisable_download_status=X\ndownload_root=\ndownload_max=\ndownload_expire=\ndownloadable_products_keep_stocklevel=0\nfeed_enabled=0\nfeed_cache=0\nfeed_cachetime=1800\nfeed_title=\nfeed_title_categories=\nfeed_show_images=0\nfeed_show_prices=0\nfeed_show_description=0\nfeed_description_type=product_s_desc\nfeed_limittext=0\nfeed_max_text_length=500\ntask=apply\noption=com_virtuemart\nview=config\n\n');
-- (null, 'shop_is_offline=0\noffline_message=Our Shop is currently down for maintenance.Please check back again soon.\nuse_as_catalog=0\nshow_prices=1\nprice_access_level_enabled=0\nprice_access_level=\nshow_prices_with_tax=0\nshow_excluding_tax_note=0\nshow_including_tax_note=0\nshow_price_for_packaging=0\nenable_content_plugins=0\nenable_coupons=1\nenable_reviews=0\nautopublish_reviews=0\ncomment_min_length=100\ncomment_max_length=2000\nvirtual_tax=1\ntax_mode=0\nenable_multiple_taxrates=0\nsubtract_payment_before_discount=0\nregistration_type=NORMAL_REGISTRATION\nshow_remember_me_box=0\nagree_tos_onorder=0\noncheckout_show_legal_info=0\noncheckout_legalinfo_shorttext=ReturnsPolicyYou can cancel this order within two weeks after we have received it.  You can return new,unopened items from a cancelled order within 2 weeks after they have been delivered to you.  Items should be returned in their original packaging.  For more information on cancelling orders and returning items, see the OurReturnsPolicy page.\nshow_out_of_stock_products=0\nenable_cookie_check=1\nmail_format=0\ndebug=0\ndebug_by_ip=0\ndebug_ip_address=\nenable_logfile=0\nlogfile_name=\nlogfile_level=PEAR_LOG_WARNING\nlogfile_format=%{timestamp}%{ident}[%{priority}][%{remoteip}][%{username}]%{message}\nurl=http://\nsecureurl=https://\ngenerally_prevent_https=1\nencrypt_function=ENCODE\nencode_key=bcd54d81dae74e9ae31c53cb4e6a6040\nstore_creditcard_data=0\nerrorpage=shop.error\nproxy_url=\nproxy_port=\nproxy_user=\nproxy_pass=\nallow_frontendadminfor_nonbackenders=\ntableprefix=vm\nhomepage=shop.index\nenable_pdf_button=1\nbrowse_orderby_field=product_list\nbrowse_orderby_fields=0|0|0|0|0|0\nshow_emailfriend=1\nshow_print_button=1\nshow_top_pagenav=1\nshow_products_in_category=0\nshow_footer=1\ntheme=default\nproducts_per_row=1\nno_image=affiliate.gif\ncategory_template=managed\nimg_width=90\nimg_height=90\nimg_resize_enable=0\nshow_checkout_bar=1\nmax_vendor_pro_Cart=1\nenable_downloads=0\nenable_download_status=C\ndisable_download_status=X\ndownload_root=D:\\\ndownload_max=3\ndownload_expire=432000\ndownloadable_products_keep_stocklevel=0\nfeed_enabled=1\nfeed_cache=1\nfeed_cachetime=1800\nfeed_title=Latest Products from {storename}\nfeed_category_title={storename} - Latest Products from Category: {catname}\nfeed_show_images=1\nfeed_show_prices=1\nfeed_show_description=1\nfeed_limittext=1\nfeed_max_text_length=500\nfeed_description_type=product_s_desc\nconf_PRODUCTS_PER_ROW=1\nconf_ENCODE_KEY=bcd54d81dae74e9ae31c53cb4e6a6040\nconf_VM_TABLEPREFIX=\nconf_VM_PROXY_URL=\nconf_VM_PROXY_PORT=\nconf_VM_PROXY_USER=\nconf_VM_PROXY_PASS=\nmax_vendor_pro_cart=1\nVM_CHECKOUT_MODULES=Array|Array|Array|Array\nconf_DOWNLOADROOT=D:\\\nproduct_s_desc=product_s_desc\ntask=save\noption=com_virtuemart\nview=config\nmedia_category_path=images/stories/virtuemart/category/\nmedia_product_path=images/stories/virtuemart/product/\nbasePrice=1\nvariantModification=1\nbasePriceVariant=1\nbasePriceWithTax=1\ndiscountedPriceWithoutTax=1\nsalesPriceWithDiscount=1\nsalesPrice=1\npriceWithoutTax=1\ndiscountAmount=1\ntaxAmount=1\ndateformat=%m/%d/%y\nassets_general_path=components/com_virtuemart/images/vmgeneral\ndebug_ip_enabled=0\ncurrency_converter_module=convertECB.php\norder_mail_html=0\ncontent_plugins_enable=0\ncoupons_enable=0\nallow_reviews=0\nreviews_autopublish=0\nagree_to_tos_onorder=0\nallow_frontendadmin_for_nonbackenders=0\npdf_button_enable=0\nshow_printicon=0\nvmtemplate=beez\nvmlayout=Default\ncategorytemplate=beez\ncategorylayout=Default\nproductlayout=Default\ncategories_per_row=\nprice_show_packaging_pricelabel=0\nfeed_title_categories=\n\n');

--
-- Dumping data for table `#__vm_menu_admin`
--

INSERT INTO `#__vm_menu_admin` (`id`, `module_id`, `parent_id`, `name`, `link`, `depends`, `icon_class`, `ordering`, `published`, `tooltip`, `view`, `task`) VALUES
(null, 1, 0, 'VM_CONFIG', '', '', 'vmicon vmicon-16-config', 2, 1, '', 'config', ''),
(null, 1, 0, 'VM_USERS', '', '', 'vmicon vmicon-16-user', 4, 1, '', 'user', ''),
(null, 1, 0, 'VM_USERGROUP_LBL', '', '', 'vmicon vmicon-16-user', 6, 1, '', 'usergroups', ''),
(null, 1, 0, 'VM_MANAGE_USER_FIELDS', '', '', 'vmicon vmicon-16-content', 8, 1, '', 'userfields', ''),
(null, 1, 0, 'VM_COUNTRY_LIST_MNU', '', '', 'vmicon vmicon-16-content', 10, 1, '', 'country', ''),
(null, 1, 0, 'VM_CURRENCY_LIST_MNU', '', '', 'vmicon vmicon-16-content', 12, 1, '', 'currency', ''),
(null, 1, 0, 'VM_CHECK_UPDATES_MNU', '', '', 'vmicon vmicon-16-content', 16, 1, '', 'updatesmigration', ''),
(null, 8, 0, 'VM_STATISTIC_SUMMARY', '', '', 'vmicon vmicon-16-info', 2, 1, '', 'virtuemart', ''),
(null, 8, 0, 'VM_STORE_FORM_MNU', '', '', 'vmicon vmicon-16-config', 4, 1, '', 'user', 'editshop'),
(null, 8, 0, 'VM_CALCULATOR', '', '', 'vmicon vmicon-16-content', 5, 1, '', 'calc', ''),
(null, 8, 0, 'VM_PAYMENT_METHOD_LIST_MNU', '', '', 'vmicon vmicon-16-content', 6, 1, '', 'paymentmethod', ''),
(null, 8, 0, 'VM_PAYMENT_METHOD_FORM_MNU', '', '', 'vmicon vmicon-16-editadd', 8, 1, '', 'paymentmethod', 'edit'),
(null, 8, 0, 'VM_CREDITCARD_LIST_LBL', '', '', 'vmicon vmicon-16-content', 12, 1, '', 'creditcard', ''),
(null, 8, 0, 'VM_CREDITCARD_FORM_LBL', '', '', 'vmicon vmicon-16-editadd', 14, 1, '', 'creditcard', 'add'),
(null, 2, 0, 'VM_PRODUCT_LIST_MNU', '', '', 'vmicon vmicon-16-content', 4, 1, '', 'product', 'product'),
(null, 2, 0, 'VM_PRODUCT_FORM_MNU', '', '', 'vmicon vmicon-16-editadd', 6, 1, '', 'product', 'add'),
(null, 2, 0, 'VM_PRODUCT_INVENTORY_MNU', '', '', 'vmicon vmicon-16-install', 8, 1, '', 'inventory', 'inventory'),
(null, 2, 0, 'VM_SPECIAL_PRODUCTS', '', '', 'vmicon vmicon-16-content', 10, 1, '', 'productspecial', 'productSpecial'),
(null, 2, 0, 'VM_REVIEWS', '', '', 'vmicon vmicon-16-content', 14, 1, '', 'ratings', 'ratings'),
(null, 2, 0, 'VM_PRODUCT_TYPE_LIST_LBL', '', '', 'vmicon vmicon-16-content', 20, 1, '', 'producttypes', ''),
(null, 2, 0, 'VM_PRODUCT_PRODUCT_TYPE_FORM_MNU', '', '', 'vmicon vmicon-16-editadd', 22, 1, '', 'producttypes', 'add'),
(null, 77, 0, 'VM_CATEGORY_LIST_MNU', '', '', 'vmicon vmicon-16-content', 24, 1, '', 'category', ''),
(null, 77, 0, 'VM_CATEGORY_FORM_MNU', '', '', 'vmicon vmicon-16-editadd', 26, 1, '', 'category', 'add'),
(null, 4, 0, 'VM_SHOPPER_GROUP_LIST_MNU', '', '', 'vmicon vmicon-16-content', 2, 1, '', 'shoppergroup', ''),
(null, 4, 0, 'VM_SHOPPER_GROUP_FORM_MNU', '', '', 'vmicon vmicon-16-editadd', 4, 1, '', 'shoppergroup', 'add'),
(null, 5, 0, 'VM_ORDER_LIST_MNU', '', '', 'vmicon vmicon-16-content', 2, 1, '', 'orders', 'orders'),
(null, 5, 0, 'VM_ORDER_STATUS_LIST_MNU', '', '', 'vmicon vmicon-16-orderstatus', 4, 1, '', 'orderstatus', ''),
(null, 5, 0, 'VM_ORDER_STATUS_FORM_MNU', '', '', 'vmicon vmicon-16-orderstatus', 6, 1, '', 'orderstatus', 'edit'),
(null, 12, 0, 'VM_REPORT_BASIC_MNU', '', '', 'vmicon vmicon-16-info', 2, 1, '', 'report', 'basic'),
(null, 12839, 0, 'VM_CARRIER_LIST_MNU', '', '', 'vmicon vmicon-16-content', 2, 1, '', 'shippingcarrier', ''),
(null, 12839, 0, 'VM_CARRIER_FORM_MNU', '', '', 'vmicon vmicon-16-editadd', 4, 1, '', 'shippingcarrier', 'add'),
(null, 12839, 0, 'VM_RATE_LIST_MNU', '', '', 'vmicon vmicon-16-content', 6, 1, '', 'shippingrate', ''),
(null, 12839, 0, 'VM_RATE_FORM_MNU', '', '', 'vmicon vmicon-16-editadd', 8, 1, '', 'shippingrate', 'add'),
(null, 12843, 0, 'VM_COUPON_LIST', '', '', 'vmicon vmicon-16-content', 2, 1, '', 'coupon', ''),
(null, 12843, 0, 'VM_COUPON_NEW_HEADER', '', '', 'vmicon vmicon-16-editadd', 4, 1, '', 'coupon', 'add'),
(null, 99, 0, 'VM_MANUFACTURER_LIST_MNU', '', '', 'vmicon vmicon-16-content', 2, 1, '', 'manufacturer', ''),
(null, 99, 0, 'VM_MANUFACTURER_FORM_MNU', '', '', 'vmicon vmicon-16-editadd', 4, 1, '', 'manufacturer', 'add'),
(null, 99, 0, 'VM_MANUFACTURER_CAT_LIST_MNU', '', '', 'vmicon vmicon-16-content', 6, 1, '', 'manufacturercategory', ''),
(null, 99, 0, 'VM_MANUFACTURER_CAT_FORM_MNU', '', '', 'vmicon vmicon-16-editadd', 8, 1, '', 'manufacturercategory', 'add'),
(null, 12842, 0, 'VM_ABOUT', '', '', 'vmicon vmicon-16-info', 2, 1, '', '', ''),
(null, 12842, 0, 'VM_HELP_TOPICS', 'http://virtuemart.net/', '', 'vmicon vmicon-16-help', 4, 1, '', '', ''),
(null, 12842, 0, 'VM_COMMUNITY_FORUM', 'http://forum.virtuemart.net/', '', 'vmicon vmicon-16-language', 6, 1, '', '', ''),
(null, 2, 0, 'VM_PRODUCT_FILES_LIST_MNU', '', '', 'vmicon vmicon-16-content', 28, 1, '', 'media', 'media'),
(null, 2, 0, 'VM_ATTRIBUTE_LIST_MNU', '', '', 'vmicon vmicon-16-content', 29, 1, '', 'attributes', 'attributes');
--
-- Dumping data for table `#__vm_module`
--

INSERT INTO `#__vm_module` (`module_id`, `module_name`, `module_description`, `module_perms`, `published`, `is_admin`, `list_order`) VALUES
(1, 'admin', '<h4>ADMINISTRATIVE USERS ONLY</h4>\r\n\r\n<p>Only used for the following:</p>\r\n<OL>\r\n\r\n<LI>User Maintenance</LI>\r\n<LI>Module Maintenance</LI>\r\n<LI>Function Maintenance</LI>\r\n</OL>\r\n', 'admin', 1, '1', 1),
(2, 'product', '<p>Here you can adminster your online catalog of products.  The Product Administrator allows you to create product categories, create new products, edit product attributes, and add product items for each attribute value.</p>', 'storeadmin,admin', 1, '1', 4),
(4, 'shopper', '<p>Manage shoppers in your store.  Allows you to create shopper groups.  Shopper groups can be used when setting the price for a product.  This allows you to create different prices for different types of users.  An example of this would be to have a ''wholesale'' group and a ''retail'' group. </p>', 'admin,storeadmin', 1, '1', 5),
(5, 'order', '<p>View Order and Update Order Status.</p>', 'admin,storeadmin', 1, '1', 6),
(6, 'msgs', 'This module is unprotected an used for displaying system messages to users.  We need to have an area that does not require authorization when things go wrong.', 'none', 0, '0', 99),
(7, 'shop', 'This is the Washupito store module.  This is the demo store included with the VirtueMart distribution.', 'none', 1, '0', 99),
(8, 'store', '', 'storeadmin,admin', 1, '1', 2),
(9, 'account', 'This module allows shoppers to update their account information and view previously placed orders.', 'shopper,storeadmin,admin,demo', 0, '0', 99),
(10, 'checkout', '', 'none', 0, '0', 99),
(77, 'category', 'For the categories', 'admin,storeadmin', 1, '1', 3),
(12, 'report', 'The report module allows administrative queries relating to orders.', 'admin,storeadmin', 1, '1', 7),
(13, 'zone', 'This is the zone-shipping module. Here you can manage your shipping costs according to Zones.', 'admin,storeadmin', 0, '1', 9),
(12839, 'shipping', '<h4>Shipping</h4><p>Let this module calculate the shipping fees for your customers.<br>Create carriers for shipping areas and weight groups.</p>', 'admin,storeadmin', 1, '1', 10),
(99, 'manufacturer', 'Manage the manufacturers of products in your store.', 'storeadmin,admin', 1, '1', 12),
(12842, 'help', 'Help Module', 'admin,storeadmin', 1, '1', 13),
(12843, 'coupon', 'Coupon Management', 'admin,storeadmin', 1, '1', 11);


--
-- Dumping data for table `#__vm_order_status`
--

INSERT INTO `#__vm_order_status` (`order_status_id`, `order_status_code`, `order_status_name`, `order_status_description`, `ordering`, `vendor_id`) VALUES
(null, 'P', 'Pending', '', 1, 1),
(null, 'C', 'Confirmed', '', 2, 1),
(null, 'X', 'Cancelled', '', 3, 1),
(null, 'R', 'Refunded', '', 4, 1),
(null, 'S', 'Shipped', '', 5, 1);


--
-- Dumping data for table `#__vm_userfield`
--

INSERT INTO `#__vm_userfield` (`fieldid`, `name`, `title`, `description`, `type`, `maxlength`, `size`, `required`, `ordering`, `cols`, `rows`, `value`, `default`, `published`, `registration`, `shipping`, `account`, `readonly`, `calculated`, `sys`, `vendor_id`, `params`) VALUES
(null, 'email', 'REGISTER_EMAIL', '', 'emailaddress', 100, 30, 1, 2, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL),
(null, 'title', 'VM_SHOPPER_FORM_TITLE', '', 'select', 0, 0, 0, 8, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL),
(null, 'password', 'VM_SHOPPER_FORM_PASSWORD_1', '', 'password', 25, 30, 1, 4, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL),
(null, 'password2', 'VM_SHOPPER_FORM_PASSWORD_2', '', 'password', 25, 30, 1, 5, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL),
(null, 'company', 'VM_SHOPPER_FORM_COMPANY_NAME', '', 'text', 64, 30, 0, 7, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'delimiter_billto', 'VM_USER_FORM_BILLTO_LBL', '', 'delimiter', 25, 30, 0, 6, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 0, 1, NULL),
(null, 'username', 'REGISTER_UNAME', '', 'text', 25, 30, 1, 3, 0, 0, '', 0, 1, 1, 0, 1, 0, 0, 1, 1, ''),
(null, 'address_type_name', 'VM_USER_FORM_ADDRESS_LABEL', '', 'text', 32, 30, 1, 6, NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0, 1, 1, NULL),
(null, 'first_name', 'VM_SHOPPER_FORM_FIRST_NAME', '', 'text', 32, 30, 1, 9, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'last_name', 'VM_SHOPPER_FORM_LAST_NAME', '', 'text', 32, 30, 1, 10, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'middle_name', 'VM_SHOPPER_FORM_MIDDLE_NAME', '', 'text', 32, 30, 0, 11, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'address_1', 'VM_SHOPPER_FORM_ADDRESS_1', '', 'text', 64, 30, 1, 12, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'address_2', 'VM_SHOPPER_FORM_ADDRESS_2', '', 'text', 64, 30, 0, 13, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'city', 'VM_SHOPPER_FORM_CITY', '', 'text', 32, 30, 1, 14, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'zip', 'VM_SHOPPER_FORM_ZIP', '', 'text', 32, 30, 1, 15, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'country_id', 'VM_SHOPPER_FORM_COUNTRY', '', 'select', 0, 0, 1, 16, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'state_id', 'VM_SHOPPER_FORM_STATE', '', 'select', 0, 0, 1, 17, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'phone_1', 'VM_SHOPPER_FORM_PHONE', '', 'text', 32, 30, 1, 18, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'phone_2', 'VM_SHOPPER_FORM_PHONE2', '', 'text', 32, 30, 0, 19, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'fax', 'VM_SHOPPER_FORM_FAX', '', 'text', 32, 30, 0, 20, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(null, 'delimiter_sendregistration', 'BUTTON_SEND_REG', '', 'delimiter', 25, 30, 0, 28, NULL, NULL, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, 1, NULL),
(null, 'agreed', 'VM_I_AGREE_TO_TOS', '', 'checkbox', NULL, NULL, 1, 29, NULL, NULL, NULL, NULL, 1, 1, 0, 0, 0, 0, 1, 1, NULL),
(null, 'delimiter_userinfo', 'VM_ORDER_PRINT_CUST_INFO_LBL', '', 'delimiter', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 0, 1, NULL),
(null, 'extra_field_1', 'VM_SHOPPER_FORM_EXTRA_FIELD_1', '', 'text', 255, 30, 0, 31, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL),
(null, 'extra_field_2', 'VM_SHOPPER_FORM_EXTRA_FIELD_2', '', 'text', 255, 30, 0, 32, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL),
(null, 'extra_field_3', 'VM_SHOPPER_FORM_EXTRA_FIELD_3', '', 'text', 255, 30, 0, 33, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL),
(null, 'extra_field_4', 'VM_SHOPPER_FORM_EXTRA_FIELD_4', '', 'select', 1, 1, 0, 34, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL),
(null, 'extra_field_5', 'VM_SHOPPER_FORM_EXTRA_FIELD_5', '', 'select', 1, 1, 0, 35, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL);


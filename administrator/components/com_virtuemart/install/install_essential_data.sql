-- VirtueMart table data SQL script
-- This will insert all essential data into the VirtueMart tables


--
-- Dumping data for table `#__vm_config`
--
INSERT INTO `#__vm_config` (`config_id`, `config`) VALUES
(1, 'shop_is_offline=0\r\noffline_message=Our Shop is currently down for maintenance.Please check back again soon.\r\nuse_as_catalog=0\r\nshow_prices=1\r\nprice_access_level_enabled=0\r\nprice_access_level=\r\nshow_prices_with_tax=0\r\nshow_excluding_tax_note=0\r\nshow_including_tax_note=0\r\nshow_price_for_packaging=0\r\nenable_content_plugins=0\r\nenable_coupons=1\r\nenable_reviews=0\r\nautopublish_reviews=0\r\ncomment_min_length=100\r\ncomment_max_length=2000\r\nvirtual_tax=1\r\ntax_mode=0\r\nenable_multiple_taxrates=0\r\nsubtract_payment_before_discount=0\r\nregistration_type=NORMAL_REGISTRATION\r\nshow_remember_me_box=0\r\nagree_tos_onorder=0\r\noncheckout_show_legal_info=0\r\noncheckout_legalinfo_shorttext=ReturnsPolicyYou can cancel this order within two weeks after we have received it.  You can return new,unopened items from a cancelled order within 2 weeks after they have been delivered to you.  Items should be returned in their original packaging.  For more information on cancelling orders and returning items, see the OurReturnsPolicy page.\r\nshow_out_of_stock_products=0\r\nenable_cookie_check=1\r\nmail_format=0\r\ndebug=0\r\ndebug_by_ip=0\r\ndebug_ip_address=\r\nenable_logfile=0\r\nlogfile_name=\r\nlogfile_level=PEAR_LOG_WARNING\r\nlogfile_format=%{timestamp}%{ident}[%{priority}][%{remoteip}][%{username}]%{message}\r\nurl=http://\r\nsecureurl=https://\r\ngenerally_prevent_https=1\r\nencrypt_function=ENCODE\r\nencode_key=bcd54d81dae74e9ae31c53cb4e6a6040\r\nstore_creditcard_data=0\r\nerrorpage=shop.error\r\nproxy_url=\r\nproxy_port=\r\nproxy_user=\r\nproxy_pass=\r\nallow_frontendadminfor_nonbackenders=\r\ntableprefix=vm\r\nhomepage=shop.index\r\nenable_pdf_button=1\r\nbrowse_orderby_field=product_list\r\nbrowse_orderby_fields=1|1|1\r\nshow_emailfriend=1\r\nshow_print_button=1\r\nshow_top_pagenav=1\r\nshow_products_in_category=0\r\nshow_footer=1\r\ntheme=default\r\nproducts_per_row=1\r\nno_image=add-to-cart.gif\r\ncategory_template=managed\r\nflypage=flypage-ask.tpl.php\r\nimg_width=90\r\nimg_height=90\r\nimg_resize_enable=0\r\nshow_checkout_bar=1\r\nmax_vendor_pro_Cart=1\r\ncheckout_steps=final_basket,shipping_addr,shipping_method,payment_method,final_confirmation\r\ncheckout_steps_order=1,2,3,4,5\r\ncheckout_steps_enabled=1,1,1,1,1\r\nenable_downloads=0\r\nenable_download_status=C\r\ndisable_download_status=X\r\ndownload_root=D:\\\r\ndownload_max=3\r\ndownload_expire=432000\r\ndownloadable_products_keep_stocklevel=0\r\nfeed_enabled=1\r\nfeed_cache=1\r\nfeed_cachetime=1800\r\nfeed_title=Latest Products from {storename}\r\nfeed_category_title={storename} - Latest Products from Category: {catname}\r\nfeed_show_images=1\r\nfeed_show_prices=1\r\nfeed_show_description=1\r\nfeed_limittext=1\r\nfeed_max_text_length=500\r\nfeed_description_type=product_s_desc\r\nconf_PRODUCTS_PER_ROW=1\r\nconf_PSHOP_IMG_WIDTH=90\r\nconf_PSHOP_IMG_HEIGHT=90\r\nconf_ENCODE_KEY=bcd54d81dae74e9ae31c53cb4e6a6040\r\nconf_VM_TABLEPREFIX=\r\nconf_HOMEPAGE=shop.index\r\nconf_ERRORPAGE=shop.error\r\nconf_VM_PROXY_URL=\r\nconf_VM_PROXY_PORT=\r\nconf_VM_PROXY_USER=\r\nconf_VM_PROXY_PASS=\r\nmax_vendor_pro_cart=1\r\nVM_CHECKOUT_MODULES=Array|Array|Array|Array\r\nconf_DOWNLOADROOT=D:\\\r\nproduct_s_desc=product_s_desc\r\ntask=save\r\noption=com_virtuemart\r\nview=config\r\n\r\n');


--
-- Dumping data for table `#__vm_function`
--

INSERT INTO `#__vm_function` (`function_id`, `module_id`, `function_name`, `function_class`, `function_method`, `function_description`, `function_perms`) VALUES
(1, 1, 'userAdd', 'ps_user', 'addUpdateUser', '', 'admin,storeadmin'),
(2, 1, 'userDelete', 'ps_user', 'delete', '', 'admin,storeadmin'),
(3, 1, 'userUpdate', 'ps_user', 'addUpdateUser', '', 'admin,storeadmin'),
(31, 2, 'productAdd', 'ps_product', 'add', '', 'admin,storeadmin'),
(6, 1, 'functionAdd', 'ps_function', 'add', '', 'admin'),
(7, 1, 'functionUpdate', 'ps_function', 'update', '', 'admin'),
(8, 1, 'functionDelete', 'ps_function', 'delete', '', 'admin'),
(9, 1, 'userLogout', 'ps_user', 'logout', '', 'none'),
(10, 1, 'userAddressAdd', 'ps_user_address', 'add', '', 'admin,storeadmin,shopper,demo'),
(11, 1, 'userAddressUpdate', 'ps_user_address', 'update', '', 'admin,storeadmin,shopper'),
(12, 1, 'userAddressDelete', 'ps_user_address', 'delete', '', 'admin,storeadmin,shopper'),
(13, 1, 'moduleAdd', 'ps_module', 'add', '', 'admin'),
(14, 1, 'moduleUpdate', 'ps_module', 'update', '', 'admin'),
(15, 1, 'moduleDelete', 'ps_module', 'delete', '', 'admin'),
(16, 1, 'userLogin', 'ps_user', 'login', '', 'none'),
(17, 3, 'vendorAdd', 'ps_user', 'addUpdateUser', '', 'admin'),
(18, 3, 'vendorUpdate', 'ps_user', 'addUpdateUser', '', 'admin,storeadmin'),
(19, 3, 'vendorDelete', 'ps_vendor', 'delete', '', 'admin'),
(20, 3, 'vendorCategoryAdd', 'ps_vendor_category', 'add', '', 'admin'),
(21, 3, 'vendorCategoryUpdate', 'ps_vendor_category', 'update', '', 'admin'),
(22, 3, 'vendorCategoryDelete', 'ps_vendor_category', 'delete', '', 'admin'),
(23, 4, 'shopperAdd', 'ps_shopper', 'add', '', 'none'),
(24, 4, 'shopperDelete', 'ps_shopper', 'delete', '', 'admin,storeadmin'),
(25, 4, 'shopperUpdate', 'ps_shopper', 'update', '', 'admin,storeadmin,shopper'),
(26, 4, 'shopperGroupAdd', 'ps_shopper_group', 'add', '', 'admin,storeadmin'),
(27, 4, 'shopperGroupUpdate', 'ps_shopper_group', 'update', '', 'admin,storeadmin'),
(28, 4, 'shopperGroupDelete', 'ps_shopper_group', 'delete', '', 'admin,storeadmin'),
(30, 5, 'orderStatusSet', 'ps_order', 'order_status_update', '', 'admin,storeadmin'),
(32, 2, 'productDelete', 'ps_product', 'delete', '', 'admin,storeadmin'),
(33, 2, 'productUpdate', 'ps_product', 'update', '', 'admin,storeadmin'),
(34, 2, 'productCategoryAdd', 'ps_product_category', 'add', '', 'admin,storeadmin'),
(35, 2, 'productCategoryUpdate', 'ps_product_category', 'update', '', 'admin,storeadmin'),
(36, 2, 'productCategoryDelete', 'ps_product_category', 'delete', '', 'admin,storeadmin'),
(37, 2, 'productPriceAdd', 'ps_product_price', 'add', '', 'admin,storeadmin'),
(38, 2, 'productPriceUpdate', 'ps_product_price', 'update', '', 'admin,storeadmin'),
(39, 2, 'productPriceDelete', 'ps_product_price', 'delete', '', 'admin,storeadmin'),
(40, 2, 'productAttributeAdd', 'ps_product_attribute', 'add', '', 'admin,storeadmin'),
(41, 2, 'productAttributeUpdate', 'ps_product_attribute', 'update', '', 'admin,storeadmin'),
(42, 2, 'productAttributeDelete', 'ps_product_attribute', 'delete', '', 'admin,storeadmin'),
(43, 7, 'cartAdd', 'ps_cart', 'add', '', 'none'),
(44, 7, 'cartUpdate', 'ps_cart', 'update', '', 'none'),
(45, 7, 'cartDelete', 'ps_cart', 'delete', '', 'none'),
(46, 10, 'checkoutComplete', 'ps_checkout', 'add', '', 'shopper,storeadmin,admin'),
(48, 8, 'paymentMethodUpdate', 'paymentMethod.class', 'update', '', 'admin,storeadmin'),
(49, 8, 'paymentMethodAdd', 'paymentMethod.class', 'add', '', 'admin,storeadmin'),
(50, 8, 'paymentMethodDelete', 'paymentMethod.class', 'delete', '', 'admin,storeadmin'),
(51, 5, 'orderDelete', 'ps_order', 'delete', '', 'admin,storeadmin'),
(52, 11, 'addTaxRate', 'ps_tax', 'add', '', 'admin,storeadmin'),
(53, 11, 'updateTaxRate', 'ps_tax', 'update', '', 'admin,storeadmin'),
(54, 11, 'deleteTaxRate', 'ps_tax', 'delete', '', 'admin,storeadmin'),
(55, 10, 'checkoutValidateST', 'ps_checkout', 'validate_shipto', '', 'none'),
(59, 5, 'orderStatusUpdate', 'ps_order_status', 'update', '', 'admin,storeadmin'),
(60, 5, 'orderStatusAdd', 'ps_order_status', 'add', '', 'storeadmin,admin'),
(61, 5, 'orderStatusDelete', 'ps_order_status', 'delete', '', 'admin,storeadmin'),
(62, 1, 'currencyAdd', 'ps_currency', 'add', 'add a currency', 'storeadmin,admin'),
(63, 1, 'currencyUpdate', 'ps_currency', 'update', '        update a currency', 'storeadmin,admin'),
(64, 1, 'currencyDelete', 'ps_currency', 'delete', 'delete a currency', 'storeadmin,admin'),
(65, 1, 'countryAdd', 'ps_country', 'add', 'Add a country ', 'storeadmin,admin'),
(66, 1, 'countryUpdate', 'ps_country', 'update', 'Update a country record', 'storeadmin,admin'),
(67, 1, 'countryDelete', 'ps_country', 'delete', 'Delete a country record', 'storeadmin,admin'),
(68, 2, 'product_csv', 'ps_csv', 'upload_csv', '', 'admin'),
(110, 7, 'waitingListAdd', 'zw_waiting_list', 'add', '', 'none'),
(111, 13, 'addzone', 'ps_zone', 'add', 'This will add a zone', 'admin,storeadmin'),
(112, 13, 'updatezone', 'ps_zone', 'update', 'This will update a zone', 'admin,storeadmin'),
(113, 13, 'deletezone', 'ps_zone', 'delete', 'This will delete a zone', 'admin,storeadmin'),
(114, 13, 'zoneassign', 'ps_zone', 'assign', 'This will assign a country to a zone', 'admin,storeadmin'),
(115, 1, 'writeConfig', 'ps_config', 'writeconfig', 'This will write the configuration details to virtuemart.cfg.php', 'admin'),
(116, 12839, 'carrierAdd', 'ps_shipping', 'add', '', 'admin,storeadmin'),
(117, 12839, 'carrierDelete', 'ps_shipping', 'delete', '', 'admin,storeadmin'),
(118, 12839, 'carrierUpdate', 'ps_shipping', 'update', '', 'admin,storeadmin'),
(119, 12839, 'rateAdd', 'ps_shipping', 'rate_add', '', 'admin,storeadmin'),
(120, 12839, 'rateUpdate', 'ps_shipping', 'rate_update', '', 'admin,shopadmin'),
(121, 12839, 'rateDelete', 'ps_shipping', 'rate_delete', '', 'admin,storeadmin'),
(122, 10, 'checkoutProcess', 'ps_checkout', 'process', '', 'none'),
(123, 5, 'downloadRequest', 'ps_order', 'download_request', 'This checks if the download request is valid and sends the file to the browser as file download if the request was successful, otherwise echoes an error', 'none'),
(128, 99, 'manufacturerAdd', 'ps_manufacturer', 'add', '', 'admin,storeadmin'),
(129, 99, 'manufacturerUpdate', 'ps_manufacturer', 'update', '', 'admin,storeadmin'),
(130, 99, 'manufacturerDelete', 'ps_manufacturer', 'delete', '', 'admin,storeadmin'),
(131, 99, 'manufacturercategoryAdd', 'ps_manufacturer_category', 'add', '', 'admin,storeadmin'),
(132, 99, 'manufacturercategoryUpdate', 'ps_manufacturer_category', 'update', '', 'admin,storeadmin'),
(133, 99, 'manufacturercategoryDelete', 'ps_manufacturer_category', 'delete', '', 'admin,storeadmin'),
(134, 7, 'addReview', 'ps_reviews', 'process_review', 'This lets the user add a review and rating to a product.', 'admin,storeadmin,shopper,demo'),
(135, 7, 'productReviewDelete', 'ps_reviews', 'delete_review', 'This deletes a review and from a product.', 'admin,storeadmin'),
(136, 8, 'creditcardAdd', 'ps_creditcard', 'add', 'Adds a Credit Card entry.', 'admin,storeadmin'),
(137, 8, 'creditcardUpdate', 'ps_creditcard', 'update', 'Updates a Credit Card entry.', 'admin,storeadmin'),
(138, 8, 'creditcardDelete', 'ps_creditcard', 'delete', 'Deletes a Credit Card entry.', 'admin,storeadmin'),
(139, 2, 'changePublishState', 'vmAbstractObject.class', 'handlePublishState', 'Changes the publish field of an item, so that it can be published or unpublished easily.', 'admin,storeadmin'),
(141, 2, 'reorder', 'ps_product_category', 'reorder', 'Changes the list order of a category.', 'admin,storeadmin'),
(142, 2, 'discountAdd', 'ps_product_discount', 'add', 'Adds a discount.', 'admin,storeadmin'),
(143, 2, 'discountUpdate', 'ps_product_discount', 'update', 'Updates a discount.', 'admin,storeadmin'),
(144, 2, 'discountDelete', 'ps_product_discount', 'delete', 'Deletes a discount.', 'admin,storeadmin'),
(145, 8, 'shippingmethodSave', 'shippingMethod.class', 'save', '', 'admin,storeadmin'),
(146, 2, 'uploadProductFile', 'ps_product_files', 'add', 'Uploads and Adds a Product Image/File.', 'admin,storeadmin'),
(147, 2, 'updateProductFile', 'ps_product_files', 'update', 'Updates a Product Image/File.', 'admin,storeadmin'),
(148, 2, 'deleteProductFile', 'ps_product_files', 'delete', 'Deletes a Product Image/File.', 'admin,storeadmin'),
(149, 12843, 'couponAdd', 'ps_coupon', 'add_coupon_code', 'Adds a Coupon.', 'admin,storeadmin'),
(150, 12843, 'couponUpdate', 'ps_coupon', 'update_coupon', 'Updates a Coupon.', 'admin,storeadmin'),
(151, 12843, 'couponDelete', 'ps_coupon', 'remove_coupon_code', 'Deletes a Coupon.', 'admin,storeadmin'),
(152, 12843, 'couponProcess', 'ps_coupon', 'process_coupon_code', 'Processes a Coupon.', 'admin,storeadmin,shopper,demo'),
(153, 2, 'ProductTypeAdd', 'ps_product_type', 'add', 'Function add a Product Type and create new table product_type_<id>.', 'admin'),
(154, 2, 'ProductTypeUpdate', 'ps_product_type', 'update', 'Update a Product Type.', 'admin'),
(155, 2, 'ProductTypeDelete', 'ps_product_type', 'delete', 'Delete a Product Type and drop table product_type_<id>.', 'admin'),
(156, 2, 'ProductTypeReorder', 'ps_product_type', 'reorder', 'Changes the list order of a Product Type.', 'admin'),
(157, 2, 'ProductTypeAddParam', 'ps_product_type_parameter', 'add_parameter', 'Function add a Parameter into a Product Type and create new column in table product_type_<id>.', 'admin'),
(158, 2, 'ProductTypeUpdateParam', 'ps_product_type_parameter', 'update_parameter', 'Function update a Parameter in a Product Type and a column in table product_type_<id>.', 'admin'),
(159, 2, 'ProductTypeDeleteParam', 'ps_product_type_parameter', 'delete_parameter', 'Function delete a Parameter from a Product Type and drop a column in table product_type_<id>.', 'admin'),
(160, 2, 'ProductTypeReorderParam', 'ps_product_type_parameter', 'reorder_parameter', 'Changes the list order of a Parameter.', 'admin'),
(161, 2, 'productProductTypeAdd', 'ps_product_product_type', 'add', 'Add a Product into a Product Type.', 'admin,storeadmin'),
(162, 2, 'productProductTypeDelete', 'ps_product_product_type', 'delete', 'Delete a Product from a Product Type.', 'admin,storeadmin'),
(163, 1, 'stateAdd', 'ps_country', 'addState', 'Add a State ', 'storeadmin,admin'),
(164, 1, 'stateUpdate', 'ps_country', 'updateState', 'Update a state record', 'storeadmin,admin'),
(165, 1, 'stateDelete', 'ps_country', 'deleteState', 'Delete a state record', 'storeadmin,admin'),
(166, 2, 'csvFieldAdd', 'ps_csv', 'add', 'Add a CSV Field ', 'storeadmin,admin'),
(167, 2, 'csvFieldUpdate', 'ps_csv', 'update', 'Update a CSV Field', 'storeadmin,admin'),
(168, 2, 'csvFieldDelete', 'ps_csv', 'delete', 'Delete a CSV Field', 'storeadmin,admin'),
(169, 1, 'userfieldSave', 'ps_userfield', 'savefield', 'add or edit a user field', 'admin'),
(170, 1, 'userfieldDelete', 'ps_userfield', 'deletefield', '', 'admin'),
(171, 1, 'changeordering', 'vmAbstractObject.class', 'handleordering', '', 'admin'),
(172, 2, 'moveProduct', 'ps_product', 'move', 'Move products from one category to another.', 'admin,storeadmin'),
(173, 7, 'productAsk', 'ps_communication', 'mail_question', 'Lets the customer send a question about a specific product.', 'none'),
(174, 7, 'recommendProduct', 'ps_communication', 'sendRecommendation', 'Lets the customer send a recommendation about a specific product to a friend.', 'none'),
(175, 2, 'reviewUpdate', 'ps_reviews', 'update', 'Modify a review about a specific product.', 'admin'),
(179, 1, 'writeThemeConfig', 'ps_config', 'writeThemeConfig', 'Writes a theme configuration file.', 'admin'),
(180, 1, 'usergroupAdd', 'usergroup.class', 'add', 'Add a new user group', 'admin'),
(181, 1, 'usergroupUpdate', 'usergroup.class', 'update', 'Update an user group', 'admin'),
(182, 1, 'usergroupDelete', 'usergroup.class', 'delete', 'Delete an user group', 'admin'),
(183, 1, 'setModulePermissions', 'ps_module', 'update_permissions', '', 'admin'),
(184, 1, 'setFunctionPermissions', 'ps_function', 'update_permissions', '', 'admin'),
(185, 2, 'insertDownloadsForProduct', 'ps_order', 'insert_downloads_for_product', '', 'admin'),
(186, 5, 'mailDownloadId', 'ps_order', 'mail_download_id', '', 'storeadmin,admin'),
(187, 7, 'replaceSavedCart', 'ps_cart', 'replaceCart', 'Replace cart with saved cart', 'none'),
(188, 7, 'mergeSavedCart', 'ps_cart', 'mergeSaved', 'Merge saved cart with cart', 'none'),
(189, 7, 'deleteSavedCart', 'ps_cart', 'deleteCart', 'Delete saved cart', 'none'),
(190, 7, 'savedCartDelete', 'ps_cart', 'deleteSaved', 'Delete items from saved cart', 'none'),
(191, 7, 'savedCartUpdate', 'ps_cart', 'updateSaved', 'Update saved cart items', 'none'),
(192, 1, 'getupdatepackage', 'update.class', 'getPatchPackage', 'Retrieves the Patch Package from the virtuemart.net Servers.', 'admin'),
(193, 1, 'applypatchpackage', 'update.class', 'applyPatch', 'Applies the Patch using the instructions from the update.xml file in the downloaded patch.', 'admin'),
(194, 1, 'removePatchPackage', 'update.class', 'removePackageFile', 'Removes  a Patch Package File and its extracted contents.', 'admin'),
(195, 1, 'uninstallExtension', 'installer.class', 'uninstall', 'Uninstalls an Extension', 'admin'),
(196, 1, 'installExtension', 'installer.class', 'install', 'Installs an Extension', 'admin'),
(197, 1, 'pluginUpdate', 'pluginEntity.class', 'update', 'Updates a VM Plugin and saves all new parameter settings.', 'storeadmin,admin');

--
-- Dumping data for table `#__vm_menu_admin`
--

INSERT INTO `#__vm_menu_admin` (`id`, `module_id`, `parent_id`, `name`, `link`, `depends`, `icon_class`, `ordering`, `published`, `tooltip`, `view`, `task`) VALUES
(10, 1, 0, 'VM_CONFIG', 'page=admin.show_cfg', '', 'vmicon vmicon-16-config', 2, '1', '', '', ''),
(20, 1, 0, 'VM_USERS', 'page=admin.user_list', '', 'vmicon vmicon-16-user', 4, '1', '', '', ''),
(30, 1, 0, 'VM_USERGROUP_LBL', 'page=admin.usergroup_list', '', 'vmicon vmicon-16-user', 6, '1', '', '', ''),
(40, 1, 0, 'VM_MANAGE_USER_FIELDS', 'page=admin.user_field_list', '', 'vmicon vmicon-16-content', 8, '1', '', '', ''),
(50, 1, 0, 'VM_COUNTRY_LIST_MNU', 'page=admin.country_list', '', 'vmicon vmicon-16-content', 10, '1', '', 'country', ''),
(60, 1, 0, 'VM_CURRENCY_LIST_MNU', 'page=admin.curr_list', '', 'vmicon vmicon-16-content', 12, '1', '', 'currency', ''),
(70, 1, 0, 'VM_MODULE_LIST_MNU', 'page=admin.module_list', '', 'vmicon vmicon-16-content', 14, '1', '', '', ''),
(80, 1, 0, 'VM_CHECK_UPDATES_MNU', 'page=admin.update_check', '', 'vmicon vmicon-16-content', 16, '1', '', 'updatesMigration', ''),
(90, 8, 0, 'VM_STATISTIC_SUMMARY', 'page=store.index', '', 'vmicon vmicon-16-info', 2, '1', '', 'virtuemart', ''),
(100, 8, 0, 'VM_STORE_FORM_MNU', 'page=store.store_form', '', 'vmicon vmicon-16-config', 4, '1', '', 'store', ''),
(110, 8, 0, 'VM_PAYMENT_METHOD_LIST_MNU', 'page=store.payment_method_list', '', 'vmicon vmicon-16-content', 6, '1', '', '', ''),
(120, 8, 0, 'VM_PAYMENT_METHOD_FORM_MNU', 'page=store.payment_method_form', '', 'vmicon vmicon-16-editadd', 8, '1', '', '', ''),
(130, 8, 0, 'VM_SHIPPING_MODULE_LIST_LBL', 'page=store.shipping_module_list', '', 'vmicon vmicon-16-content', 10, '1', '', '', ''),
(140, 8, 0, 'VM_CREDITCARD_LIST_LBL', 'page=store.creditcard_list', '', 'vmicon vmicon-16-content', 12, '1', '', 'creditcard', ''),
(150, 8, 0, 'VM_CREDITCARD_FORM_LBL', 'page=store.creditcard_form', '', 'vmicon vmicon-16-editadd', 14, '1', '', 'creditcard', 'add'),
(180, 2, 0, 'CSVIMPROVED_TITLE', 'http://www.csvimproved.com/', '', 'vmicon vmicon-16-import', 2, '1', 'CSVIMPROVED_NEEDINSTALL', '', ''),
(190, 2, 0, 'VM_PRODUCT_LIST_MNU', 'page=product.product_list', '', 'vmicon vmicon-16-content', 4, '1', '', 'product', 'product'),
(200, 2, 0, 'VM_PRODUCT_FORM_MNU', 'page=product.product_form', '', 'vmicon vmicon-16-editadd', 6, '1', '', 'product','add'),
(210, 2, 0, 'VM_PRODUCT_INVENTORY_MNU', 'page=product.product_inventory', '', 'vmicon vmicon-16-install', 8, '1', '', 'inventory', 'inventory'),
(220, 2, 0, 'VM_SPECIAL_PRODUCTS', 'page=product.specialprod', '', 'vmicon vmicon-16-content', 10, '1', '', 'productSpecial', 'productSpecial'),
(240, 2, 0, 'VM_REVIEWS', 'page=product.review_list', '', 'vmicon vmicon-16-content', 14, '1', '', 'ratings', 'ratings'),
(250, 2, 0, 'VM_PRODUCT_DISCOUNT_LIST_LBL', 'page=product.product_discount_list', '', 'vmicon vmicon-16-content', 16, '1', '', 'discounts', 'discounts'),
(260, 2, 0, 'VM_PRODUCT_DISCOUNT_FORM_MNU', 'page=product.product_discount_form', '', 'vmicon vmicon-16-editadd', 18, '1', '', 'discounts', 'add'),
(270, 2, 0, 'VM_PRODUCT_TYPE_LIST_LBL', 'page=product.product_type_list', '', 'vmicon vmicon-16-content', 20, '1', '', '', ''),
(280, 2, 0, 'VM_PRODUCT_PRODUCT_TYPE_FORM_MNU', 'page=product.product_type_form', '', 'vmicon vmicon-16-editadd', 22, '1', '', '', ''),
(290, 2, 0, 'VM_CATEGORY_LIST_MNU', 'page=product.product_category_list', '', 'vmicon vmicon-16-content', 24, '1', '', 'category', ''),
(300, 2, 0, 'VM_CATEGORY_FORM_MNU', 'page=product.product_category_form', '', 'vmicon vmicon-16-editadd', 26, '1', '', 'category', 'add'),
(310, 4, 0, 'VM_SHOPPER_GROUP_LIST_MNU', 'page=shopper.shopper_group_list', '', 'vmicon vmicon-16-content', 2, '1', '', '', ''),
(320, 4, 0, 'VM_SHOPPER_GROUP_FORM_MNU', 'page=shopper.shopper_group_form', '', 'vmicon vmicon-16-editadd', 4, '1', '', '', ''),
(330, 5, 0, 'VM_ORDER_LIST_MNU', 'page=order.order_list', '', 'vmicon vmicon-16-content', 2, '1', '', 'orders', 'orders'),
(340, 5, 0, 'VM_ORDER_STATUS_LIST_MNU', 'page=order.order_status_list', '', 'vmicon vmicon-16-orderstatus', 4, '1', '', 'orderStatus', ''),
(350, 5, 0, 'VM_ORDER_STATUS_FORM_MNU', 'page=order.order_status_form', '', 'vmicon vmicon-16-orderstatus', 6, '1', '', 'orderStatus', 'edit'),
(400, 12, 0, 'VM_REPORTBASIC_MOD', 'page=reportbasic.index', '', 'vmicon vmicon-16-info', 2, '1', '', '', ''),
(410, 11, 0, 'VM_TAX_LIST_MNU', 'page=tax.tax_list', '', 'vmicon vmicon-16-content', 2, '1', '', '', ''),
(420, 11, 0, 'VM_TAX_FORM_MNU', 'page=tax.tax_form', '', 'vmicon vmicon-16-editadd', 4, '1', '', '', ''),
(430, 12839, 0, 'VM_CARRIER_LIST_MNU', 'page=shipping.carrier_list', '', 'vmicon vmicon-16-content', 2, '1', '', 'shippingcarrier', ''),
(440, 12839, 0, 'VM_CARRIER_FORM_MNU', 'page=shipping.carrier_form', '', 'vmicon vmicon-16-editadd', 4, '1', '', 'shippingcarrier', 'add'),
(450, 12839, 0, 'VM_RATE_LIST_MNU', 'page=shipping.rate_list', '', 'vmicon vmicon-16-content', 6, '1', '', 'shippingrate', ''),
(460, 12839, 0, 'VM_RATE_FORM_MNU', 'page=shipping.rate_form', '', 'vmicon vmicon-16-editadd', 8, '1', '', 'shippingrate', 'add'),
(470, 12843, 0, 'VM_COUPON_LIST', 'page=coupon.coupon_list', '', 'vmicon vmicon-16-content', 2, '1', '', 'coupon', ''),
(480, 12843, 0, 'VM_COUPON_NEW_HEADER', 'page=coupon.coupon_form', '', 'vmicon vmicon-16-editadd', 4, '1', '', 'coupon', 'add'),
(490, 99, 0, 'VM_MANUFACTURER_LIST_MNU', 'page=manufacturer.manufacturer_list', '', 'vmicon vmicon-16-content', 2, '1', '', 'manufacturer', ''),
(500, 99, 0, 'VM_MANUFACTURER_FORM_MNU', 'page=manufacturer.manufacturer_form', '', 'vmicon vmicon-16-editadd', 4, '1', '', 'manufacturer','add'),
(510, 99, 0, 'VM_MANUFACTURER_CAT_LIST_MNU', 'page=manufacturer.manufacturer_category_list', '', 'vmicon vmicon-16-content', 6, '1', '', 'manufacturerCategory', ''),
(520, 99, 0, 'VM_MANUFACTURER_CAT_FORM_MNU', 'page=manufacturer.manufacturer_category_form', '', 'vmicon vmicon-16-editadd', 8, '1', '', 'manufacturerCategory', 'add'),
(530, 12842, 0, 'VM_ABOUT', 'page=help.about', '', 'vmicon vmicon-16-info', 2, '1', '', '', ''),
(540, 12842, 0, 'VM_HELP_TOPICS', 'http://virtuemart.net/', '', 'vmicon vmicon-16-help', 4, '1', '', '', ''),
(550, 12842, 0, 'VM_COMMUNITY_FORUM', 'http://forum.virtuemart.net/', '', 'vmicon vmicon-16-language', 6, '1', '', '', ''),
(560, 2, 0, 'VM_PRODUCT_FILES_LIST_MNU', 'page=product.file_list', '', 'vmicon vmicon-16-content', 28, '1', '', 'media', 'media'),
(570, 1, 0, 'VM_ATTRIBUTE_LIST_MNU', 'page=product.attributes', '', 'vmicon vmicon-16-content', 29, '1', '', 'attributes', 'attributes'),
(580, 1, 0, '-', '', '', '', 11, '1', '', '', ''),
(590, 1, 0, '-', '', '', '', 13, '1', '', '', ''),
(600, 1, 0, '-', '', '', '', 15, '1', '', '', ''),
(610, 2, 0, '-', '', '', '', 7, '1', '', '', ''),
(620, 2, 0, '-', '', '', '', 3, '1', '', '', ''),
(630, 2, 0, '-', '', '', '', 9, '1', '', '', ''),
(640, 2, 0, '-', '', '', '', 11, '1', '', '', ''),
(650, 2, 0, '-', '', '', '', 15, '1', '', '', ''),
(660, 8, 0, '-', '', '', '', 3, '1', '', '', ''),
(670, 8, 0, '-', '', '', '', 9, '1', '', '', ''),
(680, 8, 0, '-', '', '', '', 11, '1', '', '', ''),
(690, 8, 0, '-', '', '', '', 15, '1', '', '', ''),
(700, 8, 0, '-', '', '', '', 5, '1', '', '', ''),
(710, 2, 0, '-', '', '', '', 19, '1', '', '', ''),
(720, 2, 0, '-', '', '', '', 23, '1', '', '', ''),
(730, 1, 0, 'Extension Manager', 'page=admin.extension_list', '', 'vmicon vmicon-16-content', 15, '1', '', '', ''),
(740, 1, 0, 'Plugin List', 'page=admin.plugin_list', '', 'vmicon vmicon-16-content', 16, '1', '', '', '');

--
-- Dumping data for table `#__vm_module`
--

INSERT INTO `#__vm_module` (`module_id`, `module_name`, `module_description`, `module_perms`, `module_publish`, `is_admin`, `list_order`) VALUES
(1, 'admin', '<h4>ADMINISTRATIVE USERS ONLY</h4>\r\n\r\n<p>Only used for the following:</p>\r\n<OL>\r\n\r\n<LI>User Maintenance</LI>\r\n<LI>Module Maintenance</LI>\r\n<LI>Function Maintenance</LI>\r\n</OL>\r\n', 'admin', 'Y', '1', 1),
(2, 'product', '<p>Here you can adminster your online catalog of products.  The Product Administrator allows you to create product categories, create new products, edit product attributes, and add product items for each attribute value.</p>', 'storeadmin,admin', 'Y', '1', 4),
(3, 'vendor', '<h4>ADMINISTRATIVE USERS ONLY</h4>\r\n<p>Here you can manage the vendors on the VirtueMart system.</p>', 'admin', 'Y', '1', 6),
(4, 'shopper', '<p>Manage shoppers in your store.  Allows you to create shopper groups.  Shopper groups can be used when setting the price for a product.  This allows you to create different prices for different types of users.  An example of this would be to have a ''wholesale'' group and a ''retail'' group. </p>', 'admin,storeadmin', 'Y', '1', 4),
(5, 'order', '<p>View Order and Update Order Status.</p>', 'admin,storeadmin', 'Y', '1', 5),
(6, 'msgs', 'This module is unprotected an used for displaying system messages to users.  We need to have an area that does not require authorization when things go wrong.', 'none', 'N', '0', 99),
(7, 'shop', 'This is the Washupito store module.  This is the demo store included with the VirtueMart distribution.', 'none', 'Y', '0', 99),
(8, 'store', '', 'storeadmin,admin', 'Y', '1', 2),
(9, 'account', 'This module allows shoppers to update their account information and view previously placed orders.', 'shopper,storeadmin,admin,demo', 'N', '0', 99),
(10, 'checkout', '', 'none', 'N', '0', 99),
(11, 'tax', 'The tax module allows you to set tax rates for states or regions within a country.  The rate is set as a decimal figure.  For example, 2 percent tax would be 0.02.', 'admin,storeadmin', 'Y', '1', 8),
(12, 'reportbasic', 'The report basic module allows you to do queries on all orders.', 'admin,storeadmin', 'Y', '1', 7),
(13, 'zone', 'This is the zone-shipping module. Here you can manage your shipping costs according to Zones.', 'admin,storeadmin', 'N', '1', 9),
(12839, 'shipping', '<h4>Shipping</h4><p>Let this module calculate the shipping fees for your customers.<br>Create carriers for shipping areas and weight groups.</p>', 'admin,storeadmin', 'Y', '1', 10),
(99, 'manufacturer', 'Manage the manufacturers of products in your store.', 'storeadmin,admin', 'Y', '1', 12),
(12842, 'help', 'Help Module', 'admin,storeadmin', 'Y', '1', 13),
(12843, 'coupon', 'Coupon Management', 'admin,storeadmin', 'Y', '1', 11);



--
-- Dumping data for table `#__vm_order_status`
--

INSERT INTO `#__vm_order_status` (`order_status_id`, `order_status_code`, `order_status_name`, `order_status_description`, `ordering`, `vendor_id`) VALUES
(1, 'P', 'Pending', '', 1, 1),
(2, 'C', 'Confirmed', '', 2, 1),
(3, 'X', 'Cancelled', '', 3, 1),
(4, 'R', 'Refunded', '', 4, 1),
(5, 'S', 'Shipped', '', 5, 1);



--
-- Dumping data for table `#__vm_payment_method`
--

INSERT INTO `#__vm_payment_method` (`id`, `vendor_id`, `name`, `element`, `shopper_group_id`, `discount`, `discount_is_percentage`, `discount_max_amount`, `discount_min_amount`, `ordering`, `type`, `is_creditcard`, `published`, `accepted_creditcards`, `extra_info`, `secret_key`, `params`) VALUES
(1, 1, 'Purchase Order', 'payment', 6, '0.00', 0, '0.00', '0.00', 4, 'N', 0, 'Y', '', '', '', ''),
(2, 1, 'Cash On Delivery', 'payment', 5, '-2.00', 0, '0.00', '0.00', 5, 'N', 0, 'Y', '', '', '', ''),
(3, 1, 'Credit Card', 'authorize', 5, '0.00', 0, '0.00', '0.00', 0, 'Y', 0, 'Y', '1,2,6,7,', '', '', ''),
(4, 1, 'PayPal', 'paypal', 5, '0.00', 0, '0.00', '0.00', 0, 'P', 0, 'Y', '', '', '', ''),
(5, 1, 'PayMate', 'paymate', 5, '0.00', 0, '0.00', '0.00', 0, 'P', 0, 'N', '', '', '', ''),
(6, 1, 'WorldPay', 'worldpay', 5, '0.00', 0, '0.00', '0.00', 0, 'P', 0, 'N', '', '', '', ''),
(7, 1, '2Checkout', 'twocheckout', 5, '0.00', 0, '0.00', '0.00', 0, 'P', 0, 'N', '', '', '', ''),
(8, 1, 'NoChex', 'nochex', 5, '0.00', 0, '0.00', '0.00', 0, 'P', 0, 'N', '', '', '', ''),
(9, 1, 'Credit Card (PayMeNow)', 'paymenow', 5, '0.00', 0, '0.00', '0.00', 0, 'Y', 0, 'N', '1,2,3,', '', '', ''),
(10, 1, 'eWay', 'eway', 5, '0.00', 0, '0.00', '0.00', 0, 'Y', 0, 'N', '', '', '', ''),
(11, 1, 'eCheck.net', 'echeck', 5, '0.00', 0, '0.00', '0.00', 0, 'B', 0, 'N', '', '', '', ''),
(12, 1, 'Credit Card (eProcessingNetwork)', 'epn', 5, '0.00', 0, '0.00', '0.00', 0, 'Y', 0, 'N', '1,2,3,', '', '', ''),
(13, 1, 'iKobo', 'payment', 5, '0.00', 0, '0.00', '0.00', 0, 'P', 0, 'N', '', '<form action="https://www.iKobo.com/store/index.php" method="post"> \n  <input type="hidden" name="cmd" value="cart" />Click on the image below to Pay with iKobo\n  <input type="image" src="https://www.ikobo.com/merchant/buttons/ikobo_pay1.gif" name="submit" alt="Pay with iKobo" /> \n  <input type="hidden" name="poid" value="USER_ID" /> \n  <input type="hidden" name="item" value="Order: <?php $db->p("order_id") ?>" /> \n  <input type="hidden" name="price" value="<?php printf("%.2f", $db->f("order_total"))?>" /> \n  <input type="hidden" name="firstname" value="<?php echo $user->first_name?>" /> \n  <input type="hidden" name="lastname" value="<?php echo $user->last_name?>" /> \n  <input type="hidden" name="address" value="<?php echo $user->address_1?>&#10<?php echo $user->address_2?>" /> \n  <input type="hidden" name="city" value="<?php echo $user->city?>" /> \n  <input type="hidden" name="state" value="<?php echo $user->state?>" /> \n  <input type="hidden" name="zip" value="<?php echo $user->zip?>" /> \n  <input type="hidden" name="phone" value="<?php echo $user->phone_1?>" /> \n  <input type="hidden" name="email" value="<?php echo $user->email?>" /> \n  </form> >', '', ''),
(14, 1, 'iTransact', 'payment', 5, '0.00', 0, '0.00', '0.00', 0, 'P', 0, 'N', '', '<?php\n  //your iTransact account details\n  $vendorID = "XXXXX";\n  global $vendor_name;\n  $mername = $vendor_name;\n  \n  //order details\n  $total = $db->f("order_total");$first_name = $user->first_name;$last_name = $user->last_name;$address = $user->address_1;$city = $user->city;$state = $user->state;$zip = $user->zip;$country = $user->country;$email = $user->email;$phone = $user->phone_1;$home_page = $mosConfig_live_site."/index.php";$ret_addr = $mosConfig_live_site."/index.php";$cc_payment_image = $mosConfig_live_site."/components/com_virtuemart/shop_image/ps_image/cc_payment.jpg";\n  ?>\n  <form action="https://secure.paymentclearing.com/cgi-bin/mas/split.cgi" method="POST"> \n                <input type="hidden" name="vendor_id" value="<?php echo $vendorID; ?>" />\n              <input type="hidden" name="home_page" value="<?php echo $home_page; ?>" />\n             <input type="hidden" name="ret_addr" value="<?php echo $ret_addr; ?>" />\n               <input type="hidden" name="mername" value="<?php echo $mername; ?>" />\n         <!--Enter text in the next value that should appear on the bottom of the order form.-->\n               <INPUT type="hidden" name="mertext" value="" />\n         <!--If you are accepting checks, enter the number 1 in the next value.  Enter the number 0 if you are not accepting checks.-->\n                <INPUT type="hidden" name="acceptchecks" value="0" />\n           <!--Enter the number 1 in the next value if you want to allow pre-registered customers to pay with a check.  Enter the number 0 if not.-->\n            <INPUT type="hidden" name="allowreg" value="0" />\n               <!--If you are set up with Check Guarantee, enter the number 1 in the next value.  Enter the number 0 if not.-->\n              <INPUT type="hidden" name="checkguar" value="0" />\n              <!--Enter the number 1 in the next value if you are accepting credit card payments.  Enter the number zero if not.-->\n         <INPUT type="hidden" name="acceptcards" value="1">\n              <!--Enter the number 1 in the next value if you want to allow a separate mailing address for credit card orders.  Enter the number 0 if not.-->\n               <INPUT type="hidden" name="altaddr" value="0" />\n                <!--Enter the number 1 in the next value if you want the customer to enter the CVV number for card orders.  Enter the number 0 if not.-->\n             <INPUT type="hidden" name="showcvv" value="1" />\n                \n              <input type="hidden" name="1-desc" value="Order Total" />\n               <input type="hidden" name="1-cost" value="<?php echo $total; ?>" />\n            <input type="hidden" name="1-qty" value="1" />\n          <input type="hidden" name="total" value="<?php echo $total; ?>" />\n             <input type="hidden" name="first_name" value="<?php echo $first_name; ?>" />\n           <input type="hidden" name="last_name" value="<?php echo $last_name; ?>" />\n             <input type="hidden" name="address" value="<?php echo $address; ?>" />\n         <input type="hidden" name="city" value="<?php echo $city; ?>" />\n               <input type="hidden" name="state" value="<?php echo $state; ?>" />\n             <input type="hidden" name="zip" value="<?php echo $zip; ?>" />\n         <input type="hidden" name="country" value="<?php echo $country; ?>" />\n         <input type="hidden" name="phone" value="<?php echo $phone; ?>" />\n             <input type="hidden" name="email" value="<?php echo $email; ?>" />\n             <p><input type="image" alt="Process Secure Credit Card Transaction using iTransact" border="0" height="60" width="210" src="<?php echo $cc_payment_image; ?>" /> </p>\n            </form>', '', ''),
(15, 1, 'Verisign PayFlow Pro', 'payflow_pro', 5, '0.00', 0, '0.00', '0.00', 0, 'Y', 0, 'Y', '1,2,6,7,', '', '', ''),
(16, 1, 'Dankort/PBS via ePay', 'epay', 5, '0.00', 0, '0.00', '0.00', 0, 'P', 0, 'Y', '', '', '', '');

--
-- Dumping data for table `#__vm_plugins`
--

INSERT INTO `#__vm_plugins` (`id`, `name`, `element`, `folder`, `ordering`, `published`, `iscore`, `vendor_id`, `shopper_group_id`, `checked_out`, `checked_out_time`, `params`, `secrets`) VALUES
(1, 'auspost', 'auspost', 'shipping', 11, 0, 0, 1, 5, 0, '0000-00-00 00:00:00', '', ''),
(2, 'canadapost', 'canadapost', 'shipping', 9, 0, 0, 1, 5, 0, '0000-00-00 00:00:00', '', ''),
(3, 'dhl', 'dhl', 'shipping', 4, 0, 0, 1, 5, 0, '0000-00-00 00:00:00', '', ''),
(4, 'fedex', 'fedex', 'shipping', 3, 0, 0, 1, 5, 0, '0000-00-00 00:00:00', '', ''),
(5, 'flex', 'flex', 'shipping', 2, 1, 0, 1, 5, 0, '0000-00-00 00:00:00', '', ''),
(6, 'intershipper', 'intershipper', 'shipping', 5, 0, 0, 1, 5, 0, '0000-00-00 00:00:00', '', ''),
(7, 'shipvalue', 'shipvalue', 'shipping', 8, 0, 0, 1, 5, 0, '0000-00-00 00:00:00', '', ''),
(8, 'standard_shipping', 'standard_shipping', 'shipping', 1, 1, 1, 1, 5, 0, '0000-00-00 00:00:00', '', ''),
(9, 'UPS Shipping Module', 'ups', 'shipping', 6, 0, 0, 1, 5, 0, '0000-00-00 00:00:00', '', ''),
(10, 'USPS Shipping Module', 'usps', 'shipping', 7, 0, 0, 1, 5, 0, '0000-00-00 00:00:00', '', ''),
(11, 'Zone Shipping Module', 'zone_shipping', 'shipping', 10, 0, 1, 1, 5, 0, '0000-00-00 00:00:00', '', '');





--
-- Dumping data for table `#__vm_userfield`
--

INSERT INTO `#__vm_userfield` (`fieldid`, `name`, `title`, `description`, `type`, `maxlength`, `size`, `required`, `ordering`, `cols`, `rows`, `value`, `default`, `published`, `registration`, `shipping`, `account`, `readonly`, `calculated`, `sys`, `vendor_id`, `params`) VALUES
(1, 'email', 'REGISTER_EMAIL', '', 'emailaddress', 100, 30, 1, 2, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL),
(7, 'title', 'VM_SHOPPER_FORM_TITLE', '', 'select', 0, 0, 0, 8, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL),
(3, 'password', 'VM_SHOPPER_FORM_PASSWORD_1', '', 'password', 25, 30, 1, 4, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL),
(4, 'password2', 'VM_SHOPPER_FORM_PASSWORD_2', '', 'password', 25, 30, 1, 5, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 1, 1, NULL),
(6, 'company', 'VM_SHOPPER_FORM_COMPANY_NAME', '', 'text', 64, 30, 0, 7, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(5, 'delimiter_billto', 'VM_USER_FORM_BILLTO_LBL', '', 'delimiter', 25, 30, 0, 6, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 0, 1, NULL),
(2, 'username', 'REGISTER_UNAME', '', 'text', 25, 30, 1, 3, 0, 0, '', 0, 1, 1, 0, 1, 0, 0, 1, 1, ''),
(35, 'address_type_name', 'VM_USER_FORM_ADDRESS_LABEL', '', 'text', 32, 30, 1, 6, NULL, NULL, NULL, NULL, 1, 0, 1, 0, 0, 0, 1, 1, NULL),
(8, 'first_name', 'VM_SHOPPER_FORM_FIRST_NAME', '', 'text', 32, 30, 1, 9, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(9, 'last_name', 'VM_SHOPPER_FORM_LAST_NAME', '', 'text', 32, 30, 1, 10, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(10, 'middle_name', 'VM_SHOPPER_FORM_MIDDLE_NAME', '', 'text', 32, 30, 0, 11, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(11, 'address_1', 'VM_SHOPPER_FORM_ADDRESS_1', '', 'text', 64, 30, 1, 12, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(12, 'address_2', 'VM_SHOPPER_FORM_ADDRESS_2', '', 'text', 64, 30, 0, 13, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(13, 'city', 'VM_SHOPPER_FORM_CITY', '', 'text', 32, 30, 1, 14, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(14, 'zip', 'VM_SHOPPER_FORM_ZIP', '', 'text', 32, 30, 1, 15, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(15, 'country_id', 'VM_SHOPPER_FORM_COUNTRY', '', 'select', 0, 0, 1, 16, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(16, 'state_id', 'VM_SHOPPER_FORM_STATE', '', 'select', 0, 0, 1, 17, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(17, 'phone_1', 'VM_SHOPPER_FORM_PHONE', '', 'text', 32, 30, 1, 18, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(18, 'phone_2', 'VM_SHOPPER_FORM_PHONE2', '', 'text', 32, 30, 0, 19, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(19, 'fax', 'VM_SHOPPER_FORM_FAX', '', 'text', 32, 30, 0, 20, NULL, NULL, NULL, NULL, 1, 1, 1, 1, 0, 0, 1, 1, NULL),
(20, 'delimiter_bankaccount', 'VM_ACCOUNT_BANK_TITLE', '', 'delimiter', 25, 30, 0, 21, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0, 0, 1, NULL),
(21, 'bank_account_holder', 'VM_ACCOUNT_LBL_BANK_ACCOUNT_HOLDER', '', 'text', 48, 30, 0, 22, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0, 1, 1, NULL),
(22, 'bank_account_nr', 'VM_ACCOUNT_LBL_BANK_ACCOUNT_NR', '', 'text', 32, 30, 0, 23, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0, 1, 1, NULL),
(23, 'bank_sort_code', 'VM_ACCOUNT_LBL_BANK_SORT_CODE', '', 'text', 16, 30, 0, 24, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0, 1, 1, NULL),
(24, 'bank_name', 'VM_ACCOUNT_LBL_BANK_NAME', '', 'text', 32, 30, 0, 25, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0, 1, 1, NULL),
(25, 'bank_account_type', 'VM_ACCOUNT_LBL_ACCOUNT_TYPE', '', 'select', 0, 0, 0, 26, 0, 0, '', 0, 1, 0, 0, 1, 1, 0, 1, 1, ''),
(26, 'bank_iban', 'VM_ACCOUNT_LBL_BANK_IBAN', '', 'text', 64, 30, 0, 27, NULL, NULL, NULL, NULL, 1, 0, 0, 1, 0, 0, 1, 1, NULL),
(27, 'delimiter_sendregistration', 'BUTTON_SEND_REG', '', 'delimiter', 25, 30, 0, 28, NULL, NULL, NULL, NULL, 1, 1, 0, 0, 0, 0, 0, 1, NULL),
(28, 'agreed', 'VM_I_AGREE_TO_TOS', '', 'checkbox', NULL, NULL, 1, 29, NULL, NULL, NULL, NULL, 1, 1, 0, 0, 0, 0, 1, 1, NULL),
(29, 'delimiter_userinfo', 'VM_ORDER_PRINT_CUST_INFO_LBL', '', 'delimiter', NULL, NULL, 0, 1, NULL, NULL, NULL, NULL, 1, 1, 0, 1, 0, 0, 0, 1, NULL),
(30, 'extra_field_1', 'VM_SHOPPER_FORM_EXTRA_FIELD_1', '', 'text', 255, 30, 0, 31, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL),
(31, 'extra_field_2', 'VM_SHOPPER_FORM_EXTRA_FIELD_2', '', 'text', 255, 30, 0, 32, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL),
(32, 'extra_field_3', 'VM_SHOPPER_FORM_EXTRA_FIELD_3', '', 'text', 255, 30, 0, 33, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL),
(33, 'extra_field_4', 'VM_SHOPPER_FORM_EXTRA_FIELD_4', '', 'select', 1, 1, 0, 34, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL),
(34, 'extra_field_5', 'VM_SHOPPER_FORM_EXTRA_FIELD_5', '', 'select', 1, 1, 0, 35, NULL, NULL, NULL, NULL, 0, 1, 0, 1, 0, 0, 0, 1, NULL);

--
-- Dumping data for table `#__vm_userfield_values`
--

INSERT INTO `#__vm_userfield_values` (`fieldvalueid`, `fieldid`, `fieldtitle`, `fieldvalue`, `ordering`, `sys`) VALUES
(1, 25, 'VM_ACCOUNT_LBL_ACCOUNT_TYPE_BUSINESSCHECKING', 'Checking', 1, 1),
(2, 25, 'VM_ACCOUNT_LBL_ACCOUNT_TYPE_CHECKING', 'Business Checking', 2, 1),
(3, 25, 'VM_ACCOUNT_LBL_ACCOUNT_TYPE_SAVINGS', 'Savings', 3, 1);



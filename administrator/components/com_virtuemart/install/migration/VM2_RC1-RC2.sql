

ALTER TABLE `jos_vm_user_info` CHANGE `virtuemart_state_id` `virtuemart_state_id` INT( 5 ) NOT NULL ;
ALTER TABLE `jos_vm_user_info` CHANGE `virtuemart_country_id` `virtuemart_country_id` INT( 5 ) NOT NULL;


ALTER TABLE `jos_vm_product_media_xref` ADD UNIQUE KEY `i_virtuemart_product_id` (`virtuemart_product_id`,`virtuemart_media_ids`);
ALTER TABLE `jos_vm_category_media_xref` ADD UNIQUE KEY `i_category_id` (`category_id`,`virtuemart_media_ids`);
ALTER TABLE `jos_vm_manufacturer_media_xref` ADD UNIQUE KEY `i_manufacturer_id` (`manufacturer_id`,`virtuemart_media_ids`);
ALTER TABLE `jos_vm_vendor_media_xref` ADD UNIQUE KEY `i_virtuemart_vendor_id` (`virtuemart_vendor_id`,`virtuemart_media_ids`);



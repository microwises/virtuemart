

ALTER TABLE `jos_vm_user_info` CHANGE `state_id` `state_id` INT( 5 ) NOT NULL ;
ALTER TABLE `jos_vm_user_info` CHANGE `country_id` `country_id` INT( 5 ) NOT NULL;


ALTER TABLE `jos_vm_product_media_xref` ADD UNIQUE KEY `i_product_id` (`product_id`,`file_ids`);
ALTER TABLE `jos_vm_category_media_xref` ADD UNIQUE KEY `i_category_id` (`category_id`,`file_ids`);
ALTER TABLE `jos_vm_manufacturer_media_xref` ADD UNIQUE KEY `i_manufacturer_id` (`manufacturer_id`,`file_ids`);
ALTER TABLE `jos_vm_vendor_media_xref` ADD UNIQUE KEY `i_vendor_id` (`vendor_id`,`file_ids`);



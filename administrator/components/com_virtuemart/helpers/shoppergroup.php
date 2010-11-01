<?php
/**
 * General helper class
 *
 * This class provides shopper group functions that are used throughout the VirtueMart shop.
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author RolandD
 * @copyright Copyright (c) 2004-2008 Soeren Eberhardt-Biermann, 2009 VirtueMart Team. All rights reserved.
 */

class ShopperGroup {
	
	/**
	 * Retrieves the Shopper Group Info of the SG specified by $id
	 *
	 * @todo Vendor ID
	 * @param int $id
	 * @param boolean $default_group
	 * @return array
	 */
  	function getShoppergroupById($id, $default_group = false) {
    	$vendor_id = 1;
    	$db = JFactory::getDBO();
    	
    	$q =  "SELECT `#__vm_shopper_group`.`shopper_group_id`, `#__vm_shopper_group`.`shopper_group_name`, `default` AS default_shopper_group FROM `#__vm_shopper_group`";
    		
    	if (!empty($id) && !$default_group) {
      		$q .= ", `#__vm_user_shoppergroup_xref`";
      		$q .= " WHERE `#__vm_user_shoppergroup_xref`.`user_id`='".$id."' AND ";
      		$q .= "`#__vm_shopper_group`.`shopper_group_id`=`#__vm_user_shoppergroup_xref`.`shopper_group_id`";
    	} 
    	else {
    		$q .= " WHERE `#__vm_shopper_group`.`vendor_id`='".$vendor_id."' AND `default`='1'";
    	}
    	$db->setQuery($q);
    	return $db->loadAssoc();
  	}

}
// pure php no closing tag

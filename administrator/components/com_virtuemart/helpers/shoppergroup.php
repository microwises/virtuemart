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
    	$virtuemart_vendor_id = 1;
    	$db = JFactory::getDBO();
    	
    	$q =  'SELECT `#__virtuemart_shoppergroups`.`virtuemart_shoppergroup_id`, `#__virtuemart_shoppergroups`.`shopper_group_name`, `default` AS default_shopper_group FROM `#__virtuemart_shoppergroups`';
    		
    	if (!empty($id) && !$default_group) {
      		$q .= ', `#__virtuemart_user_shoppergroups`';
      		$q .= ' WHERE `#__virtuemart_user_shoppergroups`.`virtuemart_user_id`="'.$id.'" AND ';
      		$q .= '`#__virtuemart_shoppergroups`.`virtuemart_shoppergroup_id`=`#__virtuemart_user_shoppergroups`.`virtuemart_shoppergroup_id`';
    	} 
    	else {
    		$q .= ' WHERE `#__virtuemart_shoppergroups`.`virtuemart_vendor_id`="'.$virtuemart_vendor_id.'" AND `default`="1"';
    	}
    	$db->setQuery($q);
    	return $db->loadAssoc();
  	}

}
// pure php no closing tag

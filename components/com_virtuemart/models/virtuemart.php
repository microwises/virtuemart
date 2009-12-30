<?php
/**
 * Default data model for Virtuemart
 *
 * @package     VirtueMart
 * @author      RickG
 * @copyright   Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');

/**
 * Default model class for Virtuemart
 *
 * @package	VirtueMart
 * @author RickG
 *
 */
class VirtueMartModelVirtueMart extends JModel {

    /**
     * This function shouldnt be used, it exist already in ps_vendor
     * creates a bulleted of the childen of this category if they exist
     *
     * @param int $category_id
     * @return string The HTML code
     */
    function GetVendorDetails($vendor_id) {
        $db =& JFactory::getDBO();

        $query = "SELECT category_id, category_thumb_image, category_child_id, category_name ";
        $query .= "FROM #__vm_category, #__vm_category_xref ";
        $query .= "WHERE #__vm_category_xref.category_parent_id = '$category_id' ";
        $query .= "AND #__vm_category.category_id = #__vm_category_xref.category_child_id ";
        //$query .= "AND #__vm_category.vendor_id = '$hVendor_id' ";
        $query .= "AND #__vm_category.vendor_id = '1' ";
        $query .= "AND #__vm_category.category_publish = 'Y' ";
        $query .= "ORDER BY #__vm_category.list_order, #__vm_category.category_name ASC";

        $childList = $this->_getList( $query );
        return $childList;
    }

}
?>
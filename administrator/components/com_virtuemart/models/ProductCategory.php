<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');

/**
 * Model for product categories
 */
class VirtueMartModelProductCategory extends JModel
{    
    function __construct()
    {
        parent::__construct();
    }
    
    
    /**
	 * Get the list of child categories for a given category
	 *
	 * @param int $category_id Category id to check for child categories
	 * @return object List of objects containing the child categories
	 */
	function getChildCategoryList($vendorId, $category_id) 
	{		
		$db =& JFactory::getDBO();

		$query = 'SELECT `category_id`, `category_thumb_image`, `category_child_id`, `category_name` ';
		$query .= 'FROM `#__vm_category`, `#__vm_category_xref` ';
		$query .= 'WHERE `#__vm_category_xref`.`category_parent_id` = ' . $category_id . ' ';
		$query .= 'AND `#__vm_category`.`category_id` = `#__vm_category_xref`.`category_child_id` ';
		$query .= 'AND `#__vm_category`.`vendor_id` = ' . $vendorId . ' ';
		$query .= 'AND `#__vm_category`.`category_publish` = "Y" ';
		$query .= 'ORDER BY `#__vm_category`.`list_order`, `#__vm_category`.`category_name` ASC';
		
		$childList = $this->_getList( $query );
		return $childList;	    
	}
	
	
}
?>
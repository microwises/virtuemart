<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the model framework
jimport( 'joomla.application.component.model');

/**
 * Model for product categories
 */
class VirtueMartModelProductCategory extends JModel {

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
	public function getChildCategoryList($vendorId, $category_id) {
		$db = JFactory::getDBO();

		$query = 'SELECT `category_id`, `category_thumb_image`, `category_child_id`, `category_name` ';
		$query .= 'FROM `#__vm_category`, `#__vm_category_xref` ';
		$query .= 'WHERE `#__vm_category_xref`.`category_parent_id` = ' . $category_id . ' ';
		$query .= 'AND `#__vm_category`.`category_id` = `#__vm_category_xref`.`category_child_id` ';
		$query .= 'AND `#__vm_category`.`vendor_id` = ' . $vendorId . ' ';
		$query .= 'AND `#__vm_category`.`published` = "1" ';
		$query .= 'ORDER BY `#__vm_category`.`ordering`, `#__vm_category`.`category_name` ASC';

		$db->setQuery($query);
		$childList = $db->loadObjectList();

		return $childList;
	}


}
// pure php no closing tag
<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Discount
* @author RolandD
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
 * Model for VirtueMart Products
 *
 * @package VirtueMart
 * @subpackage Discount
 * @author RolandD
 */
class VirtueMartModelDiscount extends JModel {

	var $_total;
	var $_pagination;

	function __construct() {
		parent::__construct();

		// Get the pagination request variables
		$mainframe = JFactory::getApplication() ;
		$limit = $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart = $mainframe->getUserStateFromRequest( JRequest::getVar('option').'.limitstart', 'limitstart', 0, 'int' );

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
	}

	/**
	 * Loads the pagination
	 */
    public function getPagination() {
		if ($this->_pagination == null) {
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}
		return $this->_pagination;
	}

	/**
	 * Gets the total number of products
	 */
	private function getTotal() {
    	if (empty($this->_total)) {
    		$db = JFactory::getDBO();
			$q = "SELECT COUNT(*) FROM #__vm_product_discount";
			$db->setQuery($q);
			$this->_total = $db->loadResult();
        }

        return $this->_total;
    }

    /**
     * Select the discount to list
     */
    public function getDiscountList() {
     	$db = JFactory::getDBO();
     	/* Pagination */
     	$this->getPagination();

     	/* Build the query */
     	$q = "SELECT *
     		FROM #__vm_product_discount";
     	$db->setQuery($q, $this->_pagination->limitstart, $this->_pagination->limit);
     	return $db->loadObjectList('discount_id');
    }

    /**
     * Select the products to list on the product list page
     */
    public function getDiscounts() {
     	$db = JFactory::getDBO();

     	/* Build the query */
     	$q = "SELECT *
     		FROM #__vm_product_discount";
     	$db->setQuery($q);
     	return $db->loadObjectList();
    }
}
// pure php no closing tag
<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
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
 * Model for VirtueMart Product Type Parameters
 *
 * @package VirtueMart
 * @author RolandD
 */
class VirtueMartModelProducttypeparameters extends JModel {

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
			$q = "SELECT COUNT(*) ".$this->getProductTypeParametersListQuery().$this->getProductTypeParametersFilter();
			$db->setQuery($q);
			$this->_total = $db->loadResult();
        }

        return $this->_total;
    }

    /**
     * Select the products to list on the product list page
     */
    public function getProductTypeParameters() {
     	$db = JFactory::getDBO();
     	/* Pagination */
     	$this->getPagination();

     	/* Build the query */
     	$q  = "SELECT * ".$this->getProductTypeParametersListQuery().$this->getProductTypeParametersFilter();
     	$db->setQuery($q, $this->_pagination->limitstart, $this->_pagination->limit);
     	return $db->loadObjectList();
    }

    /**
    * List of tables to include for the product query
    * @author RolandD
    */
    private function getProductTypeParametersListQuery() {
    	return 'FROM #__vm_product_type_parameter';
    }

    /**
    * Collect the filters for the query
    * @author RolandD
    */
    private function getProductTypeParametersFilter() {
    	$db = JFactory::getDBO();
    	$filter_order = JRequest::getCmd('filter_order', 'parameter_list_order');
		if ($filter_order == '') $filter_order = 'parameter_list_order';
		$filter_order_Dir = JRequest::getWord('filter_order_Dir', 'desc');
		if ($filter_order_Dir == '') $filter_order_Dir = 'desc';

    	/* Check some filters */
     	$filters = array();
     	if (JRequest::getVar('filter_producttypes', false)) $filters[] = '#__vm_product_type.`product_type_name` LIKE '.$db->Quote('%'.JRequest::getVar('filter_producttypes').'%');
     	if (JRequest::getInt('product_type_id', false)) $filters[] = 'product_type_id = '.JRequest::getInt('product_type_id');

     	if (count($filters) > 0) $filter = ' WHERE '.implode(' AND ', $filters);
     	else $filter = '';

     	return $filter.' ORDER BY '.$filter_order." ".$filter_order_Dir;
    }

    /**
    * Get the product type name
    * @author RolandD
    */
    public function getProductTypeName() {
    	$db = JFactory::getDBO();
    	$q = "SELECT product_type_name FROM #__vm_product_type WHERE product_type_id = ".JRequest::getInt('product_type_id', 0);
    	$db->setQuery($q);
    	return $db->loadResult();
    }


    /**
    * Load a single discount
    * @author RolandD
    */
    public function getProductTypeParameter() {
		/* Get the product IDs to remove */
		$cids = array();
		$cids = JRequest::getVar('cid', false);
		if ($cids && !is_array($cids)) $cids = array($cids);

		/* First copy the product in the product table */
		$parameter_data = $this->getTable('product_type_parameter');

		/* Load the rating */
		if ($cids) {
			$parameter_data->load($cids[0]);
			$parameter_data->list_order = $this->getListOrderParameter($cids[0], $parameter_data->parameter_name, $parameter_data->parameter_list_order);
		}
		else {
			$parameter_data->list_order = $this->getListOrderParameter();
		}

		return $parameter_data;
    }

    /**
    * Delete a product type
    * @author RolandD
    */
    public function removeProductTypeParameter() {
    	$db = JFactory::getDBO();
		/* Get the product IDs to remove */
		$cids = array();
		$cids = JRequest::getVar('cid');
		if (!is_array($cids)) $cids = array($cids);
		$parameter_id = JRequest::getInt('parameter_id');
		$product_type_id = JRequest::getInt('product_type_id');

		/* Start removing */
		foreach ($cids as $key => $product_type_name) {
			$q = "SELECT parameter_type FROM #__vm_product_type_parameter
				WHERE parameter_id = ".$parameter_id." AND parameter_name = ".$db->Quote($product_type_name);
			$db->setQuery($q);
			$parameter_type = $db->loadResult();

			/* If there is no parameter type, make it B as it might be non-existing */
			$q = "DELETE FROM #__vm_product_type_parameter
				WHERE parameter_id = ".$parameter_id." AND parameter_name = ".$db->Quote($product_type_name);
			$db->setQuery($q);
			$db->query();

			if ($parameter_type != "B") { // != Break Line
				// Delete column
				$q = "ALTER TABLE #__vm_product_type_" . $product_type_id." DROP ".$db->nameQuote($product_type_name);
				$db->setQuery($q) ;
				$db->query() ;
			}
		}
		return true;
    }

    /**
    * Save a discount
    *
    * @author RolandD
    * @todo Use the J! table for moving up and down
    */
    public function saveProductTypeParameter() {
    	$mainframe = Jfactory::getApplication();
    	$db = JFactory::getDBO();

		/* Get the product IDs to remove */
		$cids = array();
		$cids = JRequest::getVar('cid');
		if (!is_array($cids)) $cids = array($cids);

		/* First copy the product in the product table */
		$parameter_data = $this->getTable('product_type_parameter');

		/* Get the posted data */
		$data = JRequest::get('post', 4);

		/* added for custom parameter modification, strips the trailing semi-colon from an values */
		if (';' == substr( $data["parameter_values"], strlen( $data["parameter_values"] ) - 1, 1 ) ) {
			$data["parameter_values"] = substr($data["parameter_values"], 0, strlen( $data["parameter_values"] ) - 1 ) ;
		}

		/* Check the parameter_multiselect */
		if (empty($data["parameter_multiselect"])) $data["parameter_multiselect"] = "N";

		/* delete "\n" from field parameter_description */
		$data["parameter_description"] = str_replace( array("\r\n", "\n"), "", $data["parameter_description"] ) ;

		/* Bind the rating details */
		$parameter_data->bind($data);

		/* Set the list order for new parameters */
		if ($cids[0] == 0) {
			/* Let's find out the last Product Type */
			$q = "SELECT MAX(parameter_list_order)+1 AS list_order FROM #__vm_product_type_parameter WHERE product_type_id = ".$data['product_type_id'];
			$db->setQuery($q);
			$parameter_data->parameter_list_order = $db->loadResult();
		}

		/* Store the parameter */
		$parameter_data->store();

		/* Check if we have a new column */
		$q = "SHOW COLUMNS FROM `#__vm_product_type_".$data["product_type_id"]."` WHERE field = ".$db->Quote($data['parameter_name']);
		$db->setQuery($q);
		$result = $db->loadResult();

		if ($result != $data['parameter_name']) {
			if( $data["parameter_type"] != "B" ) { // != Break Line
				// Make new column in table product_type_<id>
				$q = "ALTER TABLE `#__vm_product_type_" ;
				$q .= $data["product_type_id"] . "` ADD `" ;
				$q .= $db->getEscaped($data['parameter_name']) . "` " ;
				switch( $data["parameter_type"]) {
					case "I" :
						$q .= "int(11) " ;
					break ; // Integer
					case "T" :
						$q .= "text " ;
					break ; // Text
					case "S" :
						$q .= "varchar(255) " ;
					break ; // Short Text
					case "F" :
						$q .= "float " ;
					break ; // Float
					case "C" :
						$q .= "char(1) " ;
					break ; // Char
					case "D" :
						$q .= "datetime " ;
					break ; // Date & Time
					case "A" :
						$q .= "date " ;
					break ; // Date
					case "V" :
						$q .= "varchar(255) " ;
					break ; // Multiple Value
					case "M" :
						$q .= "time " ;
					break ; // Time
					default :
						$q .= "varchar(255) " ; // Default type Short Text
				}
				if( $data["parameter_default"] != "" && $data["parameter_type"] != "T" ) {
					$q .= "DEFAULT ".$db->Quote($data["parameter_default"])." NOT NULL;" ;
				}

				$db->setQuery($q);
				$mainframe->enqueueMessage($db->getQuery());
				if ($db->query() === false ) {
					$mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_PRODUCT_TYPE_PARAMETER_ADDING_FAILED'), 'error');
					return false;
				}

				/* Make index for this column */
				if( $data["parameter_type"] == "T" ) {
					$q = "ALTER TABLE `#__vm_product_type_" ;
					$q .= $data["product_type_id"] . "` ADD FULLTEXT `idx_product_type_" . $data["product_type_id"] . "_" ;
					$q .= $db->getEscaped($data['parameter_name']) . "` (`" . $db->getEscaped($data['parameter_name']) . "`);" ;
					$db->setQuery($q);
					$mainframe->enqueueMessage($db->getQuery());
					$db->query();
				}
				else {
					$q = "ALTER TABLE `#__vm_product_type_" ;
					$q .= $data["product_type_id"] . "` ADD KEY `idx_product_type_" . $data["product_type_id"] . "_" ;
					$q .= $db->getEscaped($data['parameter_name']) . "` (`" . $db->getEscaped($data['parameter_name']) . "`);" ;
					$db->setQuery($q);
					$mainframe->enqueueMessage($db->getQuery());
					$db->query();
				}
			}
		}
		$mainframe->enqueueMessage(JText::_('COM_VIRTUEMART_PRODUCT_TYPE_PARAMETER_ADDED'));
		return true ;
    }

    /**
    * Get the position where the product type needs to be
    * @author RolandD
    * @return string Dropdown list with product type parameters
    */
    public function getListOrderParameter($product_type_id=0, $parameter_name = '', $list_order=0) {

    	$db = JFactory::getDBO();
    	$options = array();
		if (empty($parameter_name)) {
			return JText::_('COM_VIRTUEMART_CMN_NEW_ITEM_LAST');
		}
		else {

			$q = "SELECT parameter_list_order,parameter_label,parameter_name FROM #__vm_product_type_parameter " ;
			if ($product_type_id) {
				$q .= 'WHERE product_type_id='.$product_type_id;
			}
			$q .= " ORDER BY parameter_list_order ASC" ;
			$db->setQuery($q) ;
			$parameters = $db->loadObjectList();
			foreach ($parameters as $key => $parameter) {
				$options[] = JHTML::_('select.option', $parameter->parameter_list_order, $parameter->parameter_list_order.". ".$parameter->parameter_label.' ('.$parameter->parameter_name.')');
			}
			return JHTML::_('select.genericlist', $options, 'list_order', '', 'value', 'text', $list_order);
		}
    }
}
// pure php no closing tag
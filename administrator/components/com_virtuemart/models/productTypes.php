<?php
/**
* @package		VirtueMart
* @license		GNU/GPL, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.model');

/**
 * Model for VirtueMart Discounts
 *
 * @package VirtueMart
 * @author RolandD
 */
class VirtueMartModelProducttypes extends JModel {
    
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
			$q = "SELECT COUNT(*) ".$this->getProductTypesListQuery().$this->getProductTypesFilter();
			$db->setQuery($q);
			$this->_total = $db->loadResult();
        }
        
        return $this->_total;
    }
    
    /**
     * Select the products to list on the product list page
     */
    public function getProductTypes() {
     	$db = JFactory::getDBO();
     	/* Pagination */
     	$this->getPagination();
     	
     	/* Build the query */
     	$q = "SELECT *, p.product_type_id, IF(product_type_publish = 'Y', 1, 0) AS published ".$this->getProductTypesListQuery().$this->getProductTypesFilter();
     	$db->setQuery($q, $this->_pagination->limitstart, $this->_pagination->limit);
     	return $db->loadObjectList('product_type_id');
    }
    
    /**
    * List of tables to include for the product query
    * @author RolandD
    */
    private function getProductTypesListQuery() {
    	return 'FROM #__vm_product_type p
    			LEFT JOIN #__vm_product_product_type_xref x
    			ON p.product_type_id = x.product_type_id';
    }
    
    /**
    * Collect the filters for the query
    * @author RolandD
    */
    private function getProductTypesFilter() {
    	$db = JFactory::getDBO();
    	$filter_order = JRequest::getCmd('filter_order', 'product_type_list_order');
		if ($filter_order == '') $filter_order = 'product_type_list_order';
		$filter_order_Dir = JRequest::getWord('filter_order_Dir', 'desc');
		if ($filter_order_Dir == '') $filter_order_Dir = 'desc';
		
    	/* Check some filters */
     	$filters = array();
     	if (JRequest::getVar('filter_producttypes', false)) $filters[] = '#__vm_product_type.`product_type_name` LIKE '.$db->Quote('%'.JRequest::getVar('filter_producttypes').'%');
     	if (JRequest::getInt('product_id', false)) $filters[] = 'product_id = '.JRequest::getInt('product_id');
     	
     	if (count($filters) > 0) $filter = ' WHERE '.implode(' AND ', $filters);
     	else $filter = '';
     	
     	return $filter.' ORDER BY '.$filter_order." ".$filter_order_Dir;
    }
    
    /**
    * Load a single discount
    * @author RolandD
    */
    public function getProductType() {
		/* Get the product IDs to remove */
		$cids = array();
		$cids = JRequest::getVar('cid', false);
		if ($cids && !is_array($cids)) $cids = array($cids);
		
		/* First copy the product in the product table */
		$product_type_data = $this->getTable('product_type');
		
		/* Load the rating */
		if ($cids) {
			$product_type_data->load($cids[0]);
			$product_type_data->list_order = $this->getListOrder($cids[0], $product_type_data->product_type_list_order);
		}
		else {
			$product_type_data->list_order = $this->getListOrder();
		}
		
		return $product_type_data;
    }
    
    /**
    * Delete a product type
    * @author RolandD
    */
    public function removeProductType() {
    	$db = JFactory::getDBO();
		/* Get the product IDs to remove */
		$cids = array();
		$cids = JRequest::getVar('cid');
		if (!is_array($cids)) $cids = array($cids);
		
		/* Start removing */
		foreach ($cids as $key => $product_type_id) {
			/* Delete all product parameters from this product type */
			$q = 'SELECT `parameter_name` FROM `#__vm_product_type_parameter` WHERE `product_type_id`='.$product_type_id;
			$db->setQuery($q);
			$parameter_names = $db->loadObjectList();
			foreach ($parameter_names as $key => $name) {
				/**
				if( !isset($ps_product_type_parameter)) { $ps_product_type_parameter = new ps_product_type_parameter(); }
				$arr['product_type_id'] = $record_id;
				$arr['parameter_name'] = $db->f('parameter_name');
				$ps_product_type_parameter->delete_parameter( $arr );
				*/
			}
			
			$q = "DELETE FROM #__vm_product_type WHERE product_type_id = ".$product_type_id;
			$db->setQuery($q);
			$db->query();
			
			$q  = "DELETE FROM #__vm_product_product_type_xref WHERE product_type_id = ".$product_type_id;
			$db->setQuery($q);
			$db->query();
			
			$q  = "DROP TABLE IF EXISTS `#__vm_product_type_".$product_type_id."`";
			$db->setQuery($q);
			$db->query();
		}
		return true;
    }
    
    /**
    * Save a discount
    *
    * @author RolandD
    * @todo Use the J! table for moving up and down
    */
    public function saveProductType() {
    	 $db = JFactory::getDBO();
    	 
		/* Get the product IDs to remove */
		$cids = array();
		$cids = JRequest::getVar('cid');
		if (!is_array($cids)) $cids = array($cids);
		
		/* First copy the product in the product table */
		$product_type_data = $this->getTable('product_type');
		
		/* Get the posted data */
		$data = JRequest::get('post', 4);
		
		/* Bind the rating details */
		$product_type_data->bind($data);
		
		if ($cids[0] == 0) {
			/* Let's find out the last Product Type */
			$q = "SELECT MAX(product_type_list_order)+1 AS list_order FROM #__vm_product_type";
			$db->setQuery($q);
			$product_type_data->product_type_list_order = $db->loadResult();
			
			/* Check publish state */
			if ($product_type_data->product_type_publish != "Y") $product_type_data->product_type_publish = "N";
		}
		
		/* Store the product type */
		$product_type_data->store();
		
		/* Make a new product_type_<id> table if this is a new product type */
		if ($cids[0] == 0) {
			/* Make new table product_type_<id> */
			$q = "CREATE TABLE `#__vm_product_type_";
			$q .= $product_type_data->product_type_id. "` (";
			$q .= "`product_id` int(11) NOT NULL,";
			$q .= "PRIMARY KEY (`product_id`)";
			$q .= ") TYPE=MyISAM;";
			$db->setQuery($q);
			$db->query();
		}
		
		/* Re-Order the Product Type table IF the list_order has been changed */
		if ($cids[0] > 0 && intval($data['list_order']) != intval($data['currentpos'])) {
			$db = JFactory::getDBO();
			
			/* Moved UP in the list order */
			if( intval($data['list_order']) < intval($data['currentpos']) ) {
				$q  = "SELECT product_type_id FROM #__vm_product_type WHERE ";
				$q .= "product_type_id <> '" . $data["product_type_id"] . "' ";
				$q .= "AND product_type_list_order >= '" . intval($data["list_order"]) . "'";
				$db->setQuery($q);
				$moveup = $db->loadObjectList();
				foreach ($moveup as $key => $move) {
					$db->setQuery("UPDATE #__vm_product_type SET product_type_list_order=product_type_list_order+1 WHERE product_type_id='".$move->product_type_id."'");
					$db->query();
				}
			}
			// Moved DOWN in the list order
			else {
				$q = "SELECT product_type_id FROM #__vm_product_type WHERE ";
				$q .= "product_type_id <> '".$data["product_type_id"] . "' ";
				$q .= "AND product_type_list_order > '".intval($data["currentpos"])."'";
				$q .= "AND product_type_list_order <= '".intval($data["list_order"])."'";
				$db->setQuery($q);
				$movedown = $db->loadObjectList();
				foreach ($movedown as $key => $move) {
					$db->setQuery("UPDATE #__vm_product_type SET product_type_list_order=product_type_list_order-1 WHERE product_type_id='".$move->product_type_id."'");
					$db->query();
				}

			}
		} // END Re-Ordering
		
		return true;
    }
    
    /**
    * Count products using this product type
    * @author RolandD
    */
    public function getProductCount($product_type_id) {
    	$db = JFactory::getDBO();
    	$count  = "SELECT COUNT(*) AS num_rows FROM #__vm_product p
    		LEFT JOIN #__vm_product_product_type_xref x
    		ON p.product_id = x.product_id
    		WHERE x.product_type_id = ".$product_type_id."
    		AND p.product_parent_id = 0 
    		ORDER BY product_publish DESC, product_name";
		$db->setQuery($count);
		return $db->loadResult();
    }
    
    /**
    * Count parameters using this product type
    * @author RolandD
    */
    public function getParameterCount($product_type_id) {
    	$db = JFactory::getDBO();
    	$count  = "SELECT count(*) AS num_rows 
    		FROM #__vm_product_type_parameter 
    		WHERE product_type_id = ".$product_type_id;
		$db->setQuery($count);
		return $db->loadResult();
    }
    
    /**
    * Get the position where the product type needs to be
    * @author RolandD
    * @return string Dropdown list with product types
    */
    public function getListOrder($product_type_id=0, $list_order=0) {
    	$db = JFactory::getDBO();
    	$options = array();
		if (!$product_type_id) {
			return JText::_('CMN_NEW_ITEM_LAST');
		}
		else {

			$q  = "SELECT product_type_list_order, product_type_name FROM #__vm_product_type ";
			if ($product_type_id) {
				$q .= 'WHERE product_type_id='.$product_type_id;
			}
			$q .= " ORDER BY product_type_list_order ASC";
			$db->setQuery($q);
			$producttypes = $db->loadObjectList();
			
			foreach ($producttypes as $key => $producttype) {
				$options[] = JHTML::_('select.option', $producttype->product_type_list_order, $producttype->product_type_list_order.". ".$producttype->product_type_name);
			}
			return JHTML::_('select.genericlist', $options, 'list_order', '', 'value', 'text', $list_order);
		}
    }
}
?>
<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

/**
*
* Report Model
*
* @version $Id$
* @package VirtueMart
* @subpackage Report
* @copyright Copyright (C) VirtueMart Team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

jimport('joomla.application.component.model');

/**
 * Report Model
 * 
 * @package	VirtueMart
 * @subpackage Report
 * @author Wicksj
 */

class VirtuemartModelReport extends JModel {
	
    /** @var interger Primary key */
    var $_id;
    /** @var objectlist Report data */
    var $_data;
    /** @var integer Total number of report items from the database */
    var $_total;
    /** @var pagination Pagination for report list */
    var $_pagination;	
	
	/**
	 * Constructor for Report Model
	 */
	function __construct(){
		parent::__construct();
		
		$mainframe = JFactory::getApplication();
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest(JRequest::getVar('option').JRequest::getVar('view').'limitstart', 'limitstart', 0, 'int');
		
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		
	}
	/**
	 * 
	 */
	function getReport(){
		if(empty($this->_data)){
			
		}
		//Guard against returning null data list
		if(!$this->_data){
	    	$this->_data = new stdClass();
	    	$this->_id = 0;
	    	$this->_data = null;			
		}
		return $this->_data;	
	}
	
    /**
     * Pagination for the report table
     *
     * @author Wicksj
     * @return JPagination Pagination for the current list of report items
     */
    function getPagination() {
		if (empty($this->_pagination)) {
	    	jimport('joomla.html.pagination');
	    	$this->_pagination = new JPagination($this->_getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}
		return $this->_pagination;
    }

    /**
     * Gets the total number of report items
     *
     * @author Wicksj
     * @return int Total of report items in the database
     */
    function _getTotal() {
		if (empty($this->_total)) {
	    	$query = 'SELECT `order_id` FROM `#__virtuemart_orders`';
	    	$this->_total = $this->_getListCount($query);
		}
		return $this->_total;
    }
    
   /**
     * Retrieve a list of report items from the database.
     *
     * @author Wicksj
     * @param string $noLimit True if no record count limit is used, false otherwise
     * @return object List of order objects 
     */
    function getRevenue($start_date, $end_date, $noLimit = false){
    	$db = JFactory::getDBO();
    	
		if(empty($start_date) || empty($end_date)){
			$curDate = JFactory::getDate();
			$start_date = $curDate;
			$end_date = $curDate;
		}
		$query = "SELECT FROM_UNIXTIME(`created_on`, '%M, %Y') as order_date, ";
		$query .= "FROM_UNIXTIME(`created_on`,GET_FORMAT(DATE,'INTERNAL')) as date_num, ";
		$query .= "COUNT(order_id) as number_of_orders, ";
		$query .= "SUM(order_subtotal) as revenue ";
		$query .= "FROM `#__virtuemart_orders` ";
		$query .= "WHERE `created_on` BETWEEN UNIX_TIMESTAMP('" . $start_date->toMySQL() . "') AND UNIX_TIMESTAMP('" . $end_date->toMySQL() . "') "; 
		$query .= "GROUP BY order_date ";
		$query .= "ORDER BY date_num ASC ";
		
		if($noLimit){
			$this->_data = $this->_getList($query);
		}
		else{
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		
		//Guard against returning null data list
		if(!$this->_data){
	    	$this->_data = new stdClass();
	    	$this->_id = 0;
	    	$this->_data = null;			
		}

		return $this->_data;    	
    }
    
  /**
     * Retrieve a list of report items from the database.
     *
     * @author Wicksj
     * @param string $noLimit True if no record count limit is used, false otherwise
     * @return object List of order objects 
     */
    function getItemsSold($noLimit = false){
    	$db = JFactory::getDBO();
    	
		$query = "SELECT FROM_UNIXTIME(`created_on`, '%M, %Y') as order_date, ";
		$query .= "FROM_UNIXTIME(`created_on`,GET_FORMAT(DATE,'INTERNAL')) as date_num, ";
		$query .= "SUM(product_quantity) as items_sold ";
		$query .= "FROM `#__virtuemart_order_items` ";
		//WHERE created_on BETWEEN '" . $start_date . "' AND '" . $end_date . "' 
		$query .= "GROUP BY order_date ";
		$query .= "ORDER BY date_num ASC ";
		
		if($noLimit){
			$this->_data = $this->_getList($query);
		}
		else{
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		
		//Guard against returning null data list
		if(!$this->_data){
	    	$this->_data = new stdClass();
	    	$this->_id = 0;
	    	$this->_data = null;			
		}

		return $this->_data;    	
    }
    
   /**
     * Retrieve a list of report items from the database.
     *
     * @author Wicksj
     * @param string $noLimit True if no record count limit is used, false otherwise
     * @return object List of order objects 
     */
    function getProductList($noLimit = false){
    	$db = JFactory::getDBO();
    	
		$query = "SELECT `product_name`, `product_sku`, ";
		$query .= "FROM_UNIXTIME('i.created_on', '%M, %Y') as order_date, ";
		$query .= "FROM_UNIXTIME('o.created_on',GET_FORMAT(DATE,'INTERNAL')) as date_num, ";
		$query .= "SUM(product_quantity) as items_sold ";
  		$query .= "FROM #__virtuemart_order_items i, #__virtuemart_orders o, #__virtuemart_products p ";
		//WHERE #__virtuemart_order_items.created_on BETWEEN '" . $start_date . "' AND '" . $end_date . "' 
		//$query .= "AND o.order_id=i.order_id ";
		$query .= "WHERE o.order_id=i.order_id ";
  		$query .= "AND i.product_id=p.product_id ";
  		$query .= "GROUP BY product_sku, product_name, order_date ";
  		$query .= "ORDER BY date_num, product_name ASC";
		
		if($noLimit){
			$this->_data = $this->_getList($query);
		}
		else{
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		
		//Guard against returning null data list
		if(!$this->_data){
	    	$this->_data = new stdClass();
	    	$this->_id = 0;
	    	$this->_data = null;			
		}

		return $this->_data;    	
    }    
}

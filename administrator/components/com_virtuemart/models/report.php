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
		$start_date = $mainframe->getUserStateFromRequest(JRequest::getVar('from_period').JRequest::getVar('view').'from_period','from_period');
		$end_date = $mainframe->getUserStateFromRequest(JRequest::getVar('until_period').JRequest::getVar('view').'until_period','until_period');
				
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		$this->setState('start_date',$start_date);
		$this->setState('end_date',$end_date);		
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
			if(empty($this->start_date) || empty($this->end_date)){
				$curDate = JFactory::getDate();
				$startDate = $curDate->toFormat('%Y-%m-%d');
				$endDate = $curDate->toFormat('%Y-%m-%d');
			}
			else{
				$startDate = $this->start_date;
				$endDate = $this->end_date;
			}
			
	    	$query = 'SELECT `virtuemart_order_id` FROM `#__virtuemart_orders`';
			$query .= "WHERE `created_on` BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59' "; 			
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
    	
		if(empty($this->start_date) || empty($this->end_date)){
			$curDate = JFactory::getDate();
			$startDate = $curDate->toFormat('%Y-%m-%d');
			$endDate = $curDate->toFormat('%Y-%m-%d');
		}
		else{
			$startDate = $this->start_date;
			$endDate = $this->end_date;
		}

		$query = "SELECT `created_on` as order_date, ";
		$query .= "COUNT(virtuemart_order_id) as number_of_orders, ";
		$query .= "SUM(order_subtotal) as revenue ";
		$query .= "FROM `#__virtuemart_orders` ";
		$query .= "WHERE `created_on` BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59' "; 
		$query .= "GROUP BY order_date ";
		$query .= "ORDER BY order_date ASC ";
		
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

		if(empty($this->start_date) || empty($this->end_date)){
			$curDate = JFactory::getDate();
			$startDate = $curDate->toFormat('%Y-%m-%d');
			$endDate = $curDate->toFormat('%Y-%m-%d');
		}
		else{
			$startDate = $this->start_date;
			$endDate = $this->end_date;
		}
    	
		$query = "SELECT `created_on` as order_date, ";
		$query .= "SUM(product_quantity) as items_sold ";
		$query .= "FROM `#__virtuemart_order_items` ";
		$query .= "WHERE `created_on` BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59' "; 
		$query .= "GROUP BY order_date ";
		$query .= "ORDER BY order_date ASC ";
		
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

		if(empty($this->start_date) || empty($this->end_date)){
			$curDate = JFactory::getDate();
			$startDate = $curDate->toFormat('%Y-%m-%d');
			$endDate = $curDate->toFormat('%Y-%m-%d');
		}
		else{
			$startDate = $this->start_date;
			$endDate = $this->end_date;
		}
    	
		$query = "SELECT `product_name`, `product_sku`, ";
		$query .= "i.created_on as order_date, ";
		$query .= "SUM(product_quantity) as items_sold ";
  		$query .= "FROM #__virtuemart_order_items i, #__virtuemart_orders o, #__virtuemart_products p ";
		$query .= "WHERE i.created_on BETWEEN '$startDate 00:00:00' AND '$endDate 23:59:59' "; 
		$query .= "AND o.virtuemart_order_id=i.virtuemart_order_id ";
  		$query .= "AND i.virtuemart_product_id=p.virtuemart_product_id ";
  		$query .= "GROUP BY product_sku, product_name, order_date ";
  		$query .= "ORDER BY order_date, product_name ASC";
		
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

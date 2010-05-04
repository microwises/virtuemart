<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

/**
*
* Report Model
*
* @version 
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
	    	$query = 'SELECT `order_id` FROM `#__vm_orders`';
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
    function getReports( $noLimit = false){
		if(empty($this->_data)){
			$query = "SELECT FROM_UNIXTIME(`cdate`, '%M, %Y') as order_date, ";
			$query .= "FROM_UNIXTIME(`cdate`,GET_FORMAT(DATE,'INTERNAL')) as date_num, ";
			$query .= "COUNT(order_id) as number_of_orders, ";
			$query .= "SUM(order_subtotal) as revenue ";
			$query .= "FROM `#__vm_orders` ";
			$query .= "GROUP BY order_date ";
			$query .= "ORDER BY date_num ASC ";
			
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
?>
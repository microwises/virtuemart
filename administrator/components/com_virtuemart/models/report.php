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

if(!class_exists('VmModel'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');

/**
 * Report Model
 * TODO nothing is displayed
 * @package	VirtueMart
 * @subpackage Report
 * @author Wicksj
 */

class VirtuemartModelReport extends VmModel {

	var $start_date ='';
	var $end_date ='';
	var $date_presets = null;
	function __construct(){
		parent::__construct();
		$this->setMainTable('orders');
	}

	/*
	* Set Start & end Date
	*/
	function  setPeriod($start_date, $end_date){

		$start_date = JFactory::getDate($start_date);
		$this->start_date = $start_date->toFormat('%Y-%m-%d');
		$end_date = JFactory::getDate($end_date);
		$this->end_date = $end_date->toFormat('%Y-%m-%d');
	}	/*
	* Set Start & end Date
	*/
	function  setPeriodByPreset($period){
		$this->start_date = $this->date_presets[$period]['from'];
		$this->end_date = $this->date_presets[$period]['until'];
	}
	
   /**
     * Retrieve a list of report items from the database.
     *
     * @author Wicksj
     * @param string $noLimit True if no record count limit is used, false otherwise
     * @return object List of order objects
	 TODO Add this for grouping by date and orders
	 SELECT DATEADD(dd,DATEDIFF(dd,0,created_on),0) AS date,
	SUM(number_of_orders) AS number_of_orders
	FROM Table
	GROUP BY DATEADD(dd,DATEDIFF(dd,0,created_on),0)
	ORDER BY date DESC
	 
	 
     */
    function getRevenue( $noLimit = false){
    	//$db = JFactory::getDBO();

		$query = "SELECT `created_on` as order_date, 
			COUNT(virtuemart_order_id) as number_of_orders, 
			SUM(order_subtotal) as revenue 
			FROM `#__virtuemart_orders` 
			WHERE `created_on` BETWEEN '{$this->start_date} 00:00:00' AND '{$this->end_date} 23:59:59' 
			GROUP BY order_date ";
			$mainframe = JFactory::getApplication() ;
			$filter_order     = $mainframe->getUserStateFromRequest( 'com_virtuemart.report.filter_order', 'filter_order', 'order_date', 'cmd' );
			if ($filter_order == 'order_date' or $filter_order == 'virtuemart_order_id') {
				$query .= $this->_getOrdering('order_date', 'DESC');
			}

		if($noLimit){
			$this->_data = $this->_getList($query);
		}
		else{
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		if (!$this->_total) $this->_total = $this->_getListCount($query);

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
    	// $db = JFactory::getDBO();

		$query = "SELECT `created_on` as order_date, ";
		$query .= "SUM(product_quantity) as items_sold ";
		$query .= "FROM `#__virtuemart_order_items` ";
		$query .= "WHERE `created_on` BETWEEN '{$this->start_date} 00:00:00' AND '{$this->end_date} 23:59:59' ";
		$query .= "GROUP BY order_date ";
		$query .= "ORDER BY order_date ASC ";
		if($noLimit){
			$this->_data = $this->_getList($query);
		}
		else{
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
		}
		if (!$this->_total) $this->_total = $this->_getListCount($query);

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
    	// $db = JFactory::getDBO();

		$query = "SELECT `product_name`, `product_sku`, ";
		$query .= "i.created_on as order_date, ";
		$query .= "SUM(product_quantity) as items_sold ";
  		$query .= "FROM #__virtuemart_order_items i, #__virtuemart_orders o, #__virtuemart_products p ";
		$query .= "WHERE i.created_on BETWEEN '{$this->start_date} 00:00:00' AND '{$this->end_date} 23:59:59' ";
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
		if (!$this->_total) $this->_total = $this->_getListCount($query);

		return $this->_data;
    }
	public function getDatePresets(){
		if ($this->date_presets) return $this->date_presets;
		// set date presets
		$curDate   = JFactory::getDate();
		$curDate   = $curDate->toUnix();
		$curDate   = mktime(0, 0, 0, date('m', $curDate), date('d', $curDate), date('Y', $curDate));
		$monday = (date('w', $curDate) == 1) ? $curDate : strtotime('last Monday', $curDate);
		$this->date_presets['last90'] = array(
			'name'  => JText::_('COM_VIRTUEMART_REPORT_PERIOD_LAST90'),
			'from'  => date('Y-m-d', strtotime('-89 day', $curDate)),
			'until' => date('Y-m-d', $curDate));
		$this->date_presets['last60'] = array(
			'name'  => JText::_('COM_VIRTUEMART_REPORT_PERIOD_LAST60'),
			'from'  => date('Y-m-d', strtotime('-59 day', $curDate)),
			'until' => date('Y-m-d', $curDate));
		$this->date_presets['last30'] = array(
			'name'  => JText::_('COM_VIRTUEMART_REPORT_PERIOD_LAST30'),
			'from'  => date('Y-m-d', strtotime('-29 day', $curDate)),
			'until' => date('Y-m-d', $curDate));
		$this->date_presets['today'] = array(
			'name'  => JText::_('COM_VIRTUEMART_REPORT_PERIOD_TODAY'),
			'from'  => date('Y-m-d', $curDate),
			'until' => date('Y-m-d', $curDate));
		$this->date_presets['this-week'] = array(
			'name'  => JText::_('COM_VIRTUEMART_REPORT_PERIOD_THIS_WEEK'),
			'from'  => date('Y-m-d', $monday),
			'until' => date('Y-m-d', strtotime('+6 day', $monday)));
		$this->date_presets['this-month'] = array(
			'name'  => JText::_('COM_VIRTUEMART_REPORT_PERIOD_THIS_MONTH'),
			'from'  => date('Y-m-d', mktime(0, 0, 0, date('n', $curDate), 1, date('Y', $curDate))),
			'until' => date('Y-m-d', mktime(0, 0, 0, date('n', $curDate)+1, 0, date('Y', $curDate))));
		$this->date_presets['this-year'] = array(
			'name'  => JText::_('COM_VIRTUEMART_REPORT_PERIOD_THIS_YEAR'),
			'from'  => date('Y-m-d', mktime(0, 0, 0, 1, 1, date('Y', $curDate))),
			'until' => date('Y-m-d', mktime(0, 0, 0, 12, 31, date('Y', $curDate))));

			return $this->date_presets;

	}

	public function renderDateSelectList( $from_period, $until_period){
		// simpledate select
		$date_presets =  $this->getDatePresets() ;
		$select  = '';
		$options = array(JHTML::_('select.option', '', '- '.JText::_('COM_VIRTUEMART_REPORT_SET_PERIOD').' -', 'text', 'value'));
		foreach ($date_presets as $name => $value) {
			$options[] = JHTML::_('select.option', $name, JText::_($value['name']), 'text', 'value');
			if ($value['from'] == $from_period && $value['until'] == $until_period) {
				$select = $name;
			}
		}
		$listHTML = JHTML::_('select.genericlist', $options, 'period', 'class="inputbox" onchange="this.form.submit();" size="1"', 'text', 'value', $select);
		return $listHTML;
	}
}

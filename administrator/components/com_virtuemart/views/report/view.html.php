<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

/**
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

if(!class_exists('VmView'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmview.php');

/**
 * Report View class
 *
 * @package	VirtueMart
 * @subpackage Report
 * @author Wicksj
 */
class VirtuemartViewReport extends VmView {

	/**
	 * Render the view
	 */
	function display($tpl = null){

		// Load the helper(s)

		$this->loadHelper('html');

		$this->loadHelper('html');
		$this->loadHelper('currencydisplay');
		$this->loadHelper('reportFunctions');

		$model		= VmModel::getModel();
		// $config		= JFactory::getConfig();
		// $tzoffset	= $config->getValue('config.offset');
		JRequest::setvar('task','');
		// set period
		//$date_presets = $model->getDatePresets();

		$this->SetViewTitle('REPORT');

		$myCurrencyDisplay = CurrencyDisplay::getInstance();

		$this->addStandardDefaultViewLists($model);
		$revenueBasic = $model->getRevenue();
// 		vmdebug('VirtuemartViewReport revenue',$revenueBasic);
		if($revenueBasic){
			$totalReport['revenueTotal']= $totalReport['number_of_ordersTotal'] = $totalReport['itemsSoldTotal'] = 0 ;
			foreach($revenueBasic as &$j){

				$totalReport['revenueTotal'] += $j['order_subtotal'];
				$totalReport['number_of_ordersTotal'] += $j['count_order_id'];
				$j['order_subtotal'] = $myCurrencyDisplay->priceDisplay($j['order_subtotal']);
				$j['product_quantity'] = $model->getItemsByRevenue($j);
				$totalReport['itemsSoldTotal'] +=$j['product_quantity'];
			}
			$totalReport['revenueTotal'] = $myCurrencyDisplay->priceDisplay($totalReport['revenueTotal']);

			// if ( 'product_quantity'==JRequest::getWord('filter_order')) {
				// foreach ($revenueBasic as $key => $row) {
					// $created_on[] =$row['created_on'];
					// $intervals[] =$row['intervals'];
					// $itemsSold[] =$row['product_quantity'];
					// $number_of_orders[] =$row['count_order_id'];
					// $revenue[] =$row['revenue'];

				// }
				// if (JRequest::getWord('filter_order_Dir') == 'desc') array_multisort($itemsSold, SORT_DESC,$revenueBasic);
				// else array_multisort($itemsSold, SORT_ASC,$revenueBasic);
			// }
		}
		$this->assignRef('report', $revenueBasic);
		$this->assignRef('totalReport', $totalReport);

		//$itemsSold = $model->getItemsSold($revenueBasic);
		//$this->assignRef('itemsSold', $itemsSold);
		// I tihnk is to use in a different layout such as product solds
		// PATRICK K.
		// $productList = $model->getOrderItems();
		// $this->assignRef('productList', $productList);


		$this->lists['select_date'] = $model->renderDateSelectList();
		$this->lists['state_list'] = $model->renderOrderstatesList();
		$this->lists['intervals'] = $model->renderIntervalsList();
		$this->assignRef('from_period', $model->from_period);
		$this->assignRef('until_period', $model->until_period);

		$pagination = $model->getPagination();
		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
	}
}

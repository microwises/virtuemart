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

jimport('joomla.application.component.view');

/**
 * Report View class
 *
 * @package	VirtueMart
 * @subpackage Report
 * @author Wicksj
 */
class VirtuemartViewReport extends JView {

	/**
	 * Render the view
	 */
	function display($tpl = null){

		// Load the helper(s)
		$this->loadHelper('adminui');
		$this->loadHelper('html');
		$this->loadHelper('shopFunctions');
		$this->loadHelper('currencydisplay');
		$this->loadHelper('reportFunctions');

		$model		= $this->getModel();
		// $config		= JFactory::getConfig();
		// $tzoffset	= $config->getValue('config.offset');
		JRequest::setvar('task','');
		// set period
		//$date_presets = $model->getDatePresets();

		$viewName = ShopFunctions::SetViewTitle('REPORT');
		$this->assignRef('viewName', $viewName);

		$lists['select_date'] = $model->renderDateSelectList();
		$lists['state_list'] = $model->renderOrderstatesList();
		$lists['intervals'] = $model->renderIntervalsList();

		$myCurrencyDisplay = CurrencyDisplay::getInstance();

		$revenueBasic = $model->getRevenue();
// 		vmdebug('VirtuemartViewReport revenue',$revenueBasic);
		if($revenueBasic){
			$totalReport['revenueTotal']= $totalReport['number_of_ordersTotal'] = $totalReport['itemsSoldTotal'] = 0 ;
			foreach($revenueBasic as &$j){

				$totalReport['revenueTotal'] += $j['order_subtotal'];
				$totalReport['number_of_ordersTotal'] += $j['number_of_orders'];
				$j['order_subtotal'] = $myCurrencyDisplay->priceDisplay($j['order_subtotal'],'',false);
				$j['product_quantity'] = $model->getItemsByRevenue($j);
				$totalReport['itemsSoldTotal'] +=$j['product_quantity'];
			}
			$totalReport['revenueTotal'] = $myCurrencyDisplay->priceDisplay($totalReport['revenueTotal'],'',false);

			if ( 'product_quantity'==JRequest::getWord('filter_order')) {
				foreach ($revenueBasic as $key => $row) {
					$created_on[] =$row['created_on'];
					$intervals[] =$row['intervals'];
					$itemsSold[] =$row['product_quantity'];
					$number_of_orders[] =$row['number_of_orders'];
					$revenue[] =$row['revenue'];

				}
				if (JRequest::getWord('filter_order_Dir') == 'desc') array_multisort($itemsSold, SORT_DESC,$revenueBasic);
				else array_multisort($itemsSold, SORT_ASC,$revenueBasic);
			}
		}
		$this->assignRef('report', $revenueBasic);
		$this->assignRef('totalReport', $totalReport);

		//$itemsSold = $model->getItemsSold($revenueBasic);
		//$this->assignRef('itemsSold', $itemsSold);
		// I tihnk is to use in a different layout such as product solds
		// PATRICK K.
		// $productList = $model->getOrderItems();
		// $this->assignRef('productList', $productList);

		$lists = array_merge ($lists ,ShopFunctions::addStandardDefaultViewLists($model));
		$this->assignRef('lists', $lists);

		$this->assignRef('from_period', $model->from_period);
		$this->assignRef('until_period', $model->until_period);

		parent::display($tpl);
	}
}

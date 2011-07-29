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

		$config   = JFactory::getConfig();
		$task = JRequest::getWord('task');

		$period = JRequest::getVar('period', '');
		$from_period  = JRequest::getVar('from_period', '');
		$until_period = JRequest::getVar('until_period', '');

		JToolbarHelper::title( JText::_('COM_VIRTUEMART_REPORT'), 'vm_report_48');

		// Load the helper(s)
		$this->loadHelper('adminui');
		$this->loadHelper('shopFunctions');
		$this->loadHelper('currencydisplay');
		$this->loadHelper('reportFunctions');
		//JHTML::_('behavior.calendar');

		$model = $this->getModel();

		switch($task){
			default:{







				// set period
				$date_presets = $model->getDatePresets();
				$tzoffset     = $config->getValue('config.offset');
				// check period - set to defaults if no value is set or dates cannot be parsed
				if (empty($period)) {
					if (empty($from_period) && empty($until_period)) {
						$from_period  = $date_presets['today']['from'];
						$until_period = $date_presets['today']['until'];
					}
					$from         = JFactory::getDate($from_period, $tzoffset);
					$until        = JFactory::getDate($until_period, $tzoffset);
					$from = (strtotime($from) == -1) ? false : strtotime($from);
					$until = (strtotime($until) == -1) ? false : strtotime($until);
					$model->setPeriod($from, $until);
				} else {
					$model->setPeriodByPreset($period);
					$from_period  = $model->start_date ;
					$until_period = $model->end_date ;
				}

				
				$lists['select_date'] = $model->renderDateSelectList($date_presets, $from_period, $until_period);
				

				$myCurrencyDisplay = CurrencyDisplay::getInstance();

				$revenueBasic = $model->getRevenue();
				if(is_array($revenueBasic)){
					foreach($revenueBasic as $i => $j){
						$j->revenue = $myCurrencyDisplay->priceDisplay($j->revenue,'',false);
					}
					unset($i);
				}
				$this->assignRef('report', $revenueBasic);

				$itemsSold = $model->getItemsSold();
				$this->assignRef('itemsSold', $itemsSold);

				$productList = $model->getProductList();
				$this->assignRef('productList', $productList);

				$lists = array_merge ($lists ,ShopFunctions::addStandardDefaultViewLists($model));
				$this->assignRef('lists', $lists);

				$this->assignRef('from_period', $from_period);
				$this->assignRef('until_period', $until_period);
			}
		}

		parent::display($tpl);
	}
}

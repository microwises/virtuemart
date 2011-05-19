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

		$lists = array();
		$mainframe = JFactory::getApplication();
		$option = JRequest::getVar('option');
		$config   =& JFactory::getConfig();
		$curTask = JRequest::getVar('task');
		$layoutName = JRequest::getVar('layout','default');

		$from_period  = JRequest::getVar('from_period', '');
		$until_period = JRequest::getVar('until_period', '');

		JToolbarHelper::title( JText::_('COM_VIRTUEMART_REPORT_MOD'), 'vm_report_48');

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('currencydisplay');
		$this->loadHelper('reportFunctions');

		JHTML::_('behavior.tooltip');
		JHTML::_('behavior.calendar');

		$model = $this->getModel();

		switch($curTask){
			default:{

				$pagination = $model->getPagination();
				$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
				$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');

				$date_presets = ReportFunctions::getDatePresets();
				$lists['select_date'] = ReportFunctions::renderDateSelectList($date_presets, $from_period, $until_period);

				// set period
				$tzoffset     = $config->getValue('config.offset');
				// check period - set to defaults if no value is set or dates cannot be parsed
				if (empty($from_period) && empty($until_period)) {
					$from_period  = $date_presets['today']['from'];
					$until_period = $date_presets['today']['until'];
					$from         =& JFactory::getDate($from_period, $tzoffset);
					$until        =& JFactory::getDate($until_period, $tzoffset);
				} else {
					$from         =& JFactory::getDate($from_period, $tzoffset);
					$until        =& JFactory::getDate($until_period, $tzoffset);
				}

				$this->assignRef('pagination', $pagination);

				$myCurrencyDisplay = CurrencyDisplay::getInstance();

				$revenueBasic = $model->getRevenue($from, $until);
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

				$this->assignRef('lists', $lists);
				$this->assignRef('from_period', $from_period);
				$this->assignRef('until_period', $until_period);
			}
		}

		parent::display($tpl);
	}
}

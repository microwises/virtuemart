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
		$curTask = JRequest::getVar('task');
		$layoutName = JRequest::getVar('layout','default');

		JToolbarHelper::title( JText::_('COM_VIRTUEMART_REPORT_MOD'), 'vm_report_48');

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('currencydisplay');

		$model = $this->getModel();

		$pagination = $model->getPagination();
		$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
	    $lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');

		$this->assignRef('pagination', $pagination);

		$myCurrencyDisplay = CurrencyDisplay::getCurrencyDisplay();

		$revenueBasic = $model->getRevenue();
        if(is_array($revenueBasic)){
		    foreach($revenueBasic as $i => $j){
			    $j->revenue = $myCurrencyDisplay->getValue($j->revenue);
		    }
            unset($i);
        }

		$this->assignRef('revenueBasic', $revenueBasic);

		$itemsSold = $model->getItemsSold();
		$this->assignRef('itemsSold', $itemsSold);

		$productList = $model->getProductList();
		$this->assignRef('productList', $productList);

		$this->assignRef('lists', $lists);

		parent::display($tpl);
	}
}

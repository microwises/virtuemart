<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

/**
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
		$this->loadHelper('adminMenu');

		$model = $this->getModel();
	
		$layoutName = JRequest::getVar('layout','default');
		
		JToolbarHelper::title( JText::_('VM_REPORT_MOD'), 'vm_report_48');
		
		$pagination = $model->getPagination();
		$this->assignRef('pagination', $pagination);
		
		$reports = $model->getReports();
		$this->assignRef('report', $reports);
		
		parent::display($tpl);
	}
}
?>
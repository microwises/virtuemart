<?php
/**
*
* Extensions View
*
* @package	VirtueMart
* @subpackage Extensions
* @author StephanieS
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the list of extensions
 *
 * @package	VirtueMart
 * @subpackage Extensions
 * @author Max Milbers
 */
class VirtuemartViewUsergroups extends JView {

	function display( $tpl = null ){

		$this->loadHelper('adminMenu');
		$model = $this->getModel();

		$layoutName = JRequest::getVar('layout', 'default');
		
		if ($layoutName == 'edit') {
//			
//			$this->loadHelper('image');
//			$this->loadHelper('html');

//			jimport('joomla.html.pane');
//			
//			$this->loadHelper('shopFunctions');

			$usergroup = $model->getUsergroup();
			$this->assignRef('usergroup',	$usergroup);

			JToolBarHelper::title( JText::_('COM_VIRTUEMART_USERGROUPS_ADD' ).': <small><small>[ Edit ]</small></small>', 'vm_countries_48');
			JToolBarHelper::divider();
			JToolBarHelper::apply();
			JToolBarHelper::save();
			JToolBarHelper::cancel('cancel', 'Close');

		} else {
//			$db = JFactory::getDBO();
			
			JToolBarHelper::title( JText::_( 'COM_VIRTUEMART_USERGROUPS_LIST' ), 'vm_countries_48' );
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();	
	
			$this->loadHelper('shopFunctions');
			
			
			$pagination = $model->getPagination();			
			$this->assignRef('pagination',	$pagination);	
			
			$ugroups = $model->getUsergroups(false,true);
			$this->assignRef('usergroups',	$ugroups);
			
		}

		parent::display($tpl);
	}

//	/**
//	 * Prepares the selection for the TreeLists
//	 * 
//	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
//	 * @author Max Milbers
//	 * @param $value the selected values, may be single data or array
//	 * @return $values prepared array to work with JHTML::_('Select.genericlist')
//	 */
//	function prepareTreeSelection($values){
//		if (!isset($values)){
//			return;
//		}
//		if (!is_array($values)) $values = array($values);
//		foreach ($values as $value) {
//			$values[$value]  = 1;
//		}
//		return $values;
//	}
//	
//	
//	/**
//	 * Builds a list to choose the mathematical operations
//	 * When you want to add extra operations, look in helpers/calculationh.php for more information
//	 * 
//	 * @copyright 	Copyright (c) 2009 VirtueMart Team. All rights reserved.
//	 * @author 		Max Milbers
//	 * @param 	$selected 	the selected values, may be single data or array
//	 * @return 	$list 		list of the Entrypoints  
//	 */
//	 
//	function renderPaymentTypesList($selected){
//		$selected = self::prepareTreeSelection($selected);
//		$list = array(
//		'0' => array('paym_type' => 'Y', 'paym_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_USE_PP')),
//		'1' => array('paym_type' => 'B', 'paym_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_BANK_DEBIT')),
//		'2' => array('paym_type' => 'N', 'paym_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_AO')),
//		'3' => array('paym_type' => 'P', 'paym_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_FORMBASED'))
//		);
//
//		$listHTML = JHTML::_('Select.genericlist', $list, 'paym_type', '', 'paym_type', 'paym_type_name', $selected );
//		return $listHTML;
//	}
	
}
// pure php no closing tag

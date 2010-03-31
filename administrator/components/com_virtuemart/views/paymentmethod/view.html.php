<?php
/**
*
* Calc View
*
* @package	VirtueMart
* @subpackage Calculation tool
* @author Max Milbers
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
 * Description
 *
 * @package		VirtueMart
 * @author
 */

class VirtuemartViewPaymentMethod extends JView {
	
	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('permissions');
		$this->assignRef('perms', Permissions::getInstance());
		
		$model = $this->getModel('paymentmethod');

		//@todo should be depended by loggedVendor
		$vendorId=1;
		$this->assignRef('vendorId', $vendorId);
		
		
		$layoutName = JRequest::getVar('layout', 'default');
		if ($layoutName == 'edit') {

			// Load the helper(s)
//			$this->loadHelper('adminMenu');
			$this->loadHelper('image');
			$this->loadHelper('html');
			$this->loadHelper('parameterparser');
			jimport('joomla.html.pane');
			
			$this->loadHelper('shopFunctions');
			
			$paym = $model->getPaym();
			$this->assignRef('paym',	$paym);

			$isNew = ($paym->paym_id < 1);
			if ($isNew) {
				JToolBarHelper::title(  JText::_('VM_PAYM_LIST_ADD' ).': <small><small>[ New ]</small></small>', 'vm_countries_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
			}
			else {
				JToolBarHelper::title( JText::_('VM_PAYM_LIST_EDIT' ).': <small><small>[ Edit ]</small></small>', 'vm_countries_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
			}

			$this->assignRef('PaymentTypeList',self::renderPaymentTypesList($paym->paym_type));

			$shopperGroupList= ShopFunctions::renderShopperGroupList($paym->paym_shopper_groups);
			$this->assignRef('shopperGroupList', $shopperGroupList);

			$vendorList= ShopFunctions::renderVendorList($paym->paym_vendor_id,True);
			$this->assignRef('vendorList', $vendorList);
        }
        else {
			JToolBarHelper::title( JText::_( 'VM_PAYM_LIST_LBL' ), 'vm_countries_48' );
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();	
	
			$pagination = $model->getPagination();			
			$this->assignRef('pagination',	$pagination);	
			
			$payms = $model->getPayms();
			$this->assignRef('payms',	$payms);
		
		}

		parent::display($tpl);
	}

	
	/**
	 * Builds a list to choose the Payment type
	 * 
	 * @copyright 	Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author 		Max Milbers
	 * @param 	$selected 	the selected values, may be single data or array
	 * @return 	$list 		list of the Entrypoints  
	 */
	 
	function renderPaymentTypesList($selected){
		$this->loadHelper('modelfunctions');
		$selected = modelfunctions::prepareTreeSelection($selected);
		$list = array(
		'0' => array('paym_type' => 'Y', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_USE_PP')),
		'1' => array('paym_type' => 'B', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_BANK_DEBIT')),
		'2' => array('paym_type' => 'N', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_AO')),
		'3' => array('paym_type' => 'P', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_FORMBASED'))
		);

		$listHTML = JHTML::_('Select.genericlist', $list, 'paym_type', '', 'paym_type', 'paym_type_name', $selected );
		return $listHTML;
	}
	
}
?>
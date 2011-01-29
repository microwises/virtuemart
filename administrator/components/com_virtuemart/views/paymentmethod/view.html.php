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
		$this->addHelperPath(JPATH_COMPONENT_SITE.DS.'helpers');
		$this->loadHelper('adminMenu');
		$this->loadHelper('permissions');
		$this->loadHelper('vmpaymentplugin');
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

			$this->assignRef('vmPPaymentList', self::renderInstalledPaymentPlugins($paym->paym_jplugin_id));
//			$this->assignRef('PaymentTypeList',self::renderPaymentRadioList($paym->paym_type));

//			$this->assignRef('creditCardList',self::renderCreditCardRadioList($paym->paym_creditcards));
//			echo 'humpf <pre>'.print_r($paym).'</pre>' ;
			$this->assignRef('creditCardList',ShopFunctions::renderCreditCardList($paym->paym_creditcards,true));

			$this->assignRef('shopperGroupList', ShopFunctions::renderShopperGroupList($paym->paym_shopper_groups));

			$vendorList= ShopFunctions::renderVendorList($paym->paym_vendor_id);
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
		'0' => array('paym_type' => 'C', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_CREDIT')),
		'1' => array('paym_type' => 'Y', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_USE_PP')),
		'2' => array('paym_type' => 'B', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_BANK_DEBIT')),
		'3' => array('paym_type' => 'N', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_AO')),
		'4' => array('paym_type' => 'P', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_FORMBASED'))
		);

		$listHTML = JHTML::_('Select.genericlist', $list, 'paym_type', '', 'paym_type', 'paym_type_name', $selected );
		return $listHTML;
	}

	function renderPaymentRadioList($selected){

		$list = array(
		'0' => array('paym_type' => 'C', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_CREDIT')),
		'1' => array('paym_type' => 'Y', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_USE_PP')),
		'2' => array('paym_type' => 'B', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_BANK_DEBIT')),
		'3' => array('paym_type' => 'N', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_AO')),
		'4' => array('paym_type' => 'P', 'paym_type_name' => JText::_('VM_PAYMENT_FORM_FORMBASED'))
		);
//		$listHTML='<div id="paymList">';
		$listHTML='';
		foreach($list as $item){
			if($item['paym_type']==$selected) $checked='checked="checked"'; else $checked='';
			if($item['paym_type']=='Y' || $item['paym_type']=='C') $id = 'pam_type_CC_on'; else $id='pam_type_CC_off';
			$listHTML .= '<input id="'.$id.'" type="radio" name="paym_type" value="'.$item['paym_type'].'" '.$checked.'>'.$item['paym_type_name'].' <br />';
		}
//		$listHTML .= '</div>';
//		echo $listHTML;die;
		return $listHTML;
	}

	function renderInstalledPaymentPlugins($selected){

		$db = JFactory::getDBO();
		//Todo speed optimize that, on the other hand this function is NOT often used and then only by the vendors
//		$q = 'SELECT * FROM #__plugins as pl JOIN `#__vm_payment_method` AS pm ON `pl`.`id`=`pm`.`paym_jplugin_id` WHERE `folder` = "vmpayment" AND `published`="1" ';
//		$q = 'SELECT * FROM #__plugins as pl,#__vm_payment_method as pm  WHERE `folder` = "vmpayment" AND `published`="1" AND pl.id=pm.paym_jplugin_id';
		$q = 'SELECT * FROM #__extensions WHERE `folder` = "vmpayment" AND `enabled`="1" ';
		$db->setQuery($q);
		$result = $db->loadAssocList();

		$listHTML='<select id="paym_jplugin_id" name="paym_jplugin_id">';

		foreach($result as $paym){
			$params = new JParameter($paym['params']);
			if($paym['extension_id']==$selected) $checked='selected="selected"'; else $checked='';
			// Get plugin info
			$pType = $params->getValue('pType');
			if($pType=='Y' || $pType=='C') $id = 'pam_type_CC_on'; else $id='pam_type_CC_off';
			$listHTML .= '<option id="'.$id.'" '.$checked.' value="'.$paym['extension_id'].'">'.$paym['name'].'</option>';

		}
		$listHTML .= '</select>';

		return $listHTML;
	}

}
// pure php not tag

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
		$this->addHelperPath(JPATH_VM_SITE.DS.'helpers');
		$this->loadHelper('adminMenu');
		$this->loadHelper('permissions');
		$this->loadHelper('vmpaymentplugin');
		$this->loadHelper('shopFunctions');

		$this->assignRef('perms', Permissions::getInstance());

		$model = $this->getModel('paymentmethod');

		//@todo should be depended by loggedVendor
		$vendorId=1;
		$this->assignRef('vendorId', $vendorId);
		// TODO logo
		$viewName=ShopFunctions::SetViewTitle('vm_payment_48');
		$this->assignRef('viewName',$viewName);

		$layoutName = JRequest::getWord('layout', 'default');
		if ($layoutName == 'edit') {

		// Load the helper(s)
			$this->loadHelper('image');
			$this->loadHelper('html');
			$this->loadHelper('parameterparser');
			jimport('joomla.html.pane');

			$paym = $model->getPaym();
			$this->assignRef('paym',	$paym);
			$this->assignRef('vmPPaymentList', self::renderInstalledPaymentPlugins($paym->payment_jplugin_id));
//			$this->assignRef('PaymentTypeList',self::renderPaymentRadioList($paym->payment_type));

//			$this->assignRef('creditCardList',self::renderCreditCardRadioList($paym->payment_creditcards));
//			echo 'humpf <pre>'.print_r($paym).'</pre>' ;
			$this->assignRef('creditCardList',ShopFunctions::renderCreditCardList($paym->payment_creditcards,true));
			$this->assignRef('shopperGroupList', ShopFunctions::renderShopperGroupList($paym->virtuemart_shoppergroup_ids));

			$vendorList= ShopFunctions::renderVendorList($paym->virtuemart_vendor_id);
			$this->assignRef('vendorList', $vendorList);

			ShopFunctions::addStandardEditViewCommands();
		} else {

			$payms = $model->getPayms();
			$this->assignRef('payms',	$payms);

			ShopFunctions::addStandardDefaultViewCommands();
			$lists = ShopFunctions::addStandardDefaultViewLists($model);

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

		$list = array(
		'0' => array('payment_type' => 'C', 'payment_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_CREDIT')),
		'1' => array('payment_type' => 'Y', 'payment_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_USE_PP')),
		'2' => array('payment_type' => 'B', 'payment_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_BANK_DEBIT')),
		'3' => array('payment_type' => 'N', 'payment_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_AO')),
		'4' => array('payment_type' => 'P', 'payment_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_FORMBASED'))
		);

		$listHTML = JHTML::_('Select.genericlist', $list, 'payment_type', '', 'payment_type', 'payment_type_name', $selected );
		return $listHTML;
	}

	function renderPaymentRadioList($selected){

		$list = array(
		'0' => array('payment_type' => 'C', 'payment_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_CREDIT')),
		'1' => array('payment_type' => 'Y', 'payment_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_USE_PP')),
		'2' => array('payment_type' => 'B', 'payment_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_BANK_DEBIT')),
		'3' => array('payment_type' => 'N', 'payment_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_AO')),
		'4' => array('payment_type' => 'P', 'payment_type_name' => JText::_('COM_VIRTUEMART_PAYMENT_FORM_FORMBASED'))
		);

		$listHTML='';
		foreach($list as $item){
			if($item['payment_type']==$selected) $checked='checked="checked"'; else $checked='';
			if($item['payment_type']=='Y' || $item['payment_type']=='C') $id = 'pam_type_CC_on'; else $id='pam_type_CC_off';
			$listHTML .= '<input id="'.$id.'" type="radio" name="payment_type" value="'.$item['payment_type'].'" '.$checked.'>'.$item['payment_type_name'].' <br />';
		}

		return $listHTML;
	}

	function renderInstalledPaymentPlugins($selected){

		if ( VmConfig::isJ15()) {
			$table = '#__plugins';
			$ext_id = 'id';
			$enable = 'published';
		} else {
			$table = '#__extensions';
			$ext_id = 'extension_id';
			$enable = 'enabled';
		}

		$db = JFactory::getDBO();
		//Todo speed optimize that, on the other hand this function is NOT often used and then only by the vendors
//		$q = 'SELECT * FROM #__plugins as pl JOIN `#__virtuemart_payment_method` AS pm ON `pl`.`id`=`pm`.`payment_jplugin_id` WHERE `folder` = "vmpayment" AND `published`="1" ';
//		$q = 'SELECT * FROM #__plugins as pl,#__virtuemart_payment_method as pm  WHERE `folder` = "vmpayment" AND `published`="1" AND pl.id=pm.payment_jplugin_id';
		$q = 'SELECT * FROM `'.$table.'` WHERE `folder` = "vmpayment" AND `'.$enable.'`="1" ';
		$db->setQuery($q);
		$result = $db->loadAssocList($ext_id);
		if(empty($result)){
			$app = JFactory::getApplication();
			$app -> enqueueMessage(JText::_('COM_VIRTUEMART_NO_PAYMENT_PLUGINS_INSTALLED'));
		}
		$listHTML='<select id="payment_jplugin_id" name="payment_jplugin_id">';

		foreach($result as $paym){
			$params = new JParameter($paym['params']);
			if($paym[$ext_id]==$selected) $checked='selected="selected"'; else $checked='';
			// Get plugin info
			$pType = $params->getValue('pType');
			if($pType=='Y' || $pType=='C') $id = 'pam_type_CC_on'; else $id='pam_type_CC_off';
			$listHTML .= '<option id="'.$id.'" '.$checked.' value="'.$paym[$ext_id].'">'.$paym['name'].'</option>';

		}
		$listHTML .= '</select>';

		return $listHTML;
	}

}
// pure php not tag

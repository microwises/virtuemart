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

class VirtuemartViewCalc extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('shopFunctions');

		$model = $this->getModel('calc');
		$this->loadHelper('permissions');
		$this->assignRef('perms', Permissions::getInstance());

		//@todo should be depended by loggedVendor
		$vendorId=1;
		$this->assignRef('vendorId', $vendorId);

		$db = JFactory::getDBO();
		$config = JFactory::getConfig();
		$tzoffset = $config->getValue('config.offset');
		$this->assignRef('tzoffset',	$tzoffset);

		$dateformat = VmConfig::get('dateformat');
		$this->assignRef('dateformat',	$dateformat);

		$viewName=ShopFunctions::SetViewTitle('vm_countries_48');
		$this->assignRef('viewName',$viewName);

		$layoutName = JRequest::getVar('layout', 'default');
		if ($layoutName == 'edit') {

			$calc = $model->getCalc();
			$this->assignRef('calc',	$calc);

			$isNew = ($calc->virtuemart_calc_id < 1);
			if ($isNew) {

				$db = JFactory::getDBO();
				//get default currency of the vendor, if not set get default of the shop
				$q = 'SELECT `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id` = "'.$vendorId.'"';
				$db->setQuery($q);
				$currency= $db->loadResult();
				if(empty($currency)){
					$q = 'SELECT `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id` = "1" ';
					$db->setQuery($q);
					$currency= $db->loadResult();
					$calc->calc_currency = $currency;
				} else {
					$calc->calc_currency = $currency;
				}

				$usermodel = $this->getModel('user', 'VirtuemartModel');
				$usermodel->setCurrent();
				$userDetails = $usermodel->getUser();
				if(empty($userDetails->virtuemart_vendor_id)){
					JError::raiseError(403,'Forbidden for non vendors');
				}
				if(empty($calc->virtuemart_vendor_id))$calc->virtuemart_vendor_id = $userDetails->virtuemart_vendor_id;
			}

			$this->assignRef('entryPointsList',self::renderEntryPointsList($calc->calc_kind));
			$this->assignRef('mathOpList',self::renderMathOpList($calc->calc_value_mathop));


			/* Get the category tree */
			$categoryTree= null;
			if (isset($calc->calc_categories)){
				$calc_categories = $calc->calc_categories;
				$categoryTree = ShopFunctions::categoryListTree($calc_categories);
			}else{
				 $categoryTree = ShopFunctions::categoryListTree();
			}
			$this->assignRef('categoryTree', $categoryTree);


			$currencyModel = $this->getModel('currency');
			$_currencies = $currencyModel->getCurrencies();
			$this->assignRef('currencies', $_currencies);

			/* Get the shoppergroup tree */
			$shopperGroupList= ShopFunctions::renderShopperGroupList($calc->virtuemart_shoppergroup_ids,True);
			$this->assignRef('shopperGroupList', $shopperGroupList);

			$countriesList = ShopFunctions::renderCountryList($calc->calc_countries,True);
			$this->assignRef('countriesList', $countriesList);

			$statesList = ShopFunctions::renderStateList($calc->virtuemart_state_ids, $calc->calc_countries, 'virtuemart_country_id',True);
			$this->assignRef('statesList', $statesList);

			//Todo forbid to see this list, when not the admin or mainvendor is looking on it
			$vendorList= ShopFunctions::renderVendorList($calc->virtuemart_vendor_id,True);
			$this->assignRef('vendorList', $vendorList);

			ShopFunctions::addStandardEditViewCommands();

        }
        else {
			JToolBarHelper::custom('toggle.calc_shopper_published.0', 'unpublish', 'no', JText::_('COM_VIRTUEMART_CALC_SHOPPER_PUBLISH_TOGGLE_OFF'), true);
			JToolBarHelper::custom('toggle.calc_shopper_published.1', 'publish', 'yes', JText::_('COM_VIRTUEMART_CALC_SHOPPER_PUBLISH_TOGGLE_ON'), true);
			JToolBarHelper::custom('toggle.calc_vendor_published.0', 'unpublish', 'no', JText::_('COM_VIRTUEMART_CALC_VENDOR_PUBLISH_TOGGLE_OFF'), true);
			JToolBarHelper::custom('toggle.calc_vendor_published.1', 'publish', 'yes', JText::_('COM_VIRTUEMART_CALC_VENDOR_PUBLISH_TOGGLE_ON'), true);

			$calcs = $model->getCalcs();
			$this->assignRef('calcs',	$calcs);

			ShopFunctions::addStandardDefaultViewCommands();
			ShopFunctions::addStandardDefaultViewLists($model);
			

			// $pagination = $model->getPagination();
			// $this->assignRef('pagination',	$pagination);



		}

		parent::display($tpl);
	}


	/**
	 * Builds a list to choose the Entrypoints
	 * When you want to add extra Entrypoints, look in helpers/calculationh.php for mor information
	 *
	 *
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param 	$selected 	the selected values, may be single data or array
	 * @return 	$list 		list of the Entrypoints
	 */

	function renderEntryPointsList($selected){

		//Entrypoints array
		$this->loadHelper('modelfunctions');
		$selected = modelfunctions::prepareTreeSelection($selected);
		//MathOp array
		$entryPoints = array(
		'0' => array('calc_kind' => 'Tax', 'calc_kind_name' => JText::_('COM_VIRTUEMART_CALC_EPOINT_TAX')),
		'1' => array('calc_kind' => 'DBTax', 'calc_kind_name' => JText::_('COM_VIRTUEMART_CALC_EPOINT_DBTAX')),
		'2' => array('calc_kind' => 'DATax', 'calc_kind_name' => JText::_('COM_VIRTUEMART_CALC_EPOINT_DATAX')),
		'3' => array('calc_kind' => 'TaxBill', 'calc_kind_name' => JText::_('COM_VIRTUEMART_CALC_EPOINT_TAXBILL')),
		'4' => array('calc_kind' => 'DBTaxBill', 'calc_kind_name' => JText::_('COM_VIRTUEMART_CALC_EPOINT_DBTAXBILL')),
		'5' => array('calc_kind' => 'DATaxBill', 'calc_kind_name' => JText::_('COM_VIRTUEMART_CALC_EPOINT_DATAXBILL')),

		);

		$listHTML = JHTML::_('Select.genericlist', $entryPoints, 'calc_kind', '', 'calc_kind', 'calc_kind_name', $selected );
		return $listHTML;

	}

	/**
	 * Builds a list to choose the mathematical operations
	 * When you want to add extra operations, look in helpers/calculationh.php for more information
	 *
	 * @copyright 	Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author 		Max Milbers
	 * @param 	$selected 	the selected values, may be single data or array
	 * @return 	$list 		list of the Entrypoints
	 */

	function renderMathOpList($selected){
		$this->loadHelper('modelfunctions');
		$selected = modelfunctions::prepareTreeSelection($selected);
		//MathOp array
		$mathOps = array(
		'0' => array('calc_value_mathop' => '+', 'calc_value_mathop_name' => '+'),
		'1' => array('calc_value_mathop' => '-', 'calc_value_mathop_name' => '-'),
		'2' => array('calc_value_mathop' => '+%', 'calc_value_mathop_name' => '+%'),
		'3' => array('calc_value_mathop' => '-%', 'calc_value_mathop_name' => '-%')
		);

		$listHTML = JHTML::_('Select.genericlist', $mathOps, 'calc_value_mathop', '', 'calc_value_mathop', 'calc_value_mathop_name', $selected );
		return $listHTML;
	}



}
// pure php no closing tag
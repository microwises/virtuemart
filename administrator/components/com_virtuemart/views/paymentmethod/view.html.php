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
* @version $Id: view.html.php 2279 2010-01-31 15:15:38Z Milbo $
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

		$model = $this->getModel('paymentmethod');

		//@todo should be depended by loggedVendor
		$vendorId=1;
		$this->assignRef('vendorId', $vendorId);
		
		
		$layoutName = JRequest::getVar('layout', 'default');
		if ($layoutName == 'edit') {
			
			$calc = $model->getCalc();
			$this->assignRef('calc',	$calc);
			
			$isNew = ($calc->calc_id < 1);
			if ($isNew) {
				JToolBarHelper::title(  JText::_('VM_CALC_LIST_ADD' ).': <small><small>[ New ]</small></small>', 'vm_countries_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
			}
			else {
				JToolBarHelper::title( JText::_('VM_CALC_LIST_ADD' ).': <small><small>[ Edit ]</small></small>', 'vm_countries_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
			}

			$this->assignRef('entryPointsList',self::renderEntryPointsList($calc->calc_kind));
			$this->assignRef('mathOpList',self::renderMathOpList($calc->calc_value_mathop));
			
			
			$this->loadHelper('shopFunctions');

			/* Get the category tree */
			$categoryTree= null;
			if (isset($calc->calc_categories)){
				$calc_categories = $calc->calc_categories;
				$categoryTree = ShopFunctions::categoryListTree($calc_categories);
			}else{
				 $categoryTree = ShopFunctions::categoryListTree();
			}
			$this->assignRef('categoryTree', $categoryTree);

			
			/* Get the shoppergroup tree */
			$shopperGroupList= ShopFunctions::renderShopperGroupList($calc->calc_shopper_groups,True);
			$this->assignRef('shopperGroupList', $shopperGroupList);

//			$countriesList = ShopFunctions::renderCountryList($calc->calc_countries,True);
//			$this->assignRef('countriesList', $countriesList);
//			
//			$statesList = ShopFunctions::renderStateList($calc->calc_states, $calc->calc_countries, 'country_id',True);
//			$this->assignRef('statesList', $statesList);			

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
	 * Prepares the selection for the TreeLists
	 * 
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param $value the selected values, may be single data or array
	 * @return $values prepared array to work with JHTML::_('Select.genericlist')
	 */
	function prepareTreeSelection($values){
		if (!isset($values)){
			return;
		}
		if (!is_array($values)) $values = array($values);
		foreach ($values as $value) {
			$values[$value]  = 1;
		}
		return $values;
	}
	
	/**
	 * Builds a list to choose the Entrypoints
	 * When you want to add extra Entrypoints, look in helpers/calculationh.php for mor information
	 * 
	 * This does not use the normal joomla function as it needs too much data that is not necessary,
	 * Maybe this will be moved to the helper
	 * 
	 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author Max Milbers
	 * @param 	$selected 	the selected values, may be single data or array
	 * @return 	$list 		list of the Entrypoints  
	 */
	 
	function renderEntryPointsList($selected){

		//Entrypoints array

		$selected = self::prepareTreeSelection($selected);
		//MathOp array
		$entryPoints = array(
		'0' => array('calc_kind' => 'Tax', 'calc_kind_name' => JText::_('VM_CALC_EPOINT_TAX')),
		'1' => array('calc_kind' => 'DBTax', 'calc_kind_name' => JText::_('VM_CALC_EPOINT_DBTAX')),
		'2' => array('calc_kind' => 'DATax', 'calc_kind_name' => JText::_('VM_CALC_EPOINT_DATAX')),
		'3' => array('calc_kind' => 'TaxBill', 'calc_kind_name' => JText::_('VM_CALC_EPOINT_TAXBILL')),
		'4' => array('calc_kind' => 'DBTaxBill', 'calc_kind_name' => JText::_('VM_CALC_EPOINT_DBTAXBILL')),
		'5' => array('calc_kind' => 'DATaxBill', 'calc_kind_name' => JText::_('VM_CALC_EPOINT_DATAXBILL')),
		
		);

		$listHTML = JHTML::_('Select.genericlist', $entryPoints, 'calc_kind', '', 'calc_kind', 'calc_kind_name', $selected );
		return $listHTML;

	}

	/**
	 * Builds a list to choose the mathematical operations
	 * When you want to add extra operations, look in helpers/calculationh.php for more information
	 * 
	 * This does not use the normal joomla function as it needs too much data that is not necessary,
	 * Maybe this will be moved to the helper
	 * 
	 * @copyright 	Copyright (c) 2009 VirtueMart Team. All rights reserved.
	 * @author 		Max Milbers
	 * @param 	$selected 	the selected values, may be single data or array
	 * @return 	$list 		list of the Entrypoints  
	 */
	 
	function renderMathOpList($selected){
		$selected = self::prepareTreeSelection($selected);
		//MathOp array
		$mathOps = array(
		'0' => array('calc_value_mathop' => '+', 'calc_value_mathop_name' => '+'),
		'1' => array('calc_value_mathop' => '-', 'calc_value_mathop_name' => '-'),
		'2' => array('calc_value_mathop' => '+%', 'calc_value_mathop_name' => '%+'),
		'3' => array('calc_value_mathop' => '-%', 'calc_value_mathop_name' => '%-')
		);

		$listHTML = JHTML::_('Select.genericlist', $mathOps, 'calc_value_mathop', '', 'calc_value_mathop', 'calc_value_mathop_name', $selected );
		return $listHTML;
	}


	
}
?>
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

		$model = $this->getModel('calc');
        $calc = $model->getCalc();
        
        $layoutName = JRequest::getVar('layout', 'default');
        $isNew = ($calc->calc_id < 1);
		
		if ($layoutName == 'edit') {
			$this->assignRef('calc',	$calc);
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
			$category_tree= null;
			if (isset($calc->calc_categories)){
				$calc_categories = self::prepareTreeSelection($calc->calc_categories);
				$category_tree = ShopFunctions::categoryListTree(0, 0, 0, $calc_categories);
			}else{
				 $category_tree = ShopFunctions::categoryListTree();
			}
			$this->assignRef('category_tree', $category_tree);

			
			/* Get the shoppergroup tree */
			$shopper_tree= null;
			if (isset($calc->calc_shopper_groups)){
				$calc_shopper_groups = self::prepareTreeSelection($calc->calc_shopper_groups);
				$shopper_tree = ShopFunctions::renderShopperGroupList($calc_shopper_groups,1);
			}else{
				$shopper_tree = ShopFunctions::shopperListTree();
			}
			$this->assignRef('shopper_tree', $shopper_tree);


			/* Get the country tree */
			$country_tree= null;
			if (isset($calc->calc_countries)){
				$calc_countries = self::prepareTreeSelection($calc->calc_countries);
				$country_tree = ShopFunctions::renderCountryList($calc_countries,1);
			}else{
				$country_tree = ShopFunctions::renderCountryList();
			}
			$this->assignRef('country_tree', $country_tree);


			/* Get the states tree */
			$states_tree= null;
			if (isset($calc->calc_states)){
				$calc_states = self::prepareTreeSelection($calc->calc_states);
				$states_tree = ShopFunctions::renderStateList($calc_states,1);
			}else{
				$states_tree = ShopFunctions::renderStateList();
			}
			$this->assignRef('states_tree', $states_tree);

        }
        else {
			JToolBarHelper::title( JText::_( 'VM_CALC_LIST_LBL' ), 'vm_countries_48' );
			JToolBarHelper::publishList();
			JToolBarHelper::unpublishList();
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();	
			
			$pagination = $model->getPagination();			
			$this->assignRef('pagination',	$pagination);	
			
			$calcs = $model->getCalcs();
			$this->assignRef('calcs',	$calcs);	
			
		}
		require_once(CLASSPATH. 'ps_perm.php' );
		$perm = new ps_perm();
		$perm->check( 'admin' );
		$this->assignRef('perm',	$perm);
		$this->assignRef('model',	$model);

		//@todo should be depended by loggedVendor
		$vendorId=1;
		$this->assignRef('vendorId', $vendorId);

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
		'2' => array('calc_value_mathop' => '%+', 'calc_value_mathop_name' => '%+'),
		'3' => array('calc_value_mathop' => '%-', 'calc_value_mathop_name' => '%-')
		);

		$listHTML = JHTML::_('Select.genericlist', $mathOps, 'calc_value_mathop', '', 'calc_value_mathop', 'calc_value_mathop_name', $selected );
		return $listHTML;
	}


	
}
?>
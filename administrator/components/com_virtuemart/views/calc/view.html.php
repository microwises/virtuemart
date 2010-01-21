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

jimport( 'joomla.application.component.view');

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

			$this->loadHelper('shopFunctions');

			/* Load some common models */
			$categoryModel = $this->getModel('category');
			$category_tree= null;
			if (!is_array($calc->calc_categories)) $calc->calc_categories = array($calc->calc_categories);
			$categories = $calc->calc_categories;
			foreach ($categories as $value) {
				$categories[$value]  = 1;
			}
			/* Get the category tree */
			if (isset($calc->calc_categories)) $category_tree = ShopFunctions::categoryListTree('', 0, 0, $categories);
			else $category_tree = ShopFunctions::categoryListTree();
			$this->assignRef('category_tree', $category_tree);
			
			$shopper_tree= null;
			if (!is_array($calc->calc_shopper_groups)) $calc->calc_shopper_groups = array($calc->calc_shopper_groups);
			$calc_shopper_groups = $calc->calc_shopper_groups;
			foreach ($calc_shopper_groups as $value) {
				$calc_shopper_groups[$value]  = 1;
			}
			/* Get the category tree */
			if (isset($calc->calc_shopper_groups)) $shopper_tree = ShopFunctions::renderShopperGroupList($calc_shopper_groups,1);
			else $shopper_tree = ShopFunctions::shopperListTree();
			$this->assignRef('shopper_tree', $shopper_tree);
			
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
//		$this->assignRef('calc_categories', $calc->calc_categories);
	
		
		parent::display($tpl);
	}
	
}
?>
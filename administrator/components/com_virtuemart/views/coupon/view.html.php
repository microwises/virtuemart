<?php
/**
 * Coupon View
 *
 * @package	VirtueMart
 * @subpackage Coupon
 * @author RickG
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

jimport( 'joomla.application.component.view');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'adminMenu.php');

/**
 * HTML View class for maintaining the list of Coupons
 *
 * @package	VirtueMart
 * @subpackage Coupon
 * @author RickG 
 */
class VirtuemartViewCoupon extends JView {
	
	function display($tpl = null) {	
		$model = $this->getModel();
		
        $coupon = $model->getCoupon();
        
        $layoutName = JRequest::getVar('layout', 'default');
        $isNew = ($coupon->coupon_id < 1);
		
		if ($layoutName == 'edit') {
			if ($isNew) {
				JToolBarHelper::title(  JText::_('VM_COUPON_NEW_HEADER' ).': <small><small>[ New ]</small></small>', 'vm_coupon_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
			}
			else {
				JToolBarHelper::title( JText::_('VM_COUPON_EDIT_HEADER' ).': <small><small>[ Edit ]</small></small>', 'vm_coupon_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
			}
			$this->assignRef('coupon',	$coupon);
        }
        else {
			JToolBarHelper::title( JText::_('VM_COUPON_LIST'), 'vm_coupon_48');
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();	
			
			$pagination = $model->getPagination();			
			$this->assignRef('pagination',	$pagination);	
			
			$coupons = $model->getCoupons();
			$this->assignRef('coupons',	$coupons);	
		}			
		
		parent::display($tpl);
	}
	
}
?>

<?php
/**
 * Shopper group View
 *
 * @package	VirtueMart
 * @subpackage ShopperGroup
 * @author Markus Öhler
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the list of shopper groups
 *
 * @package	VirtueMart
 * @subpackage ShopperGroup
 * @author Markus Öhler
 */
class VirtuemartViewShopperGroup extends JView {

  function display($tpl = null) {
		// Load the helper(s)
		$this->loadHelper('adminMenu');

		$model = $this->getModel();
		//$vendorModel = $this->getModel('Vendor');

		$shoppergroup = $model->getShopperGroup();

		$layoutName = JRequest::getVar('layout', 'default');
		$isNew = ($shoppergroup->shoppergroup_id < 1);

		if ($layoutName == 'edit') {
		  if ($isNew) {
				JToolBarHelper::title(  JText::_('VM_SHOPPER_GROUP_FORM_LBL' ).': <small><small>[ New ]</small></small>', 'vm_shop_users_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel();
		  } else {
				JToolBarHelper::title( JText::_('VM_SHOPPER_GROUP_FORM_LBL' ).': <small><small>[ Edit ]</small></small>', 'vm_shop_users_48');
				JToolBarHelper::divider();
				JToolBarHelper::save();
				JToolBarHelper::cancel('cancel', 'Close');
		  }
		  $this->assignRef('shoppergroup',	$shoppergroup);
		  //$this->assignRef('vendors',	$zoneModel->getShippingZoneSelectList());
		}	else {
			JToolBarHelper::title( JText::_( 'VM_SHOPPER_GROUP_LIST_LBL ' ), 'vm_shop_users_48' );
			JToolBarHelper::addNewX();
			JToolBarHelper::editListX();
			JToolBarHelper::deleteList('', 'remove', 'Delete');

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$shoppergroups = $model->getShopperGroups();
			//$vendors = $vendorModel->getVendors());
			$this->assignRef('shoppergroups',	$shoppergroups);
			//$this->assignRef('vendors', $vendors);
		}
		parent::display($tpl);
  }

} ?>

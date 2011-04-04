<?php
/**
*
* Shopper group View
*
* @package	VirtueMart
* @subpackage ShopperGroup
* @author Markus �hler
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
jimport('joomla.application.component.view');

/**
 * HTML View class for maintaining the list of shopper groups
 *
 * @package	VirtueMart
 * @subpackage ShopperGroup
 * @author Markus �hler
 */
class VirtuemartViewShopperGroup extends JView {

  function display($tpl = null) {
		// Load the helper(s)
		$this->loadHelper('adminMenu');

//		$this->assignRef('perms', Permissions::getInstance());

		$model = $this->getModel();
		//$vendorModel = $this->getModel('Vendor');
		$layoutName = JRequest::getVar('layout', 'default');

		if ($layoutName == 'edit') {
			$shoppergroup = $model->getShopperGroup();
			$isNew = ($shoppergroup->shopper_group_id < 1);
			if ($isNew) {
				JToolBarHelper::title(  JText::_('COM_VIRTUEMART_SHOPPER_GROUP_FORM_LBL' ).': <small><small>[ New ]</small></small>', 'vm_shop_users_48');
			} else {
				JToolBarHelper::title( JText::_('COM_VIRTUEMART_SHOPPER_GROUP_FORM_LBL' ).': <small><small>[ Edit ]</small></small>', 'vm_shop_users_48');
			}

			$this->loadHelper('shopfunctions');
			$vendors = ShopFunctions::renderVendorList($shoppergroup->vendor_id);
			$this->assignRef('vendorList',	$vendors);

			JToolBarHelper::divider();
			JToolBarHelper::apply();
			JToolBarHelper::save();
			JToolBarHelper::cancel();
			$this->assignRef('shoppergroup',	$shoppergroup);

		} else {
			JToolBarHelper::title( JText::_( 'COM_VIRTUEMART_SHOPPER_GROUP_LIST_LBL' ), 'vm_shop_users_48' );
			JToolBarHelper::addNewX();
			JToolBarHelper::editListX();
			JToolBarHelper::deleteList('', 'remove', 'Delete');

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$shoppergroups = $model->getShopperGroups(false, true);

			$this->assignRef('shoppergroups',	$shoppergroups);

			$this->loadHelper('permissions');
			$this->assignRef('showVendors',Permissions::getInstance()->check('admin,storeadmin'));

		}
		parent::display($tpl);
  }

} // pure php no closing tag

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
		$this->loadHelper('adminui');
		$this->loadHelper('shopFunctions');
		if (!class_exists('VmHTML')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'html.php');

		//		$this->assignRef('perms', Permissions::getInstance());

		$model = $this->getModel();

		$layoutName = JRequest::getWord('layout', 'default');
		if ($layoutName == 'edit') {
			$shoppergroup = $model->getShopperGroup();
			$viewName=ShopFunctions::SetViewTitle('SHOPPERGROUP',$shoppergroup->shopper_group_name);
			$this->assignRef('viewName',$viewName);

			$vendors = ShopFunctions::renderVendorList($shoppergroup->virtuemart_vendor_id);
			$this->assignRef('vendorList',	$vendors);

			$this->assignRef('shoppergroup',	$shoppergroup);

			ShopFunctions::addStandardEditViewCommands();


		} else {
			$viewName=ShopFunctions::SetViewTitle();
			$this->assignRef('viewName',$viewName);
			JToolBarHelper::makeDefault();
			$shoppergroups = $model->getShopperGroups(false, true);

			$this->assignRef('shoppergroups',	$shoppergroups);

			$this->loadHelper('permissions');
			$this->assignRef('showVendors',Permissions::getInstance()->check('admin,storeadmin'));

			ShopFunctions::addStandardDefaultViewCommands();
			$lists = ShopFunctions::addStandardDefaultViewLists($model);
			$this->assignRef('lists', $lists);

		}
		parent::display($tpl);
	}

} // pure php no closing tag

<?php
/**
*
* Shipping Carrier View
*
* @package	VirtueMart
* @subpackage ShippingCarrier
* @author RickG
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
 * HTML View class for maintaining the list of shipping carriers
 *
 * @package	VirtueMart
 * @subpackage ShippingCarrier
 * @author RickG
 */
class VirtuemartViewShippingCarrier extends JView {

	function display($tpl = null) {

		// Load the helper(s)
		$this->loadHelper('adminMenu');
		$this->loadHelper('shopFunctions');

		$model = $this->getModel();
		$shippingCarrier = $model->getShippingCarrier();

		$layoutName = JRequest::getVar('layout', 'default');
		$viewName=ShopFunctions::SetViewTitle('vm_ups_48');
		$this->assignRef('viewName',$viewName);

		$layoutName = JRequest::getVar('layout', 'default');
		if ($layoutName == 'edit') {

			$vendorList= ShopFunctions::renderVendorList($shippingCarrier->virtuemart_vendor_id);
			$this->assignRef('vendorList', $vendorList);
			$this->assignRef('pluginList', self::renderInstalledShipperPlugins($shippingCarrier->shipping_carrier_jplugin_id));
			$this->assignRef('carrier',	$shippingCarrier);

			ShopFunctions::addStandardEditViewCommands();

		} else {


			$shippingCarriers = $model->getShippingCarriers();
			$this->assignRef('shippingCarriers', $shippingCarriers);

			ShopFunctions::addStandardDefaultViewCommands();
			$lists = ShopFunctions::addStandardDefaultViewLists($model);
			$this->assignRef('lists', $lists);

		}

		parent::display($tpl);
	}

	function renderInstalledShipperPlugins($selected)
	{
		$db = JFactory::getDBO();

		if (VmConfig::isJ15()) {
			$table = '#__plugins';
			$enable = 'published';
			$ext_id = 'id';
		}
		else {
			$table = '#__extensions';
			$enable = 'enabled';
			$ext_id = 'extension_id';
		}
		$q = 'SELECT * FROM `'.$table.'` WHERE `folder` = "vmshipper" AND `'.$enable.'`="1" ';
		$db->setQuery($q);
		$result = $db->loadAssocList($ext_id);

		return JHtml::_('select.genericlist', $result, 'shipping_carrier_jplugin_id', null, $ext_id, 'name', $selected);
	}

}
// pure php no closing tag

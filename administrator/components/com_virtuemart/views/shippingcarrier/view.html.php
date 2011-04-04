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

		$model = $this->getModel();
		$shippingCarrier = $model->getShippingCarrier();

		$layoutName = JRequest::getVar('layout', 'default');
		$isNew = ($shippingCarrier->shipping_carrier_id < 1);

		if ($layoutName == 'edit') {
			if ($isNew) {
				JToolBarHelper::title(  JText::_('COM_VIRTUEMART_CARRIER_LIST_LBL' ).': <small><small>[ New ]</small></small>', 'vm_ups_48');
			} else {
				JToolBarHelper::title( JText::_('COM_VIRTUEMART_CARRIER_FORM_LBL' ).': <small><small>[ Edit ]</small></small>', 'vm_ups_48');
			}
			JToolBarHelper::divider();
			JToolBarHelper::apply();
			JToolBarHelper::save();
			JToolBarHelper::cancel();
			
			$this->loadHelper('shopFunctions');
			$vendorList= ShopFunctions::renderVendorList($shippingCarrier->shipping_carrier_vendor_id);
			$this->assignRef('vendorList', $vendorList);
			$this->assignRef('pluginList', self::renderInstalledShipperPlugins($shippingCarrier->shipping_carrier_jplugin_id));
			$this->assignRef('carrier',	$shippingCarrier);
		} else {
			JToolBarHelper::title( JText::_( 'COM_VIRTUEMART_CARRIER_LIST_LBL' ), 'vm_ups_48' );
			JToolBarHelper::deleteList('', 'remove', 'Delete');
			JToolBarHelper::editListX();
			JToolBarHelper::addNewX();

			$pagination = $model->getPagination();
			$this->assignRef('pagination',	$pagination);

			$shippingCarriers = $model->getShippingCarriers();
			$this->assignRef('shippingCarriers', $shippingCarriers);
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

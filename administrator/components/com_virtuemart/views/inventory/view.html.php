<?php
/**
 *
 * Description
 *
 * @package	VirtueMart
 * @subpackage
 * @author
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
if(!class_exists('VmView'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmview.php');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewInventory extends VmView {

	function display($tpl = null) {


		//Load helpers

		$this->loadHelper('currencydisplay');

		$this->loadHelper('html');

		// Get the data
		$model = VmModel::getModel('product');

		// Create filter
		$this->addStandardDefaultViewLists($model);

		$inventorylist = $model->getProductListing(false,false);

		$pagination = $model->getPagination();
		$this->assignRef('pagination', $pagination);

		// Apply currency
		$currencydisplay = CurrencyDisplay::getInstance();;

		foreach ($inventorylist as $virtuemart_product_id => $product) {
			//TODO oculd be interesting to show the price for each product, and all stored ones $product->product_in_stock
			$product->product_instock_value = $currencydisplay->priceDisplay($product->product_price,'',$product->product_in_stock,false);
			$product->product_price_display = $currencydisplay->priceDisplay($product->product_price,'',1,false);
			$product->weigth_unit_display= ShopFunctions::renderWeightUnit($product->product_weight_uom);
		}
		$this->assignRef('inventorylist', $inventorylist);


		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('COM_VIRTUEMART_SELECT'));
		$options[] = JHTML::_('select.option', 0, JText::_('COM_VIRTUEMART_LIST_ALL_PRODUCTS'));
		$options[] = JHTML::_('select.option', 1, JText::_('COM_VIRTUEMART_HIDE_OUT_OF_STOCK'));
		$this->lists['stockfilter'] = JHTML::_('select.genericlist', $options, 'stockfilter', 'onChange="document.adminForm.submit(); return false;"', 'value', 'text', JRequest::getVar('stockfilter'));
		$this->lists['filter_product'] = JRequest::getVar('filter_product');
		// $this->assignRef('lists', $lists);

		/* Toolbar */
		$this->SetViewTitle('PRODUCT_INVENTORY');
		JToolBarHelper::publish();
		JToolBarHelper::unpublish();

		parent::display($tpl);
	}

}
// pure php no closing tag

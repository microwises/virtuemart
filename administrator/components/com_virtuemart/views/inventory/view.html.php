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
jimport( 'joomla.application.component.view');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewInventory extends JView {

	function display($tpl = null) {

		$option = JRequest::getVar('option');


		/* Load helpers */
		$this->loadHelper('adminMenu');
		$this->loadHelper('currencydisplay');
		$this->loadHelper('shopFunctions');

		/* Get the data */
		$model = $this->getModel('product');
		$inventorylist = $model->getProductList();

		/* Apply currency */
		$currencydisplay = CurrencyDisplay::getInstance();;

		foreach ($inventorylist as $virtuemart_product_id => $product) {
			$product->product_price_display = $currencydisplay->priceDisplay($product->product_price,'',false);
		}
		$this->assignRef('inventorylist', $inventorylist);

		/* Create filter */
		$lists = ShopFunctions::addStandardDefaultViewLists($model);

		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('COM_VIRTUEMART_SELECT'));
		$options[] = JHTML::_('select.option', 0, JText::_('COM_VIRTUEMART_LIST_ALL_PRODUCTS'));
		$options[] = JHTML::_('select.option', 1, JText::_('COM_VIRTUEMART_HIDE_OUT_OF_STOCK'));
		$lists['stockfilter'] = JHTML::_('select.genericlist', $options, 'stockfilter', 'onChange="document.adminForm.submit(); return false;"', 'value', 'text', JRequest::getVar('stockfilter'));

		$this->assignRef('lists', $lists);

		/* Toolbar */
		JToolBarHelper::title(JText::_('COM_VIRTUEMART_PRODUCT_INVENTORY'), 'vm_inventory_48');
		JToolBarHelper::publish();
		JToolBarHelper::unpublish();

		parent::display($tpl);
	}

}
// pure php no closing tag

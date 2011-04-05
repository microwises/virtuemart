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
class VirtuemartViewProductspecial extends JView {

	function display($tpl = null) {

		$mainframe = Jfactory::getApplication();
		$option = JRequest::getVar('option');
		$lists = array();

		/* Load helpers */
		$this->loadHelper('adminMenu');
		$this->loadHelper('currencydisplay');

		/* Get the data */
		$productlist = $this->get('ProductSpecial');

		/* Apply currency */
		$currencydisplay = CurrencyDisplay::getCurrencyDisplay();;
		foreach ($productlist as $product_id => $product) {
			$product->product_price_display = $currencydisplay->getValue($product->product_price);
		}

		/* Get the pagination */
		$pagination = $this->get('Pagination');
		$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
		$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');

		/* Add filters */
		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('SELECT'));
		$options[] = JHTML::_('select.option', 'all', JText::_('VM_LIST_ALL_PRODUCTS'));
		$options[] = JHTML::_('select.option', 'featured_and_discounted', JText::_('VM_SHOW_FEATURED_AND_DISCOUNTED'));
		$options[] = JHTML::_('select.option', 'featured', JText::_('VM_SHOW_FEATURED'));
		$options[] = JHTML::_('select.option', 'discounted', JText::_('VM_SHOW_DISCOUNTED'));
		$lists['search_type'] = JHTML::_('select.genericlist', $options, 'search_type', 'onChange="document.adminForm.submit(); return false;"', 'value', 'text', JRequest::getVar('search_type'));

		/* Toolbar */
		JToolBarHelper::title(JText::_( 'VM_FEATURED_PRODUCTS_LIST_LBL' ), 'vm_product_48');

		/* Assign the data */
		$this->assignRef('productlist', $productlist);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('lists',	$lists);

		parent::display($tpl);
	}

}
// pure php no closing tag

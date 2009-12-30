<?php
/**
* @package		VirtueMart
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
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
		$currencydisplay = new CurrencyDisplay();
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
?>

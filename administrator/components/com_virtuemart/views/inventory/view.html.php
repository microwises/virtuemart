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
class VirtuemartViewInventory extends JView {
	
	function display($tpl = null) {
		$mainframe = Jfactory::getApplication();
		$option = JRequest::getVar('option');
		$lists = array();
		/* Get the task */
		$task = JRequest::getVar('task');
		
		switch ($task) {
			case 'publish':
			case 'unpublish':
				$product_model = $this->getModel('product');
				$product_model->getPublish();
				break;
		}
		
		/* Load helpers */
		$this->loadHelper('adminMenu');
		$this->loadHelper('currencydisplay');
		
		/* Get the data */
		$inventorylist = $this->get('Inventory');
		
		/* Apply currency */
		$currencydisplay = new CurrencyDisplay();
		foreach ($inventorylist as $product_id => $product) {
			$product->product_price_display = $currencydisplay->getValue($product->product_price);
		}
		
		/* Get the pagination */
		$pagination = $this->get('Pagination');
		$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
		$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');
		
		/* Create filter */
		$options = array();
		$options[] = JHTML::_('select.option', '', JText::_('SELECT'));
		$options[] = JHTML::_('select.option', 0, JText::_('VM_LIST_ALL_PRODUCTS'));
		$options[] = JHTML::_('select.option', 1, JText::_('VM_HIDE_OUT_OF_STOCK'));
		$lists['stockfilter'] = JHTML::_('select.genericlist', $options, 'stockfilter', 'onChange="document.adminForm.submit(); return false;"', 'value', 'text', JRequest::getVar('stockfilter'));
		
		/* Toolbar */
		JToolBarHelper::title(JText::_( 'VM_PRODUCT_INVENTORY_LBL' ), 'vm_inventory_48');
		JToolBarHelper::publish();
		JToolBarHelper::unpublish();
		
		/* Assign the data */
		$this->assignRef('inventorylist', $inventorylist);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('lists',	$lists);
		
		parent::display($tpl);
	}
	
}
?>

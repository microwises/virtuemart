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
class VirtuemartViewProducttypes extends JView {
	
	function display($tpl = null) {
		$mainframe = Jfactory::getApplication();
		$option = JRequest::getVar('option');
		$lists = array();
		/* Load helpers */
		$this->loadHelper('adminMenu');
		
		/* Get the task */
		$task = JRequest::getVar('task');
		
		switch ($task) {
			case 'add':
			case 'edit':
				/* Get the data */
				$producttype = $this->get('ProductType');
				
				/* Load the editor */
				$editor = JFactory::getEditor();
				
				/* Toolbar */
				JToolBarHelper::title(JText::_( 'VM_PRODUCT_TYPE_FORM_LBL' ), 'vm_product_types_48');
				JToolBarHelper::save();
				JToolBarHelper::cancel();
				
				/* Assign the data */
				$this->assignRef('producttype', $producttype);
				$this->assignRef('editor', $editor);
				$this->assignRef('lists', $lists);
				break;
			default:
				/* Get the data */
				$producttypeslist = $this->get('ProductTypes');
				
				/* Get some statistics */
				$model = $this->getModel();
				foreach ($producttypeslist as $key => $producttype) {
					$producttypeslist[$key]->productcount = $model->getProductCount($producttype->product_type_id);
					$producttypeslist[$key]->parametercount = $model->getParameterCount($producttype->product_type_id);
				}
				
				/* Get the pagination */
				$pagination = $this->get('Pagination');
				$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
				$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');
				
				/* Toolbar */
				JToolBarHelper::title(JText::_('VM_PRODUCT_TYPE_LIST_LBL'), 'vm_product_types_48');
				JToolBarHelper::deleteListX();
				JToolBarHelper::editListX();
				JToolBarHelper::addNewX();
				
				/* Assign the data */
				$this->assignRef('producttypeslist', $producttypeslist);
				$this->assignRef('pagination',	$pagination);
				$this->assignRef('lists',	$lists);
				break;
		}
		
		parent::display($tpl);
	}
	
}
?>

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
class VirtuemartViewProducttypeparameters extends JView {
	
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
				$parameter = $this->get('ProductTypeParameter');
				
				if ($task == 'add') $parameter->product_type_id = JRequest::getInt('product_type_id');
				
				/* Load the editor */
				$editor = JFactory::getEditor();
				JHTML::_('behavior.tooltip');
				
				/* Parameter types */
				$options = array();
				$options[] = JHTML::_('select.option', 'I', JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_INTEGER'));
				$options[] = JHTML::_('select.option', 'T', JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TEXT'));
				$options[] = JHTML::_('select.option', 'S', JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_SHORTTEXT'));
				$options[] = JHTML::_('select.option', 'F', JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_FLOAT'));
				$options[] = JHTML::_('select.option', 'C', JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_CHAR'));
				$options[] = JHTML::_('select.option', 'D', JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATETIME'));
				$options[] = JHTML::_('select.option', 'A', JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_DATE'));
				$options[] = JHTML::_('select.option', 'M', JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_TIME'));
				$options[] = JHTML::_('select.option', 'V', JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_MULTIVALUE'));
				$options[] = JHTML::_('select.option', 'B', JText::_('VM_PRODUCT_TYPE_PARAMETER_FORM_TYPE_BREAK'));
				
				$lists['parameter_type'] = JHTML::_('select.genericlist', $options, 'parameter_type', 'class="inputbox"', 'value', 'text', $parameter->parameter_type);
				
				/* Toolbar */
				JToolBarHelper::title(JText::_( 'VM_PRODUCT_TYPE_PARAMETER_FORM_LBL' ), 'vm_product_type_parameters_48');
				JToolBarHelper::save();
				JToolBarHelper::cancel();
				
				/* Assign the data */
				$this->assignRef('parameter', $parameter);
				$this->assignRef('editor', $editor);
				$this->assignRef('lists', $lists);
				break;
			default:
				/* Get the data */
				$producttypeparameterslist = $this->get('ProductTypeParameters');
				
				/* Get the product type */
				$product_type_name = $this->get('ProductTypeName');
				
				/* Get the pagination */
				$pagination = $this->get('Pagination');
				$lists['filter_order'] = $mainframe->getUserStateFromRequest($option.'filter_order', 'filter_order', '', 'cmd');
				$lists['filter_order_Dir'] = $mainframe->getUserStateFromRequest($option.'filter_order_Dir', 'filter_order_Dir', '', 'word');
				
				/* Toolbar */
				JToolBarHelper::title(JText::_('VM_PRODUCT_TYPE_PARAMETER_LIST_LBL').'::'.$product_type_name, 'vm_product_type_parameters_48');
				JToolBarHelper::deleteListX();
				JToolBarHelper::editListX();
				JToolBarHelper::addNewX();
				
				/* Assign the data */
				$this->assignRef('producttypeparameterslist', $producttypeparameterslist);
				$this->assignRef('pagination',	$pagination);
				$this->assignRef('lists',	$lists);
				break;
		}
		
		parent::display($tpl);
	}
	
}
?>

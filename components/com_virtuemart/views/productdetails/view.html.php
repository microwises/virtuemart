<?php
/**
* Product details view
*
* @package VirtueMart
* @author RolandD
*/

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport( 'joomla.application.component.view' );
/**
* Product details
*/
class VirtueMartViewProductdetails extends JView {
	
	function display($tpl = null) {
		$mainframe = JFactory::getApplication();
		$pathway	= $mainframe->getPathway();
		$task = JRequest::getCmd('task');
		
		/* Set the helper path */
		$this->addHelperPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers');
		
		/* Load helpers */
		$this->loadHelper('image');
		
		/* Set the titles */
		$mainframe->setPageTitle(JText::_('VM_PRODUCT_DETAILS'));
		$uri = JURI::getInstance();
		$pathway->addItem(JText::_('PRODUCT_DETAILS'), $uri->toString(array('path', 'query', 'fragment')));
		
		/* Load the product */
		$product = $this->get('product');
		$this->assignRef('product', $product);
		$pathway->addItem($product->product_name);
		
		/* Load the authorizations */
		$auth = JRequest::getVar('auth');
		$this->assignRef('auth', $auth);
		
		/* Check for editing access */
		if (Permissions::check("admin,storeadmin")) {
			$url = JRoute::_('index2.php?option=com_virtuemart&view=productdetails&task=edit&product_id='.$product->product_id);
			$edit_link = JHTML::_('link', $url, JHTML::_('image', 'images/M_images/edit.png', JText::_('VM_PRODUCT_FORM_EDIT_PRODUCT'), array('width' => 16, 'height' => 16, 'border' => 0)));
		}
		else {
			$edit_link = "";
		}
		$this->assignRef('edit_link', $edit_link);
		
		
		/* Display it all */
		parent::display($tpl); 
	}
}

?>
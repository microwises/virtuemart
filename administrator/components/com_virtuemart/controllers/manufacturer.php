<?php
/**
 * Manufacturer controller
 *
 * @package	VirtueMart
 * @subpackage Manufacturer
 * @author vhv_alex 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * Manufacturer Controller
 *
 * @package    VirtueMart
 * @subpackage Manufacturer
 *  
 */
class VirtuemartControllerManufacturer extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
		
		// Register Extra tasks
		$this->registerTask( 'add',  'edit' );			
	    
		$document =& JFactory::getDocument();				
		$viewType	= $document->getType();
		$view =& $this->getView('manufacturer', $viewType);		

		// Push a model into the view					
		$model =& $this->getModel('manufacturer');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}			
		$model1 =& $this->getModel('manufacturerCategory');
		if (!JError::isError($model1)) {
			$view->setModel($model1, false);
		}			
	}
	
	/**
	 * Display the manufacturer view
	 *
	 */
	function display() {			
		parent::display();
	}
	
	
	/**
	 * Handle the edit task
	 *
	 */
	function edit()
	{				
		JRequest::setVar('controller', 'manufacturer');
		JRequest::setVar('view', 'manufacturer');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemenu', 1);		
		
		parent::display();
	}		
	
	
	/**
	 * Handle the cnacel task
	 *
	 */
	function cancel()
	{
		$this->setRedirect('index.php?option=com_virtuemart&view=manufacturer');
	}	
	
	
	/**
	 * Handle the save task
	 *
	 */	
	function save()
	{
		$model =& $this->getModel('manufacturer');		
		
		if ($model->store()) {
			$msg = JText::_('VM_MANUFACTURER_SAVED');
		}
		else {
			$msg = JText::_($model->getError());
		}
		
		$this->setRedirect('index.php?option=com_virtuemart&view=manufacturer', $msg);
	}	
	
	
	/**
	 * Handle the remove task
	 *
	 */		
	function remove()
	{
		$model = $this->getModel('manufacturer');
		if (!$model->delete()) {
			$msg = JText::_('VM_MANUFACTURER_DELETE_ERROR');
		}
		else {
			$msg = JText::_( 'VM_MANUFACTURER_DELETE_SUCCESS');
		}
	
		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturer', $msg);
	}	
	
	
	/**
	 * Handle the publish task
	 *
	 */		
	function publish()
	{
		$model = $this->getModel('manufacturer');
		if (!$model->publish(true)) {
			$msg = JText::_('VM_MANUFACTURER_PUBLISH_ERROR');
		}
	
		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturer', $msg);
	}		
	
	
	/**
	 * Handle the publish task
	 *
	 */		
	function unpublish()
	{
		$model = $this->getModel('manufacturer');
		if (!$model->publish(false)) {
			$msg = JText::_('VM_MANUFACTURER_UNPUBLISH_ERROR');
		}
	
		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturer', $msg);
	}	
}
?>

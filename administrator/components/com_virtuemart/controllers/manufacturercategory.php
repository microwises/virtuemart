<?php
/**
 * Manufacturer category controller
 *
 * @package	VirtueMart
 * @subpackage Manufacturer Category
 * @author vhv_alex
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * Manufacturer category controller
 *
 * @package    VirtueMart
 * @subpackage Manufacturer
 */
class VirtuemartControllerManufacturerCategory extends JController
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
		$view =& $this->getView('manufacturerCategory', $viewType);		

		// Push a model into the view					
		$model =& $this->getModel('manufacturerCategory');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}			
	
	}
	
	/**
	 * Display the country view
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
		JRequest::setVar('controller', 'manufacturerCategory');
		JRequest::setVar('view', 'manufacturerCategory');
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
		$this->setRedirect('index.php?option=com_virtuemart&view=manufacturerCategory');
	}	
	
	
	/**
	 * Handle the save task
	 *
	 */	
	function save()
	{
		$model =& $this->getModel('manufacturerCategory');		
		
		if ($model->store()) {
			$msg = JText::_('VM_MANUFACTURER_CATEGORY_SAVED');
		}
		else {
			$msg = JText::_($model->getError());
		}
		
		$this->setRedirect('index.php?option=com_virtuemart&view=manufacturerCategory', $msg);
	}	
	
	
	/**
	 * Handle the remove task
	 *
	 */		
	function remove()
	{
		$model = $this->getModel('manufacturerCategory');
		if (!$model->delete()) {
			$msg = JText::_('VM_MANUFACTURER_CATEGORY_DELETE_WARNING');
		}
		else {
			$msg = JText::_( 'VM_MANUFACTURER_DELETE_SUCCESS');
		}
	
		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturerCategory', $msg);
	}	
	
	
	/**
	 * Handle the publish task
	 *
	 */		
	function publish()
	{
		$model = $this->getModel('manufacturerCategory');
		if (!$model->publish(true)) {
			$msg = JText::_('VM_MANUFACTURER_CATEGORY_PUBLISH_ERROR');
		}
	
		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturerCategory', $msg);
	}		
	
	
	/**
	 * Handle the publish task
	 *
	 */		
	function unpublish()
	{
		$model = $this->getModel('manufacturerCategory');
		if (!$model->publish(false)) {
			$msg = JText::_('VM_MANUFACTURER_CATEGORY_UNPUBLISH_ERROR');
		}
	
		$this->setRedirect( 'index.php?option=com_virtuemart&view=manufacturerCategory', $msg);
	}	
}
?>

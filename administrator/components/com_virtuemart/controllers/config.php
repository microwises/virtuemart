<?php
/**
 * Config controller
 *
 * @package	VirtueMart
 * @subpackage Config
 * @author RickG 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * Configuration Controller
 *
 * @package    VirtueMart
 * @subpackage Config
 * @author RickG 
 */
class VirtuemartControllerConfig extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();			
	    
		$document = JFactory::getDocument();				
		$viewType = $document->getType();
		$view = $this->getView('config', $viewType);		

		// Push a model into the view					
		$model = $this->getModel('config');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}	
		$model = $this->getModel('user');
		if (!JError::isError($model)) {
			$view->setModel($model, false);
		}
	}
	
	/**
	 * Display the config view
	 *
	 * @author RickG	 
	 */
	function display() {			
		parent::display();
	}
	
	
	/**
	 * Handle the edit task
	 *
     * @author RickG
	 */
	function edit()
	{	
		JRequest::setVar('controller', 'config');
		JRequest::setVar('view', 'config');
		JRequest::setVar('layout', 'edit');
		JRequest::setVar('hidemenu', 1);		
		
		parent::display();
	}		
	
	
	/**
	 * Handle the cancel task
	 *
	 * @author RickG
	 */
	function cancel()
	{
		$msg = JText::_('Operation Canceled!!');
		
		$this->setRedirect('index.php?option=com_virtuemart&view=config', $msg);
	}	
	
	
	/**
	 * Handle the save task
	 *
	 * @author RickG	 
	 */	
	function save()
	{
		$model = $this->getModel('config');		
		$data = JRequest::get('post');				
//die(print_r($data));
		if ($model->store($data)) {
			$msg = JText::_('Config saved!');
			// Load the newly saved values into the session.
			VmConfig::loadConfig();
		}
		else {
			$msg = JText::_($model->getError());
		}
		
		$this->setRedirect('index.php?option=com_virtuemart&view=config', $msg);
	}	
	
	
	/**
	 * Handle the remove task
	 *
	 * @author RickG	 
	 */		
	function remove()
	{
		$model = $this->getModel('config');
		if (!$model->delete()) {
			$msg = JText::_('Error: One or more configs could not be deleted!');
		}
		else {
			$msg = JText::_( 'Configs Deleted!');
		}
	
		$this->setRedirect( 'index.php?option=com_virtuemart&view=config', $msg);
	}		
}
?>

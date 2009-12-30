<?php
/**
 * Currency controller
 *
 * @package	VirtueMart
 * @subpackage Currency
 * @author RickG 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * Currency Controller
 *
 * @package    VirtueMart
 * @subpackage Currency
 * @author RickG 
 */
class VirtuemartControllerCurrency extends JController
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
		$view =& $this->getView('currency', $viewType);		

		// Push a model into the view					
		$model =& $this->getModel('currency');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}					
	}
	
	/**
	 * Display the currency view
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
		JRequest::setVar('controller', 'currency');
		JRequest::setVar('view', 'currency');
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
		$this->setRedirect('index.php?option=com_virtuemart&view=currency');
	}	
	
	
	/**
	 * Handle the save task
	 *
	 * @author RickG	 
	 */	
	function save()
	{
		$model =& $this->getModel('currency');		
		
		if ($model->store()) {
			$msg = JText::_('Currency saved!');
		}
		else {
			$msg = JText::_($model->getError());
		}
		
		$this->setRedirect('index.php?option=com_virtuemart&view=currency', $msg);
	}	
	
	
	/**
	 * Handle the remove task
	 *
	 * @author RickG	 
	 */		
	function remove()
	{
		$model = $this->getModel('currency');
		if (!$model->delete()) {
			$msg = JText::_('Error: One or more currencies could not be deleted!');
		}
		else {
			$msg = JText::_( 'Currencies Deleted!');
		}
	
		$this->setRedirect( 'index.php?option=com_virtuemart&view=currency', $msg);
	}	
	
	
	/**
	 * Handle the publish task
	 *
	 * @author RickG	 
	 */		
	function publish()
	{
		$model = $this->getModel('currency');
		if (!$model->publish(true)) {
			$msg = JText::_('Error: One or more currencies could not be published!');
		}
	
		$this->setRedirect( 'index.php?option=com_virtuemart&view=currency', $msg);
	}		
	
	
	/**
	 * Handle the publish task
	 *
	 * @author RickG	 
	 */		
	function unpublish()
	{
		$model = $this->getModel('currency');
		if (!$model->publish(false)) {
			$msg = JText::_('Error: One or more currencies could not be unpublished!');
		}
	
		$this->setRedirect( 'index.php?option=com_virtuemart&view=currency', $msg);
	}	
}
?>

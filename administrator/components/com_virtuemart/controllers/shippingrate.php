<?php
/**
 * Shipping Rate controller
 *
 * @package	VirtueMart
 * @subpackage ShippingRate
 * @author RickG 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * Shipping Carrier Controller
 *
 * @package    VirtueMart
 * @subpackage ShippingRate
 * @author RickG 
 */
class VirtuemartControllerShippingRate extends JController
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
		$view =& $this->getView('shippingrate', $viewType);		

		// Push a model into the view					
		$model =& $this->getModel('shippingrate');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}					
		$model1 =& $this->getModel('country');
		if (!JError::isError($model1)) {
			$view->setModel($model1, false);
		}		
		$model2 =& $this->getModel('shippingcarrier');
		if (!JError::isError($model2)) {
			$view->setModel($model2, false);
		}	
		$model3 =& $this->getModel('currency');
		if (!JError::isError($model3)) {
			$view->setModel($model3, false);
		}	
		$model =& $this->getModel('taxrate');
		if (!JError::isError($model)) {
			$view->setModel($model, false);
		}					
	}
	
	/**
	 * Display the shipping rate view
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
		JRequest::setVar('controller', 'shippingrate');
		JRequest::setVar('view', 'shippingrate');
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
		$this->setRedirect('index.php?option=com_virtuemart&view=shippingrate');
	}	
	
	
	/**
	 * Handle the save task
	 *
	 * @author RickG	 
	 */	
	function save()
	{
		$model =& $this->getModel('shippingrate');		
		
		if ($model->store()) {
			$msg = JText::_('Shipping Rate saved!');
		}
		else {
			$msg = JText::_($model->getError());
		}
		
		$this->setRedirect('index.php?option=com_virtuemart&view=shippingrate', $msg);
	}	
	
	
	/**
	 * Handle the remove task
	 *
	 * @author RickG	 
	 */		
	function remove()
	{
		$model = $this->getModel('shippingrate');
		if (!$model->delete()) {
			$msg = JText::_('Error: One or more shipping rates could not be deleted!');
		}
		else {
			$msg = JText::_( 'Shipping rates deleted!');
		}
	
		$this->setRedirect( 'index.php?option=com_virtuemart&view=shippingrate', $msg);
	}		
}
?>

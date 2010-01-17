<?php
/**
 * State controller
 *
 * @package	VirtueMart
 * @subpackage State
 * @author jseros 
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

jimport('joomla.application.component.controller');

require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'state.php' );

class VirtueMartControllerState extends JController
{
    /**
	 * Method to display the view
	 *
	 * @access	public
	 * @author RickG and Max Milbers
	 */
	public function __construct() {
		parent::__construct();
		
		$document = JFactory::getDocument();				
		$viewType = $document->getType();
		$view = $this->getView('state', $viewType);		

		$stateModel = new VirtueMartModelState();
		
		// Push a model into the view
		if (!JError::isError($stateModel)) {
			$view->setModel($stateModel, true);
		}
	}
	
}
<?php
/**
* @package		VirtueMart
* @author RolandD
*/

jimport('joomla.application.component.controller');

/**
* VirtueMart Component Controller
*
* @package VirtueMart
* @author RolandD
*/
class VirtueMartControllerProductdetails extends JController {
    
	public function __construct() {
		parent::__construct();
	}
	
	public function Productdetails() {
		/* Create the view */
		$view = $this->getView('productdetails', 'html');
		
		/* Add the default model */
		$view->setModel($this->getModel('productdetails','VirtuemartModel'), true);
		
		/* Add the category model */
		$view->setModel($this->getModel('category', 'VirtuemartModel'));
		
		/* Set the layout */
		$view->setLayout('productdetails');
		
		/* Display it all */
		$view->display();
	}
}
?>

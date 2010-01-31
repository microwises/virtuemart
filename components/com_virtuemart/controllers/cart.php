<?php
/**
*
* Controller for the cart
*
* @package	VirtueMart
* @subpackage Cart
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
* Controller for the cart view
*
* @package VirtueMart
* @subpackage Cart
* @author RolandD
*/
class VirtueMartControllerCart extends JController {

    /**
    * Construct the cart
    *
    * @access public
    * @author RolandD
    */
	public function __construct() {
		parent::__construct();
	
		// $this->registerTask('add', 'cart');
	}

	/**
	* Show the main page for the cart 
	* 
	* @author RolandD 
	* @access public 
	*/
	public function Cart() {
		/* Create the view */
		$view = $this->getView('cart', 'html');
		
		/* Add the default model */
		$view->setModel($this->getModel('cart', 'VirtuemartModel'), true);
		
		/* Set the layout */
		$view->setLayout('cart');
		
		/* Display it all */
		$view->display();
	}
	
	/**
	* Add the product to the cart 
	* 
	* @author RolandD 
	* @access public 
	*/
	public function add() {
		$mainframe = JFactory::getApplication();
		/* Load the cart helper */
		require_once(JPATH_COMPONENT.DS.'helpers'.DS.'cart.php');
		$this->getModel('productdetails');
		$model = $this->getModel('cart');
		$msgtype = '';
		if ($model->add()) $msg = JText::_('PRODUCT_ADDED_SUCCESSFULLY');
		else {
			$msg = JText::_('PRODUCT_NOT_ADDED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=cart');
	}
}
?>

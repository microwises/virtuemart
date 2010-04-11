<?php
/**
*
* Controller for the checkout
*
* @package	VirtueMart
* @subpackage Checkout
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: cart.php 2302 2010-02-07 19:57:37Z rolandd $
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
* Controller for the checkout view
*
* @package VirtueMart
* @subpackage Checkout
* @author RolandD
*/
class VirtueMartControllerCheckout extends JController {

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
	public function Checkout() {
		/* Create the view */
		$view = $this->getView('checkout', 'html');
		
		/* Add the default model */
		$view->setModel($this->getModel('checkout', 'VirtuemartModel'), true);
		$view->setModel($this->getModel('cart', 'VirtuemartModel'));
		
		/* Set the layout */
		$view->setLayout('checkout');
		
		/* Display it all */
		$view->display();
	}
}
?>

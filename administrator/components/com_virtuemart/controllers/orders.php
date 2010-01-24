<?php
/**
*
* Orders controller
*
* @package	VirtueMart
* @subpackage
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
* Orders Controller
*
* @package    VirtueMart
* @author
*/
class VirtuemartControllerOrders extends JController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct();

		/* Redirect templates to templates as this is the standard call */
		//$this->registerTask('edit','orders');
	}

	/**
	* Shows the product list screen
	*/
	public function Orders() {
		/* Create the view object */
		$view = $this->getView('orders', 'html');

		/* Default model */
		$view->setModel( $this->getModel( 'orders', 'VirtueMartModel' ), true );

		/* Set the layout */
		$view->setLayout('orders');

		/* Now display the view. */
		$view->display();
	}

	/**
	* Shows the order details
	*/
	public function edit() {
		/* Create the view object */
		$view = $this->getView('orders', 'html');

		/* Default model */
		$view->setModel( $this->getModel( 'orders', 'VirtueMartModel' ), true );

		/* Set the layout */
		$view->setLayout('orders_edit');

		/* Now display the view. */
		$view->display();
	}

	/**
	* Cancellation, redirect to main order list
	*
	* @author RolandD
	*/
	public function Cancel() {
		$mainframe = Jfactory::getApplication();
		$mainframe->redirect('index.php?option=com_virtuemart&view=orders');
	}

	/**
	* Save an order
	*
	* @author RolandD
	*/
	public function save() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('orders', 'html');

		$model = $this->getModel('orders');
		$msgtype = '';
		if ($model->saveOrder()) $msg = JText::_('ORDER_SAVED_SUCCESSFULLY');
		else {
			$msg = JText::_('ORDER_NOT_SAVED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=orders', $msg, $msgtype);
	}

	/**
	* Delete an order
	*
	* @author RolandD
	*/
	public function remove() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('orders', 'html');

		$model = $this->getModel('orders');
		$msgtype = '';
		if ($model->removeOrder()) $msg = JText::_('ORDER_REMOVED_SUCCESSFULLY');
		else {
			$msg = JText::_('ORDER_NOT_REMOVED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=orders', $msg, $msgtype);
	}

	/**
	* Update an order status
	*
	* @author RolandD
	*/
	public function updatestatus() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('orders', 'html');

		/* Load the helper */
		$view->loadHelper('shopFunctions');
		$view->loadHelper('vendorHelper');

		/* Update the statuses */
		$model = $this->getModel('orders');
		$result = $model->updateStatus();

		if ($result['updated'] > 0) 
		    $msg = str_replace('{X}', $result['updated'], JText::_('ORDER_UPDATED_SUCCESSFULLY'));
		if ($result['error'] > 0) 
		    $msg - str_replace('{X}', $result['error'], JText::_('ORDER_NOT_UPDATED_SUCCESSFULLY'));

		$mainframe->redirect('index.php?option=com_virtuemart&view=orders', $msg);
	}
}
?>

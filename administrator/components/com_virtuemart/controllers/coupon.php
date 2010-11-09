<?php
/**
*
* Coupon controller
*
* @package	VirtueMart
* @subpackage Coupon
* @author RickG
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
 * Coupon Controller
 *
 * @package    VirtueMart
 * @subpackage Coupon
 * @author RickG
 */
class VirtuemartControllerCoupon extends JController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct();

		// Register Extra tasks
		$this->registerTask( 'add',  'edit' );

		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$view = $this->getView('coupon', $viewType);

		// Push a model into the view
		$model = $this->getModel('coupon');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
	}

	/**
	 * Display the coupon view
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
		JRequest::setVar('controller', 'coupon');
		JRequest::setVar('view', 'coupon');
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
		$this->setRedirect('index.php?option=com_virtuemart&view=coupon');
	}


	/**
	 * Handle the save task
	 *
	 * @author RickG
	 */
	function save()
	{
		$model = $this->getModel('coupon');

		if ($model->store()) {
			$msg = JText::_('Coupon saved!');
		}
		else {
			$msg = JText::_($model->getError());
		}

		$this->setRedirect('index.php?option=com_virtuemart&view=coupon', $msg);
	}


	/**
	 * Handle the remove task
	 *
	 * @author RickG
	 */
	function remove()
	{
		$model = $this->getModel('coupon');
		if (!$model->delete()) {
			$msg = JText::_('Error: One or more coupons could not be deleted!');
		}
		else {
			$msg = JText::_( 'Coupons Deleted!');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=coupon', $msg);
	}
}
// pure php no closing tag

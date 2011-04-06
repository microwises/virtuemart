<?php
/**
*
* Product types controller
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
* Product types Controller
*
* @package    VirtueMart
* @author RolandD
*/
class VirtuemartControllerProducttypes extends JController {

	/**
	* Method to display the view
	*
	* @access	public
	*/
	function __construct() {
		parent::__construct();

		/* Redirects */
		$this->registerTask('add','edit');
		$this->registerTask('apply','save');
		$this->registerTask('cancel','productTypes');
	}

	/**
	 * Shows the product list screen
	 */
	public function productTypes() {
		/* Create the view object */
		$view = $this->getView('producttypes', 'html');

		/* Default model */
		$view->setModel( $this->getModel( 'producttypes', 'VirtueMartModel' ), true );

		/* Set the layout */
		$view->setLayout('producttypes');

		/* Now display the view. */
		$view->display();
	}

	/**
	 * Handle the edit task
	 *
     * @author RolandD
	 */
	function edit() {
		JRequest::setVar('controller', 'producttypes');
		JRequest::setVar('view', 'producttypes');
		JRequest::setVar('layout', 'producttypes_edit');
		JRequest::setVar('hidemenu', 1);

		parent::display();
	}

	/**
	* Save a product type
	*
	* @author RolandD, Max Milbers
	*/
	public function save() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('producttypes', 'html');

		$model = $this->getModel('producttypes');
		$msgtype = '';
		if ($id=$model->saveProductType()) $msg = JText::_('COM_VIRTUEMART_PRODUCTTYPE_SAVED_SUCCESSFULLY');
		else {
			$msg = JText::_('COM_VIRTUEMART_PRODUCTTYPE_NOT_SAVED_SUCCESSFULLY');
			$msgtype = 'error';
		}

		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply'){
			$redirection = 'index.php?option=com_virtuemart&view=producttypes&task=edit&cid[]='.$id;
		} else {
			$redirection = 'index.php?option=com_virtuemart&view=producttypes';
		}

		$mainframe->redirect($redirection, $msg, $msgtype);

	}

	/**
	* Delete a discount
	* @author RolandD
	*/
	public function remove() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('discounts', 'html');

		$model = $this->getModel('disocunts');
		$msgtype = '';
		if ($model->removeDiscount()) $msg = JText::_('COM_VIRTUEMART_DISOUNCT_REMOVED_SUCCESSFULLY');
		else {
			$msg = JText::_('COM_VIRTUEMART_DISCOUNT_NOT_REMOVED_SUCCESSFULLY');
			$msgtype = 'error';
		}

		$mainframe->redirect('index.php?option=com_virtuemart&view=discounts&task=discounts', $msg, $msgtype);
	}
}
// pure php no closing tag

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
//		$this->registerTask('saveorder','productTypes');
		$this->registerTask('orderup','reorder');
		$this->registerTask('orderdown','reorder');
//		$this->registerTask('unpublish','productTypes');
//		$this->registerTask('publish','productTypes');
		$this->registerTask('add','edit');
		$this->registerTask('apply','save');
		$this->registerTask('addParameter','save');
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
	 * orderup
	 *
	 * @author Kohl Patrick
	 */
	public function reorder(){

		$model = $this->getModel('producttypes');

		$cmd = JRequest::getCmd('task');
		if($cmd == 'orderup') $dir = -1 ;
		else $dir= 1 ;
		if (!$model->orderChange( $dir )) {
			$msg = JText::_('COM_VIRTUEMART_TYPES_REORDER_ERROR');
		} else {
			$msg = JText::_('COM_VIRTUEMART_TYPES_REORDER_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=producttypes', $msg);
	}	/**
	 * orderup
	 *
	 * @author Kohl Patrick
	 */
	public function saveOrder(){

		$model = $this->getModel('producttypes');

		if (!$model->saveOrder()) {
			$msg = JText::_('COM_VIRTUEMART_TYPES_SAVE_ORDER_ERROR');
		} else {
			$msg = JText::_('COM_VIRTUEMART_TYPES_SAVE_ORDER_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=producttypes', $msg);
	}
	/**
	 * Handle the publish task
	 *
	 * @author Max Milbers
	 */
	public function publish(){
		$model = $this->getModel('producttypes');
		if (!$model->publish(true)) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_PRODUCTS_COULD_NOT_BE_PUBLISHED');
		} else {
			$msg = JText::_('COM_VIRTUEMART_PRODUCTS_PUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=producttypes', $msg);
	}

	/**
	 * Handle the publish task
	 *
	 * @author RickG, jseros
	 */
	public function unpublish(){
		$model = $this->getModel('producttypes');
		if (!$model->publish(false)) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_PRODUCTTYPE_COULD_NOT_BE_UNPUBLISHED');
		} else {
			$msg = JText::_('COM_VIRTUEMART_PRODUCTTYPE_UNPUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=producttypes', $msg);
	}
	/**
	 * Handle the edit task
	 *
     * @author RolandD
	 */
	function edit() {
		JRequest::setVar('view', 'producttypes');
		JRequest::setVar('layout', 'edit');
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
		} else if($cmd == 'addParameter'){
			$redirection = 'index.php?option=com_virtuemart&view=producttypeparameters&task=add&product_type_id='.$id;
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
		$view = $this->getView('producttypes', 'html');

		$model = $this->getModel('producttypes');
		$msgtype = '';
		if ($model->removeProducttype()) $msg = JText::_('COM_VIRTUEMART_PRODUCTTYPE_REMOVED_SUCCESSFULLY');
		else {
			$msg = JText::_('COM_VIRTUEMART_PRODUCTTYPE_NOT_REMOVED_SUCCESSFULLY');
			$msgtype = 'error';
		}

		$mainframe->redirect('index.php?option=com_virtuemart&view=producttypes', $msg, $msgtype);
	}
	public function saveParameter() {
		$mainframe = Jfactory::getApplication();
		$mainframe->redirect('index.php?option=com_virtuemart&view=producttypes', $msg, $msgtype);
	}
}
// pure php no closing tag

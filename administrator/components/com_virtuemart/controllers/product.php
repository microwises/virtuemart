<?php
/**
*
* Product controller
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

if(!class_exists('VmController'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmcontroller.php');


/**
 * Product Controller
 *
 * @package    VirtueMart
 * @author
 */
class VirtuemartControllerProduct extends VmController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct();


//		$this->setMainLangKey('PRODUCT');
		/* Redirect templates to templates as this is the standard call */
		$this->registerTask('saveorder','product');
		$this->registerTask('orderup','product');
		$this->registerTask('orderdown','product');
//		$this->registerTask('unpublish','product');
//		$this->registerTask('publish','product');
		$this->registerTask('edit','add');
		$this->registerTask('apply','save');

		/* dont SET THE HTML View and layout in constructor or json view is broken
		/* Create the view object */
		/*$view = $this->getView('product', 'html');
		/* Set the layout */
		/*$view->setLayout('product');
		  **/

	}

	/**
	 * Shows the product list screen
	 */
	public function Product() {
		/* Create the view object */
		$view = $this->getView('product', 'html');

		/* Default model */
		$view->setModel( $this->getModel( 'product', 'VirtueMartModel' ), true );
		/* Media files functions */
		$view->setModel( $this->getModel( 'media', 'VirtueMartModel' ));
		/* Product reviews functions */
		$view->setModel( $this->getModel( 'ratings', 'VirtueMartModel' ));
		/* Product category functions */
		$view->setModel( $this->getModel( 'category', 'VirtueMartModel' ));
		/* Vendor functions */
		$view->setModel( $this->getModel( 'vendor', 'VirtueMartModel' ));

		/* Set the layout */
		$view->setLayout('product');

		/* Now display the view. */
		$view->display();
	}

	/**
	 * Handle the publish task
	 *
	 * @author Max Milbers
	 */
	public function publish(){
		$model = $this->getModel('product');
		if (!$model->publish(true)) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_PRODUCTS_COULD_NOT_BE_PUBLISHED');
		} else {
			$msg = JText::_('COM_VIRTUEMART_PRODUCTS_PUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=product', $msg);
	}

	/**
	 * Handle the publish task
	 *
	 * @author RickG, jseros
	 */
	public function unpublish(){
		$model = $this->getModel('product');
		if (!$model->publish(false)) {
			$msg = JText::_('COM_VIRTUEMART_ERROR_PRODUCTS_COULD_NOT_BE_UNPUBLISHED');
		} else {
			$msg = JText::_('COM_VIRTUEMART_PRODUCTS_UNPUBLISHED_SUCCESS');
		}

		$this->setRedirect( 'index.php?option=com_virtuemart&view=product', $msg);
	}
	/**
	 * Shows the product add/edit screen
	 */
	public function add() {
		/* Create the view object */
		$view = $this->getView('product', 'html');

		/* Default model */
		$view->setModel( $this->getModel( 'product', 'VirtueMartModel' ), true );
		/* Media files functions */
		$view->setModel( $this->getModel( 'media', 'VirtueMartModel' ));
		/* Product category functions */
		$view->setModel( $this->getModel( 'category', 'VirtueMartModel' ));
		/* Vendor functions */
		$view->setModel( $this->getModel( 'vendor', 'VirtueMartModel' ));
		/* Manufacturer functions */
		$view->setModel( $this->getModel( 'manufacturer', 'VirtueMartModel' ));
		/* Currency functions */
		$view->setModel( $this->getModel( 'currency', 'VirtueMartModel' ));
		/* Waitinglist functions */
		$view->setModel( $this->getModel( 'waitinglist', 'VirtueMartModel' ));
		/* custom functions */
		$view->setModel( $this->getModel( 'custom', 'VirtueMartModel' ));

		/* Set the layout */
		$view->setLayout('product_edit');

		/* Now display the view. */
		$view->display();
	}

	/**
	* Cancellation, redirect to main product list
	*
	* @author RolandD
	*/
	public function Cancel() {
		$mainframe = Jfactory::getApplication();
		$mainframe->redirect('index.php?option=com_virtuemart&view=product&task=product&product_parent_id='.JRequest::getInt('product_parent_id'));
	}

	/**
	* Save a product
	*
	* @author RolandD, Max Milbers
	*/
	public function save() {

		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('product', 'html');

		/* Waitinglist functions */
		$view->setModel( $this->getModel( 'waitinglist', 'VirtueMartModel' ));

		/* Load some helpers */
		$view->loadHelper('image');
		$view->loadHelper('shopFunctions');

		$model = $this->getModel('product');
		$msgtype = '';
		if ($virtuemart_product_id = $model->saveProduct()){
			 $msg = JText::_('COM_VIRTUEMART_PRODUCT_SAVED_SUCCESSFULLY');
		}
		else {
			$msg = $model->getError();
			$msgtype = 'error';
		}

		$cmd = JRequest::getCmd('task');
		if($cmd == 'apply'){
			$redirection = 'index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id='.$virtuemart_product_id.'&product_parent_id='.JRequest::getInt('product_parent_id');
		} else {
			$redirection = 'index.php?option=com_virtuemart&view=product';
		}

		$mainframe->redirect($redirection, $msg, $msgtype);

	}

	/**
	 * This task creates a child by a given product id
	 *
	 * @author Max Milbers
	 */
	public function createChild(){
		$app = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('product', 'html');

		$model = $this->getModel('product');

		$cids = JRequest::getVar('cid');
		if ($id=$model->createChild($cids[0])){
			$msg = JText::_('COM_VIRTUEMART_PRODUCT_CHILD_CREATED_SUCCESSFULLY');
			$redirect = 'index.php?option=com_virtuemart&controller=product&task=edit&product_parent_id='.$cids[0].'&virtuemart_product_id='.$id;
		} else {
			$msg = JText::_('COM_VIRTUEMART_PRODUCT_NO_CHILD_CREATED_SUCCESSFULLY');
			$msgtype = 'error';
			$redirect = 'index.php?option=com_virtuemart&controller=product';
		}
		$app->redirect($redirect, $msg, $msgtype);

	}

	/**
	* Clone a product
	*
	* @author RolandD, Max Milbers
	*/
	public function CloneProduct() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('product', 'html');

		$model = $this->getModel('product');
		$msgtype = '';
		$cids = JRequest::getVar('cid');
		if ($model->createClone($cids[0])) $msg = JText::_('COM_VIRTUEMART_PRODUCT_CLONED_SUCCESSFULLY');
		else {
			$msg = JText::_('COM_VIRTUEMART_PRODUCT_NOT_CLONED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=product&task=product&product_parent_id='.JRequest::getInt('product_parent_id'), $msg, $msgtype);
	}

	/**
	* Delete a product
	*
	* @author RolandD
	*/
	public function remove() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('product', 'html');

		$model = $this->getModel('product');
		$msgtype = '';
		if ($model->removeProduct()) $msg = JText::_('COM_VIRTUEMART_PRODUCT_REMOVED_SUCCESSFULLY');
		else {
			$msg = JText::_('COM_VIRTUEMART_PRODUCT_NOT_REMOVED_SUCCESSFULLY');
			$msgtype = 'error';
		}

		$mainframe->redirect('index.php?option=com_virtuemart&view=product&task=product&product_parent_id='.JRequest::getInt('product_parent_id'), $msg, $msgtype);
	}

	/**
	* Get a list of related products
	* @author RolandD
	*/
	public function getData() {

		/* Create the view object. */
		$view = $this->getView('product', 'json');

		/* Standard model */
		//$view->setModel( $this->getModel( 'product', 'VirtueMartModel' ), true );
		$type = JRequest::getVar('type', false);
		if ($type = 'customfield') {
			$view->setModel( $this->getModel( 'product', 'VirtueMartModel' ));
			$view->setModel( $this->getModel( 'custom', 'VirtueMartModel' ));
		}
		/* Now display the view. */
		$view->display(null);
	}

	/**
	* Add a product rating
	* @author RolandD
	*/
	public function addRating() {
		$mainframe = Jfactory::getApplication();

		/* Get the product ID */
		$cids = array();
		$cids = JRequest::getVar('cid');
		if (!is_array($cids)) $cids = array($cids);

		$mainframe->redirect('index.php?option=com_virtuemart&view=ratings&task=add&virtuemart_product_id='.$cids[0]);
	}


}
// pure php no closing tag

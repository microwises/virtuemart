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
		parent::__construct('virtuemart_product_id');

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
		$view->setLayout('default');

		/* Now display the view. */
		$view->display();
	}


	/**
	 * Shows the product add/edit screen
	 */
	public function edit() {
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
	 * We want to allow html so we need to overwrite some request data
	 *
	 * @author Max Milbers
	 */
	function save(){

		$data = JRequest::get('post');

		$data['product_desc'] = JRequest::getVar('product_desc','','post','STRING',JREQUEST_ALLOWHTML);

		parent::save($data);
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
		if (!$cids) $cids = JRequest::getVar('virtuemart_product_id');
		if ($id=$model->createChild($cids[0])){
			$msg = JText::_('COM_VIRTUEMART_PRODUCT_CHILD_CREATED_SUCCESSFULLY');
			$redirect = 'index.php?option=com_virtuemart&view=product&task=edit&product_parent_id='.$cids[0].'&virtuemart_product_id='.$id;
		} else {
			$msg = JText::_('COM_VIRTUEMART_PRODUCT_NO_CHILD_CREATED_SUCCESSFULLY');
			$msgtype = 'error';
			$redirect = 'index.php?option=com_virtuemart&view=product';
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
		$cids = JRequest::getVar('virtuemart_product_id');
		if ($model->createClone($cids[0])) $msg = JText::_('COM_VIRTUEMART_PRODUCT_CLONED_SUCCESSFULLY');
		else {
			$msg = JText::_('COM_VIRTUEMART_PRODUCT_NOT_CLONED_SUCCESSFULLY');
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
		} else $view->setModel( $this->getModel( 'product', 'VirtueMartModel' ));
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

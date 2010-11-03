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

/**
 * Product Controller
 *
 * @package    VirtueMart
 * @author
 */
class VirtuemartControllerProduct extends JController {

	/**
	 * Method to display the view
	 *
	 * @access	public
	 * @author
	 */
	function __construct() {
		parent::__construct();

		/* Redirect templates to templates as this is the standard call */
		$this->registerTask('saveorder','product');
		$this->registerTask('orderup','product');
		$this->registerTask('orderdown','product');
		$this->registerTask('unpublish','product');
		$this->registerTask('publish','product');
		$this->registerTask('edit','add');
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

		/* Set the layout */
		$view->setLayout('product');

		/* Now display the view. */
		$view->display();
	}

	/**
	 * Shows the product add/edit screen
	 */
	public function Add() {
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
		/* Tax functions */
		$view->setModel( $this->getModel( 'taxRate', 'VirtueMartModel' ));
		/* Discount functions */
//		$view->setModel( $this->getModel( 'discount', 'VirtueMartModel' ));
		/* Waitinglist functions */
		$view->setModel( $this->getModel( 'waitinglist', 'VirtueMartModel' ));

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
	* @author RolandD
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
		if ($model->saveProduct()) $msg = JText::_('PRODUCT_SAVED_SUCCESSFULLY');
		else {
			$msg = $model->getError();
//			$msg = JText::_('PRODUCT_NOT_SAVED_SUCCESSFULLY');
//			$msg = $model->getErrorMsg();
			$msgtype = 'error';
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=product&task=product&product_parent_id='.JRequest::getInt('product_parent_id'), $msg, $msgtype);

	}

	/**
	* Clone a product
	*
	* @author RolandD
	*/
	public function CloneProduct() {
		$mainframe = Jfactory::getApplication();

		/* Load the view object */
		$view = $this->getView('product', 'html');

		$model = $this->getModel('product');
		$msgtype = '';
		if ($model->cloneProduct()) $msg = JText::_('PRODUCT_CLONED_SUCCESSFULLY');
		else {
			$msg = JText::_('PRODUCT_NOT_CLONED_SUCCESSFULLY');
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
		if ($model->removeProduct()) $msg = JText::_('PRODUCT_REMOVED_SUCCESSFULLY');
		else {
			$msg = JText::_('PRODUCT_NOT_REMOVED_SUCCESSFULLY');
			$msgtype = 'error';
		}

		$mainframe->redirect('index.php?option=com_virtuemart&view=product&task=product&product_parent_id='.JRequest::getInt('product_parent_id'), $msg, $msgtype);
	}

	/**
	* Get a list of related products
	* @author RolandD
	*/
	public function getData() {
		/* Create the view object */
		$view = $this->getView('product', 'json');

		/* Default model */
		$view->setModel( $this->getModel( 'product', 'VirtueMartModel' ), true );

		$view->setLayout('product');

		/* Now display the view. */
		$view->display();
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

		$mainframe->redirect('index.php?option=com_virtuemart&view=ratings&task=add&product_id='.$cids[0]);
	}

	/**
	* Add a product type to a product
	* @author RolandD
	*/
	public function addProductType() {
		/* Create the view object */
		$view = $this->getView('product', 'html');

		/* Default model */
		$view->setModel( $this->getModel( 'product', 'VirtueMartModel' ), true );

		/* Set the layout */
		$view->setLayout('product_type_add');

		/* Now display the view. */
		$view->display();
	}

	/**
	* Save a product with a product type relation
	*
	* @author RolandD
	*/
	public function saveProductType() {
		$mainframe = Jfactory::getApplication();

		$model = $this->getModel('product');
		$msgtype = '';
		if ($model->saveProductType()) $msg = JText::_('PRODUCT_TYPE_LINK_SAVED_SUCCESSFULLY');
		else {
			$msg = JText::_('PRODUCT_TYPE_LINK_NOT_SAVED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=product&task=product&product_parent_id='.JRequest::getInt('product_parent_id'), $msg, $msgtype);
	}

	/**
	* Add a product attribute
	* @author RolandD
	*/
	public function addAttribute() {
		$mainframe = Jfactory::getApplication();

		/* Get the product ID */
		$cids = array();
		$cids = JRequest::getVar('cid');
		if (!is_array($cids)) $cids = array($cids);

		$mainframe->redirect('index.php?option=com_virtuemart&view=attributes&task=add&product_id='.$cids[0]);
	}
}
?>

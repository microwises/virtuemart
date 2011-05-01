<?php
/**
*
* Description
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
* VirtueMart Component Controller
*
* @package VirtueMart
* @author RolandD
*/
class VirtueMartControllerProductdetails extends JController {

	public function __construct() {
		parent::__construct();
		$this->registerTask( 'askquestion','askquestion' );
	}

	public function productdetails() {

		$cart = JRequest::getVar('cart',false,'post');
//		if($cart){
//			require(JPATH_VM_SITE.DS.'controllers'.DS.'cart.php');
//			$controller= new VirtueMartControllerCart();
//			$controller->add();
//		}else{
			/* Create the view */
			$view = $this->getView('productdetails', 'html');

			$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart' . DS . 'models');
			/* Add the default model */
			$view->setModel($this->getModel('product','VirtuemartModel'), true);

			/* Add the category model */
			$view->setModel($this->getModel('category', 'VirtuemartModel'));

			/* Set the layout */
//			$view->setLayout('productdetails');

			/* Display it all */
			$view->display();
//		}
	}

	/**
	 * Send the ask question email.
	 * @author Kohl Patrick, Christopher Roussel
	 */
	public function mailAskquestion () {
		if(!class_exists('shopFunctionsF')) require(JPATH_VM_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
		$mainframe = JFactory::getApplication();
		$vars = array();

		$this->addModelPath(JPATH_VM_ADMINISTRATOR.DS.'models');
		$productModel = $this->getModel('product');
		$vars['product'] = $productModel->getProductDetails();

		$user = JFactory::getUser();
		if (empty($user->id)) {
			$fromMail = JRequest::getVar('email');
			$fromName = JRequest::getVar('name','');
		}
		else {
			$fromMail = $user->email;
			$fromName = $user->name;
	 	}
	 	$vars['user'] = array('name' => $fromName, 'email' => $fromMail);

	 	$vendorModel = $this->getModel('vendor');
		$VendorEmail = $vendorModel->getVendorEmail($vars['product']->vendor_id);

		if (shopFunctionsF::renderMail('askquestion', $VendorEmail, $vars)) {
			$string = 'COM_VIRTUEMART_MAIL_SEND_SUCCESSFULLY';
		}
		else {
			$string = 'COM_VIRTUEMART_MAIL_NOT_SEND_SUCCESSFULLY';
		}
		$mainframe->enqueueMessage(JText::_($string));

		/* Display it all */
		$view = $this->getView('askquestion', 'html');
		$view->setModel($this->getModel('category', 'VirtuemartModel'));
		$view->setLayout('mailconfirmed');
		$view->display();
	}

	/**
	 *  Ask Question form
	 *
	 */
	public function askquestion(){
		/* Create the view */
		$view = $this->getView('askquestion', 'html');

		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );

		/* Add the default model */
		$view->setModel($this->getModel('product','VirtuemartModel'), true);

		/* Add the category model */
		$view->setModel($this->getModel('category', 'VirtuemartModel'));

		/* Set the layout */
		$view->setLayout('form');

		/* Display it all */
		$view->display();
	}

	/* Add or edit a review
	 TODO  control and update in database the review */
	public function review(){

		$mainframe = JFactory::getApplication();
		// add the ratings admin model

		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
		$model = $this->getModel( 'ratings', 'VirtuemartModel' );

		/* Create the view */
		$view = $this->getView('productdetails', 'html');

		/* Add the default model */
		$view->setModel($this->getModel('product','VirtuemartModel'), true);

		/* Add the category model */
		$view->setModel($this->getModel('category', 'VirtuemartModel'));

		/* Set the layout */
		$view->setLayout('productdetails');
		$msgtype = '';
		if ($model->saveRating()) $mainframe->enqueueMessage( JText::_('COM_VIRTUEMART_RATING_SAVED_SUCCESSFULLY') );
		else {
			$mainframe->enqueueMessage($model->getError());
			$mainframe->enqueueMessage( JText::_('COM_VIRTUEMART_RATING_NOT_SAVED_SUCCESSFULLY') );
		}
		/* Display it all */
		$view->display();
	}

	/**
	 * Json task for recalculation of prices
	 *
	 * @author Max Milbers
	 * @author Patrick Kohl
	 *
	 */
	public function recalculate(){

		$post = JRequest::get('request');

//		echo '<pre>'.print_r($post,1).'</pre>';
		$product_idArray = JRequest::getVar('product_id',0);
		$product_id = $product_idArray[0];

		$this->addModelPath( JPATH_VM_ADMINISTRATOR.DS.'models' );
		$product_model = $this->getModel('product');

		$customVariant = JRequest::getVar('customPrice',array());
		$prices = $product_model->getPrice($product_id,$customVariant);

		//Why we do not have to include the calculatorh.php here?
		//Because it is already require in the model!

		$calculator = calculationHelper::getInstance();
		foreach ($prices as &$value  ){
			$value = $calculator->priceDisplay($value);
		}
//		die;
		// Get the document object.
		$document =& JFactory::getDocument();

		// Set the MIME type for JSON output.
		$document->setMimeEncoding( 'application/json' );


		echo json_encode ($prices);
		jexit();
		die;

	}

}
// pure php no closing tag

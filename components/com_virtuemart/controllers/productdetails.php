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
//		$this->registerTask( 'recalc','productdetails' );
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

			/* Add the default model */
			$view->setModel($this->getModel('productdetails','VirtuemartModel'), true);

			/* Add the category model */
			$view->setModel($this->getModel('category', 'VirtuemartModel'));

			/* Set the layout */
//			$view->setLayout('productdetails');

			/* Display it all */
			$view->display();
//		}
	}

	public function askquestion(){
		/* Create the view */
		$view = $this->getView('productdetails', 'html');

		/* Add the default model */
		$view->setModel($this->getModel('productdetails','VirtuemartModel'), true);

		/* Add the category model */
		$view->setModel($this->getModel('category', 'VirtuemartModel'));

		/* Set the layout */
		$view->setLayout('askquestion');

		/* Display it all */
		$view->display();
	}
	/* Add or edit a review
	 TODO  control and update in database the review */
	public function review(){

		$comment = JRequest::getVar('comment', '');

		$mainframe = JFactory::getApplication();
		/* Create the view */
		$view = $this->getView('productdetails', 'html');

		/* Add the default model */
		$view->setModel($this->getModel('productdetails','VirtuemartModel'), true);

		/* Add the category model */
		$view->setModel($this->getModel('category', 'VirtuemartModel'));

		/* Set the layout */
		$view->setLayout('productdetails');
		if ($comment) {
			$mainframe->enqueueMessage(JText::_('REVIEW_UPDATED_SUCCESSFULLY'));
		} else {
		$mainframe->enqueueMessage(JText::_('REVIEW_NO_COMMENT'));
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

		$product_idArray = JRequest::getVar('product_id',0);
		$product_id = $product_idArray[0];

		$product_model = $this->getModel('productdetails');

		$prices = $product_model->getPrice($product_id);

		//Why we do not have to include the calculatorh.php here?
		//Because it is already require in the model!
		$calculator = calculationHelper::getInstance();
		foreach ($prices as &$value  ){
			$value = $calculator->priceDisplay($value);
		}

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

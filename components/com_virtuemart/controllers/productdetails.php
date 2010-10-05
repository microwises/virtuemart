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
	
	public function Productdetails() {
		
		$cart = JRequest::getVar('cart',false,'post');
		if($cart){
			require_once(JPATH_COMPONENT.DS.'controllers'.DS.'cart.php');
			$controller= new VirtueMartControllerCart();
			$controller->add();
		}else{
			/* Create the view */
			$view = $this->getView('productdetails', 'html');
	
			/* Add the default model */
			$view->setModel($this->getModel('productdetails','VirtuemartModel'), true);
			
			/* Add the category model */
			$view->setModel($this->getModel('category', 'VirtuemartModel'));
			
			/* Set the layout */
			$view->setLayout('productdetails');
			
			/* Display it all */
			$view->display();
		}
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
	
	public function recalculate(){

		$product_idArray = JRequest::getVar('product_id',0);
		$product_id = $product_idArray[0];
		
		$product_model = $this->getModel('productdetails');
		$price = $product_model->getPrice($product_id);
		
//		$currencyDisplay = JRequest::getVar('currencyDisplay');
//		$currencyDisplay->getFullValue($this->product->product_price['salesPrice'])
		
//		for ($x = 0; $x < sizeof($fields); ++$x){
//	echo "key: ".key($fields)."<br>value: ".current($fields)."<br>";
//	next($fields);
//}

//		$count=0;
//		while ($count <$price.length){
//			$count++;
//			echo '<br />the current: '.current($price);
//			current($price) = $currencyDisplay->getFullValue(current($price));
//			echo '<br />the current adjusted: '.current($price);
//			next($price);
//		}
//		JRequest::setVar('tmpl', 'component');
		
		echo json_encode ($price);
		
		die;
		
	}
	
}
?>

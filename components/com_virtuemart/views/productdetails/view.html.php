<?php
/**
*
* Product details view
*
* @package VirtueMart
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

// Load the view framework
jimport( 'joomla.application.component.view' );

/**
* Product details
*
* @package VirtueMart
* @author RolandD
* @author Max Milbers
*/
class VirtueMartViewProductdetails extends JView {
	
	/**
	* Collect all data to show on the template
	*
	* @author RolandD
	*/
	function display($tpl = null) {
		
		$document = JFactory::getDocument();
		$document->addScript(JURI::base().'components/com_virtuemart/assets/js/vmprices.js');

//		echo 'grmbl <pre>'.print_r($_POST).'</pre>';	
		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();
		$task = JRequest::getCmd('task');
		
		/* Set the helper path */
		$this->addHelperPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers');
		
		/* Load helpers */
		$this->loadHelper('image');
		$this->loadHelper('vendorhelper');
		$this->loadHelper('addtocart');
		
		/* Set the titles */
		$document->setTitle(JText::_('VM_PRODUCT_DETAILS'));
		$uri = JURI::getInstance();
		
		/* Load the product */
//		$product = $this->get('product');
		$product_model = $this->getModel('productdetails');
		
		$product_idArray = JRequest::getVar('product_id');
		if(is_array($product_idArray)){
			$product_id=$product_idArray[0];
		} else {
			$product_id=$product_idArray;
		}

		$product = $product_model->getProduct($product_id);
		$this->assignRef('product', $product);

		/* Load the category */
		$category_model = $this->getModel('category');
		/* Get the category ID */
		$category_id = JRequest::getInt('category_id');
		if ($category_id == 0) {
			if (array_key_exists('0', $product->categories)) $category_id = $product->categories[0]; 
		}
		
		shopFunctionsF::setLastVisitedCategoryId($category_id);
		
		if($category_model){
			$category = $category_model->getCategory($category_id);
			$this->assignRef('category', $category);				
			$pathway->addItem($category->category_name,JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$category_id));	
		}

		$pathway->addItem(JText::_('PRODUCT_DETAILS'), $uri->toString(array('path', 'query', 'fragment')));
		$pathway->addItem($product->product_name);
		
		/* Load the reviews */
		if (VmConfig::get('pshop_allow_reviews', 1) == '1') {
			$model = $this->getModel();
			/* Show all reviews available */
			$product_reviews = $model->getProductReviews($product->product_id);
			$this->assignRef('product_reviews', $product_reviews);
		}
		
		/* Check for editing access */
		/** @todo build edit page */
		if (Permissions::getInstance()->check("admin,storeadmin")) {
			$url = JRoute::_('index2.php?option=com_virtuemart&view=productdetails&task=edit&product_id='.$product->product_id);
			$edit_link = JHTML::_('link', $url, JHTML::_('image', 'images/M_images/edit.png', JText::_('VM_PRODUCT_FORM_EDIT_PRODUCT'), array('width' => 16, 'height' => 16, 'border' => 0)));
		}
		else {
			$edit_link = "";
		}
		$this->assignRef('edit_link', $edit_link);
		
		/* Load the user details */
		$this->assignRef('user', JFactory::getUser());
		
		/* More reviews link */
		$uri = JURI::getInstance();
		$uri->setVar('showall', 1);
		$this->assignRef('more_reviews', $uri->toString());
		
		
		if($product){
			/* Use the template the user wants */
			parent::setLayout($product->detailspage);
			
		} else {
			
		}
		
		
		
		/* Display it all */
		parent::display($tpl);
	}
}

?>
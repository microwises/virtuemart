<?php
/**
*
* Handle the category view
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

// Load the view framework
jimport('joomla.application.component.view');

/**
* Handle the category view
*
* @package VirtueMart
* @author RolandD
* @todo set meta data
* @todo add full path to breadcrumb
*/
class VirtuemartViewCategory extends JView {

	public function display($tpl = null) {


		$show_prices  = VmConfig::get('show_prices',1);
		if($show_prices == '1'){
			if(!class_exists('calculationHelper')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');
		}
		$this->assignRef('show_prices', $show_prices);

		$document = JFactory::getDocument();
		// add javascript for price and cart
		vmJsApi::jPrice();

		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();

		/* Set the helper path */
		$this->addHelperPath(JPATH_VM_ADMINISTRATOR.DS.'helpers');

		/* Load helpers */
		$this->loadHelper('image');
		if (!class_exists('VirtueMartModelCategory')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'category.php');
		$categoryModel = new VirtueMartModelCategory();

		if (!class_exists('VirtueMartModelProduct')) require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'product.php');
		$productModel = new VirtueMartModelProduct();
		//$categoryModel = $this->getModel('category');
		//$productModel = $this->getModel('product');
		$categoryId = JRequest::getInt('virtuemart_category_id', 0);
		$vendorId = 1;

		$category = $categoryModel->getCategory($categoryId);
		$search = JRequest::getWord('search') ;

		//No redirect here, category id = 0 means show ALL categories! note by Max Milbers
/*		if(empty($category->virtuemart_vendor_id) && $search == null ) {
	    	$mainframe -> enqueueMessage(JText::_('COM_VIRTUEMART_CATEGORY_NOT_FOUND'));
	    	$mainframe -> redirect( 'index.php');
	    }*/

	    // Add the category name to the pathway
		if ($category->parents) {
			foreach ($category->parents as $c){
				$pathway->addItem(strip_tags($c->category_name),JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$c->virtuemart_category_id));
			}
		}
		//if($category->children)	$categoryModel->addImages($category->children);

		$category->children = $categoryModel->getChildCategoryList($vendorId, $categoryId);
		$categoryModel->addImages($category->children);

	   $this->assignRef('category', $category);

		// Set Canonic link
		$document->addHeadLink( JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$categoryId) , 'canonical', 'rel', '' );

		$categoryStripped = strip_tags($category->category_name);
	    // Set the titles
	  	if(JRequest::getInt('error')){
			$head = $document->getHeadData();
			$head['title'] = JText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');

			$document->setHeadData($head);

		} else {
			$document->setTitle($categoryStripped);
		}

		// set search and keyword
		if ($keyword = vmRequest::get('keyword', '', ' ')) {
			$pathway->addItem($keyword);
			$document->setTitle( $categoryStripped.' '.$keyword);
		}
		if ($search = JRequest::getWord('search', '')) {
			if(!class_exists('VirtueMartModelCustomfields')) require(JPATH_VM_ADMINISTRATOR.DS.'models'.DS.'customfields.php');
			$modelCustomfields = new VirtueMartModelCustomfields();
			$searchcustom = $modelCustomfields->getSearchCustom();
			$this->assignRef('searchcustom', $searchcustom);
		}
		$this->assignRef('keyword', $keyword);
		$this->assignRef('search', $search);

	    // Load the products in the given category
	    $products = $productModel->getProductsInCategory($categoryId);
	    $productModel->addImages($products);
	    $this->assignRef('products', $products);

		foreach($products as $product){
			$product->stock = $productModel->getStockIndicator($product);
		}

//	    $total = $productModel->getTotalProductsInCategory($categoryId);
	    $total = $categoryModel->countProducts($categoryId);
	    $this->assignRef('total', $total);

	    $pagination = $productModel->getPagination(true);
	    $this->assignRef('pagination', $pagination);
	    $orderByList = $productModel->getOrderByList();
	    $this->assignRef('orderByList', $orderByList);
		//$sortOrderButton = $productModel->getsortOrderButton();
		//$this->assignRef('sortOrder', $sortOrderButton);


	   if ($category->metadesc) {
			$document->setDescription( $category->metadesc );
		}
		if ($category->metakey) {
			$document->setMetaData('keywords', $category->metakey);
		}
		if ($category->metarobot) {
			$document->setMetaData('robots', $category->metarobot);
		}

		if ($mainframe->getCfg('MetaTitle') == '1') {
			$document->setMetaData('title',  $categoryStripped);

		}
		if ($mainframe->getCfg('MetaAuthor') == '1') {
			$document->setMetaData('author', $category->metaauthor);
		}
		if ($products) {
		$currency = CurrencyDisplay::getInstance( );
		$this->assignRef('currency', $currency);
		}

		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		$showBasePrice = Permissions::getInstance()->check('admin'); //todo add config settings
		$this->assignRef('showBasePrice', $showBasePrice);

	    shopFunctionsF::setLastVisitedCategoryId($categoryId);

// 	    if(empty($category->category_template)){
// 	    	$catTpl = VmConfig::get('categorytemplate');
// 	    	vmdebug('category_template',$catTpl);
// 	    } else {
// 	    	$catTpl = $category->category_template;
// 	    }
	    shopFunctionsF::setVmTemplate($this,$category->category_template,0,$category->category_layout);

		parent::display($tpl);
	}
}


//no closing tag
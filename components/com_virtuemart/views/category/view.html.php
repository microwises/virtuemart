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
		VmConfig::jPrice();

		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();

		/* Set the helper path */
		$this->addHelperPath(JPATH_VM_ADMINISTRATOR.DS.'helpers');

		/* Load helpers */
		$this->loadHelper('image');

		$categoryModel = $this->getModel('category');
		$productModel = $this->getModel('productdetails');
	    $categoryId = JRequest::getInt('category_id', 0);
	    $vendorId = 1;

	    $category = $categoryModel->getCategory($categoryId);
		if ($category->parents) {
			foreach ($category->parents as $c){
				$pathway->addItem($c->category_name,JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$c->category_id));
			}
		}
		if($category->children)	$categoryModel->addImagesToCategories($category->children);

	    /* Add the category name to the pathway */
		//$pathway->addItem($category->category_name);
	    $this->assignRef('category', $category);

		/* Set Canonic link */
		$document->addHeadLink( JRoute::_('index.php?option=com_virtuemart&view=category&category_id='.$categoryId) , 'canonical', 'rel', '' );

	    /* Set the titles */
		$document->setTitle($category->category_name);
		/* set keyword */
		if ($keyword = JRequest::getVar('keyword', '')) {
			$pathway->addItem($keyword);
			$document->setTitle($category->category_name.' '.$keyword);
			$this->assignRef('keyword', $keyword);
		}

	    /* Load the products in the given category */
	    $products = $productModel->getProductsInCategory($categoryId);
	    $productModel->addImagesToProducts($products);
	    $this->assignRef('products', $products);

	    $total = $productModel->getTotalProductsInCategory($categoryId);
	    $this->assignRef('total', $total);

	    $pagination = $productModel->getPagination($categoryId);
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
			$document->setMetaData('title', $category->category_description);  //Maybe better category_name
		}
		if ($mainframe->getCfg('MetaAuthor') == '1') {
			$document->setMetaData('author', $category->metaauthor);
		}

		if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
		$showBasePrice = Permissions::getInstance()->check('admin'); //todo add config settings
		$this->assignRef('showBasePrice', $showBasePrice);

	    shopFunctionsF::setLastVisitedCategoryId($categoryId);

	    if(empty($category->category_template)){
	    	$catTpl = VmConfig::get('categorytemplate');
	    }else {
	    	$catTpl = $category->category_template;
	    }
	    shopFunctionsF::setVmTemplate($this,$catTpl,0,$category->category_layout);

		parent::display($tpl);
	}
}


//no closing tag
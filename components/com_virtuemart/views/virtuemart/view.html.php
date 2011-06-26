<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
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
jimport( 'joomla.application.component.view');

/**
 * Default HTML View class for the VirtueMart Component
* @todo Find out how to use the front-end models instead of the backend models
 */
class VirtueMartViewVirtueMart extends JView {

	var $mediaModel = null;

	public function display($tpl = null) {

		/* MULTI-X
	    * $this->loadHelper('vendorHelper');
	    * $vendorModel = new Vendor;
	    * $vendor = $vendorModel->getVendor();
	    * $this->assignRef('vendor',	$vendor);
	    */
		$vendorId = JRequest::getInt('vendorid', 1);

	    $vendorModel = $this->getModel('vendor');

	    $vendorModel->setId(1);
	    $vendor = $vendorModel->getVendor();
	    $this->assignRef('vendor',$vendor);

		if(!VmConfig::get('shop_is_offline',0)){

			$categoryModel = $this->getModel('category');
			$productModel = $this->getModel('product');

		    $categoryId = JRequest::getInt('catid', 0);
	        $categoryChildren = $categoryModel->getChildCategoryList($vendorId, $categoryId);
	        $categoryModel->addImages($categoryChildren);

	        $this->assignRef('categories',	$categoryChildren);

	       // if(!class_exists('calculationHelper'))require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'calculationh.php');

	        /* Load the recent viewed products */
	        if (VmConfig::get('show_recent', 1)) {
	        	$recentProductIds = shopFunctionsF::getRecentProductIds();
				$recentProducts = $productModel->getProducts($recentProductIds);

	        	$productModel->addImages($recentProducts);
	        	$this->assignRef('recentProducts', $recentProducts);
	        }

	        if (VmConfig::get('show_featured', 1)) {
				$featuredProducts = $productModel->getProductListing('featured', 5);
				$productModel->addImages($featuredProducts);
				$this->assignRef('featuredProducts', $featuredProducts);
			}

			if (VmConfig::get('show_latest', 1)) {
				$latestProducts = $productModel->getProductListing('latest', 5); 
				//if(empty($latestProducts)) $latestProducts = 0;
				$productModel->addImages($latestProducts);
				//$latestProducts = array();
				$this->assignRef('latestProducts', $latestProducts);
			}

	        if (VmConfig::get('show_topTen', 1)) {
				$toptenProducts = $productModel->getProductListing('topten', 5);
				$productModel->addImages($toptenProducts);
				$this->assignRef('toptenProducts', $toptenProducts);
			}

			if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
			$showBasePrice = Permissions::getInstance()->check('admin'); //todo add config settings
			$this->assignRef('showBasePrice', $showBasePrice);

	//		$layoutName = VmConfig::get('vmlayout','default');

			$layout = VmConfig::get('vmlayout','default');
			$this->setLayout($layout);

//			if(!class_exists('CurrencyDisplay')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'currencydisplay.php');
//		    $this->assignRef('currencyDisplay',CurrencyDisplay::getCurrencyDisplay());

		} else {
			$this->setLayout('off_line');
		}

		/* Set the titles */
		$document = JFactory::getDocument();

		//Todo this may not work everytime as expected, because the error must be set in the redirect links.
	   	if(JRequest::getInt('error')){
			$head = $document->getHeadData();
			$head['title'] = JText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');	
			$document->setHeadData($head);
		} else {
			$document->setTitle(JText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND').JText::sprintf('COM_VIRTUEMART_HOME',$vendor->vendor_store_name));
		}
		


		$template = VmConfig::get('vmtemplate','default');
		if (is_dir(JPATH_THEMES.DS.$template)) {
			$mainframe = JFactory::getApplication();
			$mainframe->set('setTemplate', $template);
		}

		parent::display($tpl);

	}

}
// pure php no closing tag
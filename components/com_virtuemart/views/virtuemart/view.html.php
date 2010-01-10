<?php

jimport( 'joomla.application.component.view');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'libraries'.DS.'CategoryUtils.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'libraries'.DS.'ProductUtils.php');


/**
 * Default HTML View class for the VirtueMart Component
 */
class VirtueMartViewVirtueMart extends JView {
	
	public function display($tpl = null) {	  	    
	
		$categoryModel = $this->getModel('productcategory');
		$productModel = $this->getModel('product');

	    $vendorId = JRequest::getInt('vendorid', 1);
	    /* MULTI-X
	    * $this->loadHelper('vendorHelper');
	    * $vendorModel = new Vendor;
	    * $vendor = $vendorModel->getVendor($vendorId); 	    
	    * $this->assignRef('vendor',	$vendor);
	    */
	    
	    $categoryId = JRequest::getInt('catid', 0);
        $categoryChildren = $categoryModel->getChildCategoryList($vendorId, $categoryId);
        $this->assignRef('categories',	$categoryChildren);
        
        /* Load the recent viewed products */
        $this->assignRef('recentProducts', $productModel->getRecentProducts());
        
        if (Vmconfig::getVar('showFeatured', 1)) {
			$featuredProducts = $productModel->getGroupProducts('featured', $vendorId, '', 5);	
			$this->assignRef('featuredProducts', $featuredProducts);
		}
		
		if (Vmconfig::getVar('showlatest', 1)) {
			$latestProducts = $productModel->getGroupProducts('latest', $vendorId, '', 5);
			$this->assignRef('latestProducts', $latestProducts);
		}
		
		parent::display($tpl);

	}
}
?>
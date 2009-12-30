<?php

jimport( 'joomla.application.component.view');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'libraries'.DS.'CategoryUtils.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'libraries'.DS.'ProductUtils.php');


/**
 * Default HTML View class for the VirtueMart Component
 */
class VirtueMartViewVirtueMart extends JView
{
	
	function display($tpl = null)
	{	  	    
//	    $vendorModel =& $this->getModel('vendor'); 	    
		$categoryModel =& $this->getModel('category');
		$productModel =& $this->getModel('product');
	
	    $vendorId = JRequest::getInt('vendorid', 1);
//	    $vendor =& $vendorModel->getVendor($vendorId); 	    
	    $this->assignRef('vendor',	$vendor);
	    
	    $categoryId = JRequest::getInt('catid', 0);
        $categoryChildren = $categoryModel->getChildCategoryList($vendorId, $categoryId);	
        $this->assignRef('categories',	$categoryChildren);
        
        $featuredProducts = $productModel->getFeaturedProducts($vendorId, '', 5);	
        $this->assignRef('featuredProducts', $featuredProducts);        
		
		//parent::display($tpl);
		$this->useVirtuemartFrontend();

	}
	
	
	function useVirtuemartFrontend()
	{
//		echo('In the Frontend the JPATH_COMPONENT is: '.JPATH_COMPONENT);
	    include(JPATH_COMPONENT.DS.'virtuemart.php');
	    
	 }
}

?>
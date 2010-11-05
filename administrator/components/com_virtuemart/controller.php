<?php
/**
 * @package	    VirtueMart
 */

jimport('joomla.application.component.controller');

/**
 * VirtueMart default administrator controller
 *
 * @package		VirtueMart
 */
class VirtuemartController extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function display()
	{	
//		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'permissions.php');

		$document = JFactory::getDocument();	    
		$viewName = JRequest::getVar('view', '');
		$viewType = $document->getType();
		$view =& $this->getView($viewName, $viewType);

		// Push a model into the view					
		$model =& $this->getModel( 'virtuemart' );
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}	    
		
		include( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'admin.vm.old.php' );	    

	}
	
	
	function redirectToProductPriceList()
	{
	    $nomenu = JRequest::getInt('no_menu', 0);
	    $limitstart = JRequest::getInt('limitstart', 0);
        $productParentId = JRequest::getInt('product_parent_id', 0);
        $productId = JRequest::getInt('product_id', 0);    
	    
	    
        $newURL = 'index2.php?option=com_virtuemart&page=product.product_price_list&product_id=' . $productId;
        $newURL .= '&product_parent_id=' . $productParentId . '&limitstart=' . $limitstart . '&return_args=&no_menu=' . $nomenu;
	    $this->setRedirect($newURL);	    	    	    
	}
	
	
	function redirectToAddProductTypeForm()
	{
	    $nomenu = JRequest::getInt('no_menu', 0);
	    $limitstart = JRequest::getInt('limitstart', 0);
        $productParentId = JRequest::getInt('product_parent_id', 0);
        $productId = JRequest::getInt('product_id', 0);        
	    
        $newURL = 'index2.php?option=com_virtuemart&page=product.product_product_type_form&product_id=' . $productId;
        $newURL .= '&product_parent_id=' . $productParentId . '&limitstart=' . $limitstart . '&return_args=&no_menu=' . $nomenu;
	    $this->setRedirect($newURL);	    	    	    
	}	
	
	
	function redirectToAddChildProductForm()
	{
	    $nomenu = JRequest::getInt('no_menu', 0);
	    $limitstart = JRequest::getInt('limitstart', 0);
        $productParentId = JRequest::getInt('product_parent_id', 0);
        $productId = JRequest::getInt('product_id', 0);        
	    
        $newURL = 'index2.php?option=com_virtuemart&page=product.product_form&product_id=';
        $newURL .= '&product_parent_id=' . $productId . '&limitstart=' . $limitstart . '&return_args=&no_menu=' . $nomenu;
	    $this->setRedirect($newURL);	    	    	    
	}	
	
	
	function redirectToAddProductAttributeForm()
	{
	    $nomenu = JRequest::getInt('no_menu', 0);
	    $limitstart = JRequest::getInt('limitstart', 0);
        $productParentId = JRequest::getInt('product_parent_id', 0);
        $productId = JRequest::getInt('product_id', 0);        
	    
        $newURL = 'index2.php?option=com_virtuemart&page=product.product_attribute_form&product_id='. $productId;
        $newURL .= '&limitstart=' . $limitstart . '&return_args=&no_menu=' . $nomenu;
	    $this->setRedirect($newURL);	    	    	    
	}		
	
	
	function redirectToParentProductForm()
	{
	    $nomenu = JRequest::getInt('no_menu', 0);
	    $limitstart = JRequest::getInt('limitstart', 0);
        $productParentId = JRequest::getInt('product_parent_id', 0);
        $productId = JRequest::getInt('product_id', 0);        
	    
        $newURL = 'index2.php?option=com_virtuemart&page=product.product_form&product_id=' . $productParentId;
        $newURL .= '&limitstart=' . $limitstart . '&return_args=&no_menu=' . $nomenu;
	    $this->setRedirect($newURL);	    	    	    
	}	
	
	/**
	 * Redirect to the a given page name.
	 */
	function redirectToEditPage()		
	{    
	    $pageName = JRequest::getVar('page', '');
	    $pageName = str_replace('list','form', $pageName);  
        $newURL = 'index2.php?option=com_virtuemart&page=' . $pageName;
	    $this->setRedirect($newURL);	    	    	    
	}	
		
}
?>

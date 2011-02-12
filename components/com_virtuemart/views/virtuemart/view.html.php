<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage 
* @author
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
jimport( 'joomla.application.component.view');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'libraries'.DS.'CategoryUtils.php');
require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'libraries'.DS.'ProductUtils.php');


/**
 * Default HTML View class for the VirtueMart Component
* @todo Find out how to use the front-end models instead of the backend models
 */
class VirtueMartViewVirtueMart extends JView {
	
	public function display($tpl = null) {	  	    
	
		$categoryModel = $this->getModel('category');
		$productModel = $this->getModel('product');

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
	    
	    $this->assignRef('currencyDisplay',VirtueMartModelVendor::getCurrencyDisplay());
	    
	    $categoryId = JRequest::getInt('catid', 0);
        $categoryChildren = $categoryModel->getChildCategoryList($vendorId, $categoryId);
        $this->assignRef('categories',	$categoryChildren);
        
        /* Load the recent viewed products */
        $this->assignRef('recentProducts', $productModel->getRecentProducts());
        
        if (VmConfig::get('showFeatured', 1)) {
			$featuredProducts = & $productModel->getGroupProducts('featured', $vendorId, '', 5);	
			$this->assignRef('featuredProducts', $featuredProducts);
		}
		
		if (VmConfig::get('showlatest', 1)) {
			$latestProducts = & $productModel->getGroupProducts('latest', $vendorId, '', 5);
			$this->assignRef('latestProducts', $latestProducts);
		}
		
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'permissions.php');
		$showBasePrice = Permissions::getInstance()->check('admin'); //todo add config settings
		$this->assignRef('showBasePrice', $showBasePrice);
		
		
		$template = VmConfig::get('vmtemplate','default');
		if (is_dir(JPATH_THEMES.DS.$template)) {
			$mainframe = JFactory::getApplication();
			$mainframe->set('setTemplate', $template);
		}
		$layout = VmConfig::get('vmlayout','default');
		$this->setLayout($layout);

		
		parent::display($tpl);

	}
}
// pure php no closing tag
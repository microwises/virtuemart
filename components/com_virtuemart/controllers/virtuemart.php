<?php
/**
 * @package		VirtueMart
 */

jimport('joomla.application.component.controller');

/**
 * VirtueMart Component Controller
 *
 * @package		VirtueMart
 */
class VirtueMartControllerVirtuemart extends JController
{
    
	function __construct() {
		parent::__construct();
		if (VmConfig::get('vm_is_offline') == '1') {
		    JRequest::setVar( 'layout', 'offline' );	
	    }
	    else {
		    JRequest::setVar( 'layout', 'virtuemart' );	
	    }
	}
	
	function Virtuemart() {
		$view = $this->getView(JRequest::getVar('view', 'virtuemart'), 'html');
		
		// Push a model into the view		
		$this->addModelPath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart' . DS . 'models');
		/* Vendor functions */
		$view->setModel( $this->getModel( 'vendor', 'VirtuemartModel' ));
		/* Category functions */
		$view->setModel( $this->getModel( 'productcategory', 'VirtuemartModel' ));
		/* Product functions */
		$view->setModel( $this->getModel( 'product', 'VirtuemartModel' ));
		
		/* Set the layout */
		$view->setLayout(JRequest::getVar('layout'));
		
		/* Display it all */
		$view->display();
	}
}
?>

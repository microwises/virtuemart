<?php
/**
 * Inventory controller
 *
 * @package VirtueMart
 * @author RolandD
 * @link http://virtuemart.org
 * @version $Id: product.php 186 2009-09-10 14:12:18Z rolandd $
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * Inventory Controller
 *
 * @package    VirtueMart
 * @author RolandD
 */
class VirtuemartControllerInventory extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
		
		/* Redirects */
		$this->registerTask('unpublish','inventory');
		$this->registerTask('publish','inventory');
	}
	
	/**
	 * Shows the product list screen
	 */
	public function Inventory() {
		/* Create the view object */
		$view = $this->getView('inventory', 'html');
				
		/* Default model */
		$view->setModel( $this->getModel( 'inventory', 'VirtueMartModel' ), true );
		
		/* Product model */
		$view->setModel( $this->getModel( 'product', 'VirtueMartModel' ));
		
		/* Set the layout */
		$view->setLayout('inventory');
		
		/* Now display the view. */
		$view->display();
	}
}
?>

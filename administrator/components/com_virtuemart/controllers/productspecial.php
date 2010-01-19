<?php
/**
 * Product Special controller
 *
 * @package VirtueMart
 * @author RolandD
 * @link http://virtuemart.org
 * @version $Id$
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * Product Special Controller
 *
 * @package    VirtueMart
 * @author RolandD
 */
class VirtuemartControllerProductspecial extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * Shows the product list screen
	 */
	public function productSpecial() {
		/* Create the view object */
		$view = $this->getView('productSpecial', 'html');
				
		/* Default model */
		$view->setModel( $this->getModel( 'productSpecial', 'VirtueMartModel' ), true );
		
		/* Set the layout */
		$view->setLayout('productSpecial');
		
		/* Now display the view. */
		$view->display();
	}
}
?>

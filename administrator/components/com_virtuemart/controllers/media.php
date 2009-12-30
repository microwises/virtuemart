<?php
/**
 * Media controller
 *
 * @package VirtueMart
 * @author VirtueMart
 * @link http://virtuemart.org
 * @version $Id: product_files.php 93 2009-06-20 12:10:04Z rolandd $
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * Product Controller
 *
 * @package    VirtueMart
 */
class VirtuemartControllerMedia extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
		
		$this->registerTask('add','media');
		$this->registerTask('edit','media');
		$this->registerTask('remove','media');
		$this->registerTask('cancel','media');
		$this->registerTask('save','media');
		$this->registerTask('publish','media');
		$this->registerTask('unpublish','media');
	}
	
	/**
	 * Shows the product files list screen
	 */
	function Media() {
		/* Create the view object */
		$view = $this->getView('media', 'html');
		
		/* Default model */
		$view->setModel( $this->getModel( 'media', 'VirtueMartModel' ), true );
		
		/* Set the layout */
		switch (JRequest::getCmd('task')) {
			case 'add':
			case 'edit':
				$view->setLayout('media_edit');
				break;
			default:
				$view->setLayout('media');
				break;
		}
		
		/* Now display the view. */
		$view->display();
	}
	
	
}
?>

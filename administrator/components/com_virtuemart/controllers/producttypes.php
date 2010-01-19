<?php
/**
* Product types controller
*
* @package VirtueMart
* @author RolandD
* @link http://virtuemart.org
* @version $Id$
*/

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
* Product types Controller
*
* @package    VirtueMart
* @author RolandD
*/
class VirtuemartControllerProducttypes extends JController {
	/**
	* Method to display the view
	*
	* @access	public
	*/
	function __construct() {
		parent::__construct();
		
		/* Redirects */
		$this->registerTask('add','edit');
		$this->registerTask('cancel','productTypes');
	}
	
	/**
	 * Shows the product list screen
	 */
	public function productTypes() {
		/* Create the view object */
		$view = $this->getView('producttypes', 'html');
				
		/* Default model */
		$view->setModel( $this->getModel( 'producttypes', 'VirtueMartModel' ), true );
		
		/* Set the layout */
		$view->setLayout('producttypes');
		
		/* Now display the view. */
		$view->display();
	}
	
	/**
	 * Handle the edit task
	 *
     * @author RolandD
	 */
	function edit() {				
		JRequest::setVar('controller', 'producttypes');
		JRequest::setVar('view', 'producttypes');
		JRequest::setVar('layout', 'producttypes_edit');
		JRequest::setVar('hidemenu', 1);		
		
		parent::display();
	}
	
	/**
	* Save a product type
	*
	* @author RolandD
	*/
	public function Save() {
		$mainframe = Jfactory::getApplication();
		
		/* Load the view object */
		$view = $this->getView('producttypes', 'html');
		
		$model = $this->getModel('producttypes');
		$msgtype = '';
		if ($model->saveProductType()) $msg = JText::_('PRODUCTTYPE_SAVED_SUCCESSFULLY');
		else {
			$msg = JText::_('PRODUCTTYPE_NOT_SAVED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=producttypes&task=producttypes', $msg, $msgtype);
	}
	
	/**
	* Delete a discount
	* @author RolandD
	*/
	public function remove() {
		$mainframe = Jfactory::getApplication();
		
		/* Load the view object */
		$view = $this->getView('discounts', 'html');
		
		$model = $this->getModel('disocunts');
		$msgtype = '';
		if ($model->removeDiscount()) $msg = JText::_('DISOUNCT_REMOVED_SUCCESSFULLY');
		else {
			$msg = JText::_('DISCOUNT_NOT_REMOVED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		
		$mainframe->redirect('index.php?option=com_virtuemart&view=discounts&task=discounts', $msg, $msgtype);
	}
}
?>

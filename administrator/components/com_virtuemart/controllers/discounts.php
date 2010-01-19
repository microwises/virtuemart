<?php
/**
 * Discounts controller
 *
 * @package VirtueMart
 * @author RolandD
 * @link http://virtuemart.org
 * @version $Id$
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * Discounts Controller
 *
 * @package    VirtueMart
 * @author RolandD
 */
class VirtuemartControllerDiscounts extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
		
		/* Redirects */
		$this->registerTask('add','edit');
		$this->registerTask('cancel','discounts');
	}
	
	/**
	 * Shows the product list screen
	 */
	public function Discounts() {
		/* Create the view object */
		$view = $this->getView('discounts', 'html');
				
		/* Default model */
		$view->setModel( $this->getModel( 'discounts', 'VirtueMartModel' ), true );
		
		/* Set the layout */
		$view->setLayout('discounts');
		
		/* Now display the view. */
		$view->display();
	}
	
	/**
	 * Handle the edit task
	 *
     * @author RolandD
	 */
	function edit() {				
		JRequest::setVar('controller', 'discounts');
		JRequest::setVar('view', 'discounts');
		JRequest::setVar('layout', 'discounts_edit');
		JRequest::setVar('hidemenu', 1);		
		
		parent::display();
	}
	
	/**
	* Save a discount
	*
	* @author RolandD
	*/
	public function Save() {
		$mainframe = Jfactory::getApplication();
		
		/* Load the view object */
		$view = $this->getView('discounts', 'html');
		
		$model = $this->getModel('discounts');
		$msgtype = '';
		if ($model->saveDiscount()) $msg = JText::_('DISCOUNT_SAVED_SUCCESSFULLY');
		else {
			$msg = JText::_('DISCOUNT_NOT_SAVED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=discounts&task=discounts', $msg, $msgtype);
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

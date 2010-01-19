<?php
/**
* Product type parameters controller
*
* @package VirtueMart
* @author RolandD
* @link http://virtuemart.org
* @version $Id$
*/

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
* Product type parameters Controller
*
* @package    VirtueMart
* @author RolandD
*/
class VirtuemartControllerProducttypeparameters extends JController {
	/**
	* Method to display the view
	*
	* @access	public
	*/
	function __construct() {
		parent::__construct();
		
		/* Check if there is a product type ID, if not do not continue */
		if (JRequest::getInt('product_type_id', 0) < 1) {
			$mainframe = Jfactory::getApplication();
			$mainframe->redirect('index.php?option=com_virtuemart&view=producttypes', JText::_('NO_PRODUCT_TYPE_SET'), 'notice');
		}
		
		/* Redirects */
		$this->registerTask('add','edit');
		$this->registerTask('cancel','productTypeParameters');
	}
	
	/**
	 * Shows the product type parameters list screen
	 */
	public function productTypeParameters() {
		/* Create the view object */
		$view = $this->getView('producttypeparameters', 'html');
				
		/* Default model */
		$view->setModel( $this->getModel( 'producttypeparameters', 'VirtueMartModel' ), true );
		
		/* Set the layout */
		$view->setLayout('producttypeparameters');
		
		/* Now display the view. */
		$view->display();
	}
	
	/**
	 * Handle the edit task
	 *
     * @author RolandD
	 */
	function edit() {				
		JRequest::setVar('controller', 'producttypeparameters');
		JRequest::setVar('view', 'producttypeparameters');
		JRequest::setVar('layout', 'producttypeparameters_edit');
		JRequest::setVar('hidemenu', 1);		
		
		parent::display();
	}
	
	/**
	* Save a product type parameter
	*
	* @author RolandD
	*/
	public function Save() {
		$mainframe = Jfactory::getApplication();
		
		/* Load the view object */
		$view = $this->getView('producttypeparameters', 'html');
		
		$model = $this->getModel('producttypeparameters');
		$msgtype = '';
		if ($model->saveProductTypeParameter()) $msg = JText::_('PRODUCTTYPEPARAMETER_SAVED_SUCCESSFULLY');
		else {
			$msg = JText::_('PRODUCTTYPEPARAMETER_NOT_SAVED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=producttypeparameters&task=producttypeparameters&product_type_id='.JRequest::getInt('product_type_id', 0), $msg, $msgtype);
	}
	
	/**
	* Delete a Product Type Parameter
	* @author RolandD
	*/
	public function remove() {
		$mainframe = Jfactory::getApplication();
		
		/* Load the view object */
		$view = $this->getView('producttypeparameters', 'html');
		
		$model = $this->getModel('producttypeparameters');
		$msgtype = '';
		if ($model->removeProductTypeParameter()) $msg = JText::_('PRODUCTTYPEPARAMETER_REMOVED_SUCCESSFULLY');
		else {
			$msg = JText::_('PRODUCTTYPEPARAMETER_NOT_REMOVED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		
		$mainframe->redirect('index.php?option=com_virtuemart&view=producttypeparameters&task=producttypeparameters&product_type_id='.JRequest::getInt('product_type_id', 0), $msg, $msgtype);
	}
}
?>

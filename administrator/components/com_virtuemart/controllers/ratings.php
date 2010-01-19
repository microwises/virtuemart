<?php
/**
 * Review controller
 *
 * @package VirtueMart
 * @author RolandD
 * @link http://virtuemart.org
 * @version $Id$
 */

defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

jimport('joomla.application.component.controller');

/**
 * Review Controller
 *
 * @package    VirtueMart
 * @author RolandD
 */
class VirtuemartControllerRatings extends JController
{
	/**
	 * Method to display the view
	 *
	 * @access	public
	 */
	function __construct() {
		parent::__construct();
		
		/* Redirects */
		$this->registerTask('unpublish','publish');
		$this->registerTask('add','edit');
		$this->registerTask('cancel','ratings');
	}
	
	/**
	 * Shows the product list screen
	 */
	public function Ratings() {
		/* Create the view object */
		$view = $this->getView('ratings', 'html');
				
		/* Default model */
		$view->setModel( $this->getModel( 'ratings', 'VirtueMartModel' ), true );
		
		/* Set the layout */
		$view->setLayout('ratings');
		
		/* Now display the view. */
		$view->display();
	}
	
	/**
	* Publish/Unpublish a rating
	* @author RolandD
	*/
	public function publish() {
		$mainframe = Jfactory::getApplication();
		
		/* Load the view object */
		$view = $this->getView('ratings', 'html');
		
		$model = $this->getModel('ratings');
		$msgtype = '';
		if ($model->getPublish()) { 
			$msg = JText::_('RATING_'.strtoupper($this->getTask()).'_SUCCESSFULLY');
		}
		else {
			$msg = JText::_('RATING_NOT_'.strtoupper($this->getTask()).'_SUCCESSFULLY');
			$msgtype = 'error';
		}
		
		$mainframe->redirect('index.php?option=com_virtuemart&view=ratings&task=ratings', $msg, $msgtype);
	}
	
	/**
	 * Handle the edit task
	 *
     * @author RolandD
	 */
	function edit() {				
		JRequest::setVar('controller', 'ratings');
		JRequest::setVar('view', 'ratings');
		JRequest::setVar('layout', 'ratings_edit');
		JRequest::setVar('hidemenu', 1);		
		
		parent::display();
	}
	
	/**
	* Save a rating
	*
	* @author RolandD
	*/
	public function Save() {
		$mainframe = Jfactory::getApplication();
		
		/* Load the view object */
		$view = $this->getView('ratings', 'html');
		
		$model = $this->getModel('ratings');
		$msgtype = '';
		if ($model->saveRating()) $msg = JText::_('RATING_SAVED_SUCCESSFULLY');
		else {
			$msg = JText::_('RATING_NOT_SAVED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		$mainframe->redirect('index.php?option=com_virtuemart&view=ratings&task=ratings', $msg, $msgtype);
	}
	
	/**
	* Delete a user rating
	* @author RolandD
	*/
	public function remove() {
		$mainframe = Jfactory::getApplication();
		
		/* Load the view object */
		$view = $this->getView('ratings', 'html');
		
		$model = $this->getModel('ratings');
		$msgtype = '';
		if ($model->removeRating()) $msg = JText::_('RATING_REMOVED_SUCCESSFULLY');
		else {
			$msg = JText::_('RATING_NOT_REMOVED_SUCCESSFULLY');
			$msgtype = 'error';
		}
		
		$mainframe->redirect('index.php?option=com_virtuemart&view=ratings&task=ratings', $msg, $msgtype);
	}
}
?>

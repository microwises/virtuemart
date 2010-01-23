<?php
/**
*
* State controller
*
* @package	VirtueMart
* @subpackage State
* @author jseros, RickG, Max Milbers
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

// Load the controller framework
jimport('joomla.application.component.controller');

require_once( JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'state.php' );

class VirtueMartControllerState extends JController
{
    /**
	 * Method to display the view
	 *
	 * @access	public
	 * @author RickG, Max Milbers
	 */
	public function __construct() {
		parent::__construct();
		
		$document = JFactory::getDocument();				
		$viewType = $document->getType();
		$view = $this->getView('state', $viewType);		

		$stateModel = new VirtueMartModelState();
		
		// Push a model into the view
		if (!JError::isError($stateModel)) {
			$view->setModel($stateModel, true);
		}
	}
	
}
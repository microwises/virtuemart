<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage 
* @author RolandD
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

/**
* VirtueMart Component Controller
*
* @package VirtueMart
* @author RolandD
*/
class VirtueMartControllerProductdetails extends JController {
    
	public function __construct() {
		parent::__construct();
	}
	
	public function Productdetails() {
		/* Create the view */
		$view = $this->getView('productdetails', 'html');
		
		/* Add the default model */
		$view->setModel($this->getModel('productdetails','VirtuemartModel'), true);
		
		/* Add the category model */
		$view->setModel($this->getModel('category', 'VirtuemartModel'));
		
		/* Set the layout */
		$view->setLayout('productdetails');
		
		/* Display it all */
		$view->display();
	}
}
?>

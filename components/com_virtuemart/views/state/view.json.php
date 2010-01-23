<?php
/**
*
* Store View
*
* @package	VirtueMart
* @subpackage State
* @author jseros, RolandD
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

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the state
 *
 * @package	VirtueMart
 * @subpackage State
 * @author RolandD
 */
class VirtuemartViewState extends JView {
	
	function display($tpl = null, $countryId = 0) {
		$stateModel = $this->getModel();
		$countryId = JRequest::getInt('country_id');
		$states = $stateModel->getFullStates($countryId);
		echo json_encode($states);
	}
}
?>

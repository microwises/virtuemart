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
 * @author RolandD, jseros
 */
class VirtuemartViewState extends JView {
	
	function display($tpl = null) {
		$stateModel = $this->getModel();
		$states = array();
		
		//retrieving countries id
		$countries = JRequest::getString('country_id');
		$countries = explode(',', $countries);
		
		foreach($countries as $country){
			$states[$country] = $stateModel->getFullStates( JFilterInput::clean($country, 'INTEGER') );
		}
		echo json_encode($states);
	}
}
?>

<?php
/**
 * Store View
 *
 * @package	VirtueMart
 * @subpackage State
 * @author RickG
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

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
		$stateModel = $this->getModel('state');
		$states = $stateModel->getFullStates($countryId);
		echo json_encode($states);
	}
}
?>

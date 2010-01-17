<?php
/**
 * Store View
 *
 * @package	VirtueMart
 * @subpackage State
 * @author jseros
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
		$stateModel = $this->getModel();
		$countryId = JRequest::getInt('country_id');
		$states = $stateModel->getFullStates($countryId);
		echo json_encode($states);
	}
}
?>

<?php
/**
 * UpdatesMigration View
 *
 * @package	VirtueMart
 * @subpackage UpdatesMigration
 * @author Max Milbers
 * @copyright Copyright (c) 2009 VirtueMart Team. All rights reserved.
 */

jimport( 'joomla.application.component.view');

/**
 * HTML View class for maintaining the Installation. Updating of the files and imports of the database should be done here
 *
 * @package	VirtueMart
 * @subpackage UpdatesMigration
 * @author Max Milbers
 */
class VirtuemartViewUpdatesMigration extends JView
{	
    function display($tpl = null) {
	$this->loadHelper('adminMenu');
	$latestVersion = JRequest::getVar('latestverison', '');

	JToolBarHelper::title('Updating and data migration', 'vm_config_48');

	$this->loadHelper('connection');
	$this->loadHelper('image');
	$model = $this->getModel();

	$this->assignRef('checkbutton_style', $checkbutton_style);
	$this->assignRef('downloadbutton_style', $downloadbutton_style);
	$this->assignRef('latestVersion', $latestVersion);

	parent::display($tpl);
    }

}
?>

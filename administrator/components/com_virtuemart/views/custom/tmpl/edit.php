<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: media_edit.php 3049 2011-04-17 07:01:44Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
VmConfig::JvalideForm();
AdminMenuHelper::startAdminArea();
echo'<form name="adminForm" id="adminform" method="post" action="">';
echo '<fieldset class="adminform">';
$this->custom->addHidden('view','custom');
$this->custom->addHidden('task','');
$this->custom->addHidden(JUtility::getToken(),1);
if ($this->custom->custom_parent_id) $this->custom->addHidden('custom_parent_id',$this->custom->custom_parent_id);
$attribute_id = JRequest::getVar('attribute_id', '');
if(!empty($attribute_id)) $this->custom->addHidden('attribute_id',$attribute_id);
echo $this->custom->displayCustomFields('',$this->field_types);

echo '</fieldset>';
echo '</form>';
 AdminMenuHelper::endAdminArea(); ?>
<script type="text/javascript">
function submitbutton(pressbutton) {
	if (pressbutton=='cancel') submitform(pressbutton);
	if (jQuery('#adminform').validationEngine('validate')== true) submitform(pressbutton);
	else return false ;
}
</script>

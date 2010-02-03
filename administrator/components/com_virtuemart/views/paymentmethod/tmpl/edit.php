<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Calculation tool
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: edit.php 2281 2010-01-31 19:02:47Z Milbo $
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access'); 
AdminMenuHelper::startAdminArea();
JHTML::_('behavior.tooltip');
?>
<form action="index.php" method="post" name="adminForm">
<?php

$pane = JPane::getInstance('tabs', array('startOffset'=>0)); 
echo $pane->startPane('pane');

echo $pane->startPanel(JText::_('VM_ADMIN_PAYM_FORM'), 'paym_edit');
echo $this->loadTemplate('edit');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('VM_ADMIN_PAYM_CONFIGURATION'), 'paym_config');
echo $this->loadTemplate('config');
echo $pane->endPanel();

echo $pane->endPane();
?>

<!-- Hidden Fields -->
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="paymentmethod" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>
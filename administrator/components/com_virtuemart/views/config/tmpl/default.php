<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Config
* @author RickG
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
AdminMenuHelper::startAdminArea();
JHTML::_('behavior.tooltip');
?>
<form action="index.php" method="post" name="adminForm">
<?php

$pane = JPane::getInstance('tabs', array('startOffset'=>0));
echo $pane->startPane('pane');

//echo $pane->startPanel(JText::_('COM_VIRTUEMART_ADMIN_CFG_SYSTEMTAB'), 'system_panel');
//echo $this->loadTemplate('system');
//echo $pane->endPanel();
echo $pane->startPanel(JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOPTAB'), 'shop_panel');
echo $this->loadTemplate('shop');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOPFRONTTAB'), 'shopfrotnl_panel');
echo $this->loadTemplate('shopfront');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('COM_VIRTUEMART_ADMIN_CFG_PRICINGTAB'), 'pricing_panel');
echo $this->loadTemplate('pricing');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('COM_VIRTUEMART_ADMIN_CFG_CHECKOUTTAB'), 'checkout_panel');
echo $this->loadTemplate('checkout');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('COM_VIRTUEMART_ADMIN_CFG_DOWNLOADABLETAB'), 'downloads_panel');
echo $this->loadTemplate('downloads');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('COM_VIRTUEMART_ADMIN_CFG_FEEDTAB'), 'feed_panel');
echo $this->loadTemplate('feeds');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('COM_VIRTUEMART_ADMIN_CFG_SEF'), 'sef_panel');
echo $this->loadTemplate('sef');
echo $pane->endPanel();

echo $pane->endPane();
?>

<!-- Hidden Fields -->
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="config" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php AdminMenuHelper::endAdminArea(); ?>
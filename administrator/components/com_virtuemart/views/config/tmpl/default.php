<?php
defined('_JEXEC') or die('Restricted access'); 
AdminMenuHelper::startAdminArea();
JHTML::_('behavior.tooltip');
?>
<form action="index.php" method="post" name="adminForm">
<?php

$pane = JPane::getInstance('tabs', array('startOffset'=>0)); 
echo $pane->startPane('pane');

echo $pane->startPanel(JText::_('VM_ADMIN_CFG_SYSTEMTAB'), 'system_panel');
echo $this->loadTemplate('system');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('VM_ADMIN_CFG_SHOPTAB'), 'shop_panel');
echo $this->loadTemplate('shop');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('VM_ADMIN_CFG_SHOPFRONTTAB'), 'shopfrotnl_panel');
echo $this->loadTemplate('shopfront');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('VM_ADMIN_CFG_PRICINGTAB'), 'pricing_panel');
echo $this->loadTemplate('pricing');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('VM_ADMIN_CFG_CHECKOUTTAB'), 'checkout_panel');
echo $this->loadTemplate('checkout');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('VM_ADMIN_CFG_DOWNLOADABLETAB'), 'downloads_panel');
echo $this->loadTemplate('downloads');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('VM_ADMIN_CFG_FEEDTAB'), 'feed_panel');
echo $this->loadTemplate('feeds');
echo $pane->endPanel();

echo $pane->endPane();
?>

<!-- Hidden Fields -->
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="config" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?> 

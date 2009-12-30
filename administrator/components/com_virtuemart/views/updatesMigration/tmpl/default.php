<?php
defined('_JEXEC') or die('Restricted access');
AdminMenuHelper::startAdminArea();
JHTML::_('behavior.tooltip');
jimport('joomla.html.pane');
?>
<form action="index.php" method="post" name="adminForm">
<?php

$pane = JPane::getInstance('tabs', array('startOffset'=>0));
echo $pane->startPane('pane');

echo $pane->startPanel(JText::_('VM_UPDATE_CHECK_UPDATE_TAB'), 'update_panel');
echo $this->loadTemplate('update');
echo $pane->endPanel();
echo $pane->startPanel(JText::_('VM_UPDATE_CHECK_TOOLS_TAB'), 'tools_panel');
echo $this->loadTemplate('update');
echo $pane->endPanel();

echo $pane->endPane();
?>

<!-- Hidden Fields -->
<input type="hidden" name="task" value="" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="updatesMigration" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>

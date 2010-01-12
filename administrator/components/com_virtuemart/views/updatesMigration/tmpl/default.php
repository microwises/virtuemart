<?php
defined('_JEXEC') or die('Restricted access');
AdminMenuHelper::startAdminArea();
JHTML::_('behavior.tooltip');
jimport('joomla.html.pane');
?>
<form action="index.php" method="post" name="adminForm" enctype="multipart/form-data" >
<input type="hidden" name="task" value="" />
<?php
$pane = JPane::getInstance('tabs', array('startOffset'=>0));
echo $pane->startPane('pane');

echo $pane->startPanel(JText::_('VM_UPDATE_CHECK_UPDATE_TAB'), 'update_panel');
echo $this->loadTemplate('update');
echo $pane->endPanel();

echo $pane->startPanel(JText::_('VM_UPDATE_CHECK_UPDATE_TAB'), 'update_panel');
echo $this->loadTemplate('tools');
echo $pane->endPanel();

echo $pane->endPane();
?>

<!-- Hidden Fields -->
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="view" value="updatesMigration" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>

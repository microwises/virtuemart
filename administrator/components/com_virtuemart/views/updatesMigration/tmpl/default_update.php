<?php 
defined('_JEXEC') or die('Restricted access'); 
?>
<?php
$checkLatestVerisonLink = JROUTE::_('index.php?option=com_virtuemart&controller=updatesMigration&task=checkForLatestVersion');
$testVersionLink = JROUTE::_('index.php?option=com_virtuemart&controller=updatesMigration&task=testVersion&view=updatesMigration');
$installSampleLink = JROUTE::_('index.php?option=com_virtuemart&controller=updatesMigration&task=installSample&view=updatesMigration');
$updateVMTables10to11Link = JROUTE::_('index.php?option=com_virtuemart&controller=updatesMigration&task=updateVMTables10to11&view=updatesMigration');
$updateVMTables11to15Link = JROUTE::_('index.php?option=com_virtuemart&controller=updatesMigration&task=updateVMTables11to15&view=updatesMigration');

$linkDeleteALL =JROUTE::_('index2.php?option=com_virtuemart&controller=updatesMigration&view=updatesMigration&task=deleteAll');
$linkDeleteOnlyRestorable =JROUTE::_('index2.php?option=com_virtuemart&controller=updatesMigration&view=updatesMigration&task=deleteRestorable');
$linkDoNothing =JROUTE::_('index2.php');
?>
<br />
<table class="admintable">
    <tr>
	<td class="key"><?php echo JText::_('VM_UPDATE_CHECK_VERSION_INSTALLED'); ?></td>
	<td>
	    <h1 style="display:inline">
		<?php echo VmConfig::getInstalledVersion(); ?>
	    </h1>
	</td>
    </tr>
    <tr>
	<td class="key"><?php echo JText::_('VM_UPDATE_CHECK_LATEST_VERSION'); ?></td>
	<td>
	    <?php
	    if ($this->latestVersion) {
		echo "<h1 style='display:inline'>" . $this->latestVersion . "</h1>";
	    }
	    else {?>
	    <a href="<?php echo $checkLatestVerisonLink; ?>">
		&nbsp;[<?php echo JText::_('VM_UPDATE_CHECK_CHECKNOW'); ?>]</a>
		<?php
	    }
	    ?>
	    <?php
	    if ($this->latestVersion) {
		if (version_compare($this->latestVersion, VmConfig::getInstalledVersion(), '>') == 1) {
		    ?>
	    <input name="downloadbutton" id="downloadbutton" type="submit" value="<?php echo JText::_('VM_UPDATE_CHECK_DLUPDATE'); ?>" style="<?php echo $downloadbutton_style ?>font-weight:bold;" />
		    <?php
		}
		else {
		    // need something in the lanuage file here
		    echo '&nbsp;&nbsp;' . JText::_('VM_UPDATE_NONEWVERSION');
		}
	    }
	    ?>
	</td>
    </tr>
</table>
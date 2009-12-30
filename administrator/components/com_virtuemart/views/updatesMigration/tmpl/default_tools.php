<?php 
defined('_JEXEC') or die('Restricted access'); 
?>
<form name="adminForm" enctype="multipart/form-data">
<?php
		$testVersionLink = JROUTE::_('index.php?option=com_virtuemart&controller=updatesMigration&task=testVersion&view=updatesMigration');
		$installSampleLink = JROUTE::_('index.php?option=com_virtuemart&controller=updatesMigration&task=installSample&view=updatesMigration');
		$updateVMTables10to11Link = JROUTE::_('index.php?option=com_virtuemart&controller=updatesMigration&task=updateVMTables10to11&view=updatesMigration');
		$updateVMTables11to15Link = JROUTE::_('index.php?option=com_virtuemart&controller=updatesMigration&task=updateVMTables11to15&view=updatesMigration');

		$linkDeleteALL =JROUTE::_('index2.php?option=com_virtuemart&controller=updatesMigration&view=updatesMigration&task=deleteAll');
		$linkDeleteOnlyRestorable =JROUTE::_('index2.php?option=com_virtuemart&controller=updatesMigration&view=updatesMigration&task=deleteRestorable');
		$linkDoNothing =JROUTE::_('index2.php');
?>
    <br />
	<table class="adminlist">	
	<tr>
		<th class="title"><?php echo JText::_('VM_UPDATE_CHECK_VERSION_INSTALLED'); ?></th>
	    <td style="color:grey;font-size:18pt;text-align:center;">
	    	<?php echo VmConfig::getVar('version_release') . ' ' . VmConfig::getVar('version_dev_status'); ?>
	    </td> 
	</tr>
	<tr>
		<th class="title"><?php echo JText::_('VM_UPDATE_CHECK_LATEST_VERSION'); ?></th>
	    <td id="updateversioncontainer" >
	    	<img src="<?php echo VM_THEMEURL ?>images/indicator.gif" align="left" alt="<?php echo JText::_('VM_UPDATE_CHECK_CHECKING'); ?>" style="display:none;" id="checkingindicator" />
	    	<input name="checkbutton" id="checkbutton" type="button" value="<?php echo JText::_('VM_UPDATE_CHECK_CHECKNOW'); ?>" onclick="performUpdateCheck();" style="<?php echo $checkbutton_style ?>font-weight:bold;" />
	    	<input name="downloadbutton" id="downloadbutton" type="submit" value="<?php echo JText::_('VM_UPDATE_CHECK_DLUPDATE'); ?>" style="<?php echo $downloadbutton_style ?>font-weight:bold;" />
	    	<span id="versioncheckresult"><?php echo $this->latestVersion; ?></span>
	    </td> 		
	</tr>	
	</table>
		
		
		<br />
		<a href="<?php echo $testVersionLink; ?>">Test installed version by database tables</a><br />
		<br />
		<a href="<?php echo $installSampleLink; ?>">Install SampleData</a><br />
		<br />
		<a href="<?php echo $updateVMTables10to11Link; ?>">Execute Update Tables 1.0 - 1.1</a><br />
		<br />
		<a href="<?php echo $updateVMTables11to15Link; ?>">Execute Update Tables 1.1 - 1.5</a><br />
		<br />
		<p><?php echo JText::_('VM_DELETE_GENERAL_WARNING'); ?></p>
<<<<<<< .mine
=======
		<br />
		<a href="<?php echo $linkDeleteALL; ?>">Uninstall All</a><br />
		<br />
		<a href="<?php echo $linkDeleteOnlyRestorable; ?>">Delete only restorable data</a><br />
>>>>>>> .r2046
		
		
	<br />
	<table class="adminlist">	
	<tr>		
		<td align="center">
			<a onclick="alert('Please don\'t interrupt the next Step! \n It is essential for running VirtueMart.');" href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesMigration&task=installSampleData'); ?>">
				<img src="components/com_virtuemart/assets/images/icon_48/vm_install_48.png">
			</a>
			<br />
			Install Sample Data
		</td>
		<td align="center">
			<a onclick="alert('Please don\'t interrupt the next Step! \n It is essential for running VirtueMart.');" href="<?php echo JROUTE::_('index.php?option=com_virtuemart&view=updatesMigration&task=installSampleData'); ?>">
				<img src="components/com_virtuemart/assets/images/icon_48/vm_install_48.png">
			</a>
			<br />
			Remove Virtuemart Data
		</td>		
	</tr>
	</table>	
	<?php 
	echo $this->pane->endPanel();
		
		echo $this->pane->startPanel( 'Upload a Patch', 'upload_patch' );
		?>
		 <div style="padding: 20px;">
		 <h2 class="vmicon vmicon32 vmicon-32-upload" name="patchupload">Upload a Patch Package</h2>
		  	<input type="file" name="uploaded_package" class="inputbox" />
		  	<br />
		  	<br />
		  	&nbsp;&nbsp;&nbsp;<input type="submit" value="Upload &amp; Preview" />
		  	<br />
		  	<br />
		</div>
		<?php
		echo $this->pane->endPanel();
		
		echo $this->pane->startPanel( 'Database Migration', 'Database_migration' );
		?>
		 <div style="padding: 20px;">
		 <h2 class="vmicon vmicon32 vmicon-32-upload" name="sqlupload">Upload a SQL File</h2>
		  	<input type="file" name="uploaded_sql" class="inputbox" />
		  	<br />
		  	<br />
		  	&nbsp;&nbsp;&nbsp;<input type="submit" value="Upload &amp; Preview" />
		  	<br />
		  	<br />
		</div>
		<?php
		
		echo $this->pane->endPanel();
		
		echo $this->pane->endPane(); 

		   ?>
		<script type="text/javascript">
		//<!--
		function performUpdateCheck() {
			form = document.adminForm;
			$("checkingindicator").setStyle("display", "inline");
			form.checkbutton.value="<?php echo JText::_('VM_UPDATE_CHECK_CHECKING'); ?>";
			var vRequest = new Json.Remote("<?php echo $mosConfig_live_site ?>/administrator/index2.php?option=com_virtuemart&task=checkForUpdate&page=admin.ajax_tools&only_page=1&no_html=1", 
												{
													method: 'get',
													onComplete: handleUpdateCheckResult
													}).send();
		}
		function handleUpdateCheckResult( o ) {
		
			$("checkingindicator").setStyle("display", "none");
			$("checkbutton").setStyle("display", "none");
		
			if( typeof o != "undefined" ) {
				$("versioncheckresult").setText( o.version_string );
				
				if( isNaN( o.version ) ) {
					$("checkbutton").setStyle("display", "");
					$("checkbutton").value= "<?php echo JText::_('VM_UPDATE_CHECK_CHECKNOW' ); ?>";
				}
				else if( o.version == "<?php echo number_format($VMVERSION->RELEASE, 2 ) ?>" ) {
					$("versioncheckresult").setStyle( "color", "green" );
				} 
				else if( o.version > "<?php echo number_format($VMVERSION->RELEASE, 2 ) ?>" ) {
					$("versioncheckresult").setStyle( "color", "red" );
					$("downloadbutton").setStyle("display", "");
				} else {
					$("versioncheckresult").setStyle( "color", "blue" );
				}
				$("versioncheckresult").setStyle( "font-size", "18pt" );
			} else { 
				form.checkbutton.value="<?php echo JText::_('VM_UPDATE_CHECK_CHECK'); ?>";
			}
		}
		//-->
		</script>
	<input type="hidden" name="controller" value="updatesMigration" />
	<input type="hidden" name="option" value="com_virtuemart" />
	<input type="hidden" name="view" value="updatesMigration" />       
</form>      
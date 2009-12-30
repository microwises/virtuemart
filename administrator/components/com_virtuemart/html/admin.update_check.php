<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: admin.update_check.php 1755 2009-05-01 22:45:17Z rolandd $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*
*/


//if( vmget($_SESSION,'vm_updatepackage') !== null ) {
if( JRequest::getVar( 'vm_updatepackage',null )!== null ) {
	include( PAGEPATH.'admin.update_preview.php');
	return;
}
//vmCommonHTML::loadMooTools();
require_once( CLASSPATH.'update.class.php');
if( !empty( $_SESSION['vmLatestVersion'] ) && version_compare( $VMVERSION->RELEASE, $_SESSION['vmLatestVersion']) === -1 ) {
	$checkbutton_style = 'display:none;';
	$downloadbutton_style = '';
} else {
	$checkbutton_style = '';
	$downloadbutton_style = 'display:none;';
}
$formObj = new formFactory( JText::_('VM_UPDATE_CHECK_LBL') );
$formObj->startForm('adminForm', 'enctype="multipart/form-data"');

vmUpdate::stepBar(1);
$tabs = new vmTabPanel(0, 0, 'versionCheck');
$tabs->startPane('versionCheckPane');
$tabs->startTab( JText::_('VM_UPDATE_CHECK_LBL'), 'update_check' );
?>
<table class="adminlist">
  <tr>
    <th class="title"><?php echo JText::_('VM_UPDATE_CHECK_VERSION_INSTALLED'); ?></th>
    <th class="title"><?php echo JText::_('VM_UPDATE_CHECK_LATEST_VERSION'); ?></th>
  </tr>
  <tr>
    <td style="color:grey;font-size:18pt;text-align:center;"><?php echo $VMVERSION->RELEASE ?></td>
    <td id="updateversioncontainer" >
    	<img src="<?php echo VM_THEMEURL ?>images/indicator.gif" align="left" alt="<?php echo JText::_('VM_UPDATE_CHECK_CHECKING'); ?>" style="display:none;" id="checkingindicator" />
    	<input name="checkbutton" id="checkbutton" type="button" value="<?php echo JText::_('VM_UPDATE_CHECK_CHECKNOW'); ?>" onclick="performUpdateCheck();" style="<?php echo $checkbutton_style ?>font-weight:bold;" />
    	<input name="downloadbutton" id="downloadbutton" type="submit" value="<?php echo JText::_('VM_UPDATE_CHECK_DLUPDATE'); ?>" style="<?php echo $downloadbutton_style ?>font-weight:bold;" />
    	<span id="versioncheckresult"><?php echo JRequest::getVar('vmLatestVersion') ?></span>
    </td>
  </tr>
</table>
<?php
$tabs->endTab();

$tabs->startTab('Upload a Patch', 'upload_patch');
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
  $tabs->endTab();
  $tabs->endPane();
  
$formObj->finishForm('getupdatepackage', 'admin.update_preview');
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

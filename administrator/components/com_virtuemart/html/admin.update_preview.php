<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: admin.update_preview.php 1755 2009-05-01 22:45:17Z rolandd $
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

if( JRequest::getVar( 'vm_updatepackage',null )== null ) {
//	$mainframe->enqueueMessage('your message', 'error');
	JError::raiseWarning(JText::_('VM_UPDATE_NOTDOWNLOADED')." ".$extractdir,JText::_('VM_UPDATE_NOTDOWNLOADED')." ".$extractdir);
	return;
}

require_once( CLASSPATH.'update.class.php');

//$packageContents = vmUpdate::getPatchContents(vmget($_SESSION,'vm_updatepackage'));
$packageContents = vmUpdate::getPatchContents(JRequest::getVar( 'vm_updatepackage',false ));
if( $packageContents === false ) {
//	$vmLogger->flush(); // An Error should be logged before
	return;
}
vmCommonHTML::loadMooTools();

$formObj = new formFactory( JText::_('VM_UPDATE_PREVIEW_LBL') );
$formObj->startForm();

$vm_mainframe->addStyleDeclaration(".writable { color:green;}\n.unwritable { color:red;font-weight:bold; }");

vmUpdate::stepBar(2);
?>
<a name="warning"></a>
<div class="shop_warning">
	<span style="font-style: italic;"><?php echo JText::_('VM_UPDATE_WARNING_TITLE') ?></span><br />
	<?php echo JText::_('VM_UPDATE_WARNING_TEXT'); ?>
</div>
<div class="shop_info">
	<span style="font-style: italic;"><?php echo JText::_('VM_UPDATE_PATCH_DETAILS') ?></span><br />
	<ul>
		<li><?php echo JText::_('VM_UPDATE_PATCH_DESCRIPTION') ?>: <?php echo vmGet($packageContents,'description',null, VMREQUEST_ALLOWHTML ) ?></li>
		<li><?php echo JText::_('VM_UPDATE_PATCH_DATE') ?>: <?php echo $packageContents['releasedate'] ?></li>
	</ul>
</div>
<table class="adminlist">
	<thead>
	  <tr>
	    <th class="title"><?php echo JText::_('VM_UPDATE_PATCH_FILESTOUPDATE') ?></th>
	    <th class="title"><?php echo JText::_('VM_UPDATE_PATCH_STATUS') ?></th>
	  </tr>
	  </thead>
	  <tbody>
  <?php
$valid = true;
foreach( $packageContents['fileArr'] as $fileentry ) {
	$file = $fileentry['filename']; 
	
  	if( file_exists($mosConfig_absolute_path.'/'.$file)) {
  		$is_writable = is_writable($mosConfig_absolute_path.'/'.$file );
  		if( !$is_writable ) {
  			$is_writable = is_dir($mosConfig_absolute_path.'/'.$file) ? @chmod($mosConfig_absolute_path.'/'.$file, 0755):chmod($mosConfig_absolute_path.'/'.$file,0644);
  		}
  	} else {
  	  	if( $fileentry['copy_policy'] == 'only_if_exists') {
  			$is_writable = true;
  			continue;
  		}
  		$check_dir = $mosConfig_absolute_path.'/'.dirname($file);
  		$is_writable = is_writable($check_dir);
  		if( !$is_writable ) {
  			while( !file_exists($check_dir)) {
  				$check_dir = dirname( $check_dir );
				$is_writable = is_writable($check_dir ) || @chmod($checkdir, 0755);  				
  				
  			}
  			
  		}
  	}
  	if( !$is_writable ) {
  		$valid = false;
  	}
  	echo '<tr><td>'.$file.'</td>';
  	$class = $is_writable ? 'writable' : 'unwritable';
  	$msg = $is_writable ? JText::_('VM_UPDATE_PATCH_WRITABLE') : JText::_('VM_UPDATE_PATCH_UNWRITABLE');
  	echo '<td class="'.$class.'">'.$msg."</td></tr>\n";
  	
} ?>
  </tbody>
</table>

<?php
if( !empty($packageContents['queryArr'])) {
	echo '<table class="adminlist"><thead><tr><th class="title">' . JText::_('VM_UPDATE_PATCH_QUERYTOEXEC') . ':</th></tr></thead>';
	echo '<tbody>';
	foreach($packageContents['queryArr'] as $query) {
		echo '<tr><td><pre>'.$query. "</pre></td></tr>";
	}
	echo '</tbody></table>';
}
if( $valid ) {
	echo '<div align="center">
	<input type="checkbox" name="confirm_update" id="confirm_update">
		<label for="confirm_update">' . JText::_('VM_UPDATE_PATCH_CONFIRM_TEXT') . '</label>
		<br /><br />
	<input class="vmicon vmicon32 vmicon-32-apply" type="submit" onclick="return checkConfirm()" value="' . JText::_('VM_UPDATE_PATCH_APPLY') . '" name="submitbutton" />
	<input type="button" onclick="document.adminForm.page.value=\'store.index\';document.adminForm.func.value=\'removePatchPackage\';submitform(\'save\');" class="vmicon vmicon32 vmicon-32-cancel" value="'.JText::_('CMN_CANCEL').'" />
	</div>';
} else {
	echo '<div class="shop_error">' . JText::_('VM_UPDATE_PATCH_ERR_UNWRITABLE').'</div>';
}
$formObj->finishForm('applypatchpackage', 'admin.update_result');
 ?>
 <script type="text/javascript">
 function checkConfirm() {
 	if( document.adminForm.confirm_update.checked ) {
 		return true;
 	}
 	alert( "<?php echo JText::_('VM_UPDATE_PATCH_PLEASEMARK') ?>" );
 	return false;
 }
 </script>

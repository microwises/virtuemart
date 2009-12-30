<?php
if (! defined ( '_VALID_MOS' ) && ! defined ( '_JEXEC' ))
	die ( 'Direct Access to ' . basename ( __FILE__ ) . ' is not allowed.' );
/**
*
* @version $Id: installer.extension_form.class.php 27/09/2008
* @package VirtueMart
* @subpackage classes
* @copyright Copyright 2008 HoaNT-Vsmarttech for this class
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/ 

require_once (CLASSPATH . "installer.class.php");
?> 			
<div class="header">
<h2 style="margin: 0px;">VirtueMart Extension Installation</h2>
</div>
<?php
$tabs = new vmTabPanel ( 0, 0, 'list' );
$tabs->startPane ( 'listIntaller' );
$tabs->startTab ( 'Installation', 'install' );
?>
<div class="m">
<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm">
<table class="adminform">
	<tr>
		<th colspan="2"><?php 	echo JText::_ ( 'Upload Package File' );	?></th>
	</tr>
	<tr>
		<td width="120"><label for="install_package"><?php
		echo JText::_ ( 'Package File' );
		?>:</label></td>
		<td><input class="input_box" id="install_package"	name="install_package" type="file" size="57" />
		<input class="button" type="submit" value="<?php echo JText::_ ( 'Upload File' ); ?> &amp; <?php echo JText::_ ( 'Install' );	?>" />
		</td>
	</tr>
</table>
<input type="hidden" name="page" value="admin.extension_list" />
<input type="hidden" name="no_menu" value="1" /> 
<input type="hidden" name="task" value="" /> 
<input type="hidden" name="func" value="installExtension" /> 
	<input type="hidden" name="option" value="com_virtuemart" />
	<?php
	// Security Stuff
	echo '<input type="hidden" name="vmtoken" value="'.vmSpoofValue($GLOBALS['sess']->getSessionId()).'" />';
	echo JHTML::_ ( 'form.token' );
	?>
	</form>
</div>
<?php
$tabs->endTab ();
$tabs->endPane ();
?>
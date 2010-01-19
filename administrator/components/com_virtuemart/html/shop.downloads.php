<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
mm_showMyFileName( __FILE__ );

$mainframe->setPageTitle( JText::_('VM_DOWNLOADS_TITLE') );

if ($ps_function->userCanExecuteFunc('downloadRequest')) { ?>

	<h3><?php echo JText::_('VM_DOWNLOADS_TITLE') ?></h3>
    <img src="<?php echo VM_THEMEURL ?>images/downloads.gif" alt="downloads" border="0" align="middle" />
    <br/>
    <br/>
    <?php

  	if (ENABLE_DOWNLOADS == '1') { ?>
	  	<form method="get" action="<?php echo $mm_action_url ?>index.php" name="downloadForm">
		  	<p><?php echo JText::_('VM_DOWNLOADS_INFO') ?></p>
		  	<div align="center">
			    <input type="text" class="inputbox" value="<?php echo @$_GET['download_id'] ?>" size="32" name="download_id" />
			    <br /><br />
			    <input type="submit" onclick="if( document.downloadForm.download_id.value &lt; 12) { alert('<?php echo JText::_('CONTACT_FORM_NC',false) ?>');return false;} else return true;" class="button" value="<?php echo JText::_('VM_DOWNLOADS_START') ?>" />
			 </div>
		    <input type="hidden" name="func" value="downloadRequest" />
		    <input type="hidden" name="option" value="<?php echo VM_COMPONENT_NAME ?>" />
		    <input type="hidden" name="page" value="shop.downloads" />
		</form>
   		<?php
	}
}
else {
	$vmLogger->info( JText::_('NOT_AUTH',false)
								.($auth['user_id'] ? '' : ' ' . JText::_('DO_LOGIN',false)) 
								);
}

?>

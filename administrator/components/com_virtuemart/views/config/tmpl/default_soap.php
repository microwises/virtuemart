<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Config
* @author Mika
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default_soap.php 3770 2011-07-29 15:24:48Z electrocity $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>
<br />
<table width="100%">
    <tr><td valign="top" width="50%">
    
    
    	<?php /* ----------------1st blok CAT SETTINGS---------------------- */ ?>
    	
	    <fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_CAT_WS_SETTINGS') ?></legend>
		<table class="admintable">

		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_ws_cat_on', $this->config->get('soap_ws_cat_on')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_CACHE_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_CACHE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_ws_cat_cache_on', $this->config->get('soap_ws_cat_cache_on')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_GETCAT') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_getcat', $this->config->get('soap_auth_getcat')); ?>
			</td>
		    </tr>
		    
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ADDCAT') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_addcat', $this->config->get('soap_auth_addcat')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_UPDATECAT') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_upcat', $this->config->get('soap_auth_upcat')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_DELCAT') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_delcat', $this->config->get('soap_auth_delcat')); ?>
			</td>
		    </tr>
		    
		         <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_GET') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_cat_otherget', $this->config->get('soap_auth_cat_otherget')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_ADD') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_cat_otheradd', $this->config->get('soap_auth_cat_otheradd')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_UPDATE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_cat_otherupdate', $this->config->get('soap_auth_cat_otherupdate')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_DELETE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_cat_otherdelete', $this->config->get('soap_auth_cat_otherdelete')); ?>
			</td>
		    </tr>
		    
		
		    
	
		   
		    
		    

		</table>
	    </fieldset>



		<?php /* ----------------2nd blok USER SETTINGS ---------------------- */ ?>
		
		
	    <fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_USER_WS_SETTINGS') ?></legend>
		<table class="admintable">
			
			<tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_ws_user_on', $this->config->get('soap_ws_user_on')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_CACHE_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_CACHE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_ws_user_cache_on', $this->config->get('soap_ws_user_cache_on')); ?>
			</td>
		    </tr>
		    
		         <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_GETUSER') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_getuser', $this->config->get('soap_auth_getuser')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ADDUSER') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_adduser', $this->config->get('soap_auth_adduser')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_UPDATEUSER') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_upuser', $this->config->get('soap_auth_upuser')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_DELUSER') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_deluser', $this->config->get('soap_auth_deluser')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_GET') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_user_otherget', $this->config->get('soap_auth_user_otherget')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_ADD') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_user_otheradd', $this->config->get('soap_auth_user_otheradd')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_UPDATE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_user_otherupdate', $this->config->get('soap_auth_user_otherupdate')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_DELETE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_user_otherdelete', $this->config->get('soap_auth_user_otherdelete')); ?>
			</td>
		    </tr>
		 
			
		</table>
		</fieldset>

	</td><td valign="top" width="50%">

		<?php /* ----------------3rd blok ---------------------- */ ?>
		
		
	    <fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_PROD_WS_SETTINGS') ?></legend>
		<table class="admintable">
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_ws_prod_on', $this->config->get('soap_ws_prod_on')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_CACHE_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_CACHE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_ws_prod_cache_on', $this->config->get('soap_ws_prod_cache_on')); ?>
			</td>
		    </tr>
		    
		       <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_GETPROD') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_getprod', $this->config->get('soap_auth_getprod')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ADDPROD') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_addprod', $this->config->get('soap_auth_addprod')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_UPDATEPROD') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_upprod', $this->config->get('soap_auth_upprod')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_DELPROD') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_delprod', $this->config->get('soap_auth_delprod')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_GET') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_prod_otherget', $this->config->get('soap_auth_prod_otherget')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_ADD') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_prod_otheradd', $this->config->get('soap_auth_prod_otheradd')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_UPDATE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_prod_otherupdate', $this->config->get('soap_auth_prod_otherupdate')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_DELETE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_prod_otherdelete', $this->config->get('soap_auth_prod_otherdelete')); ?>
			</td>
		    </tr>
		    
		    
		    
        </table>
        </fieldset>
        
        
        <?php /* ----------------4nd blok ORDERS---------------------- */ ?>
        
        
		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ORDER_WS_SETTINGS') ?></legend>
        <table class="admintable">
          
          	<tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_ws_order_on', $this->config->get('soap_ws_order_on')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_CACHE_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_CACHE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_ws_order_cache_on', $this->config->get('soap_ws_order_cache_on')); ?>
			</td>
		    </tr>
		    
		             <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_GETORDER') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_getorder', $this->config->get('soap_auth_getorder')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ADDORDER') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_addorder', $this->config->get('soap_auth_addorder')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_UPDATEORDER') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_uporder', $this->config->get('soap_auth_uporder')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_DELORDER') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_delorder', $this->config->get('soap_auth_delorder')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_GET') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_order_otherget', $this->config->get('soap_auth_order_otherget')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_ADD') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_order_otheradd', $this->config->get('soap_auth_order_otheradd')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_UPDATE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_order_otherupdate', $this->config->get('soap_auth_order_otherupdate')); ?>
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_ALLOTHER_PROD_DELETE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_order_otherdelete', $this->config->get('soap_auth_order_otherdelete')); ?>
			</td>
		    </tr>
		    
		    
		</table>
	    </fieldset>
	
		
	</td></tr>
</table>



<?php /* ----------------SQL blok ---------------------- */ ?>
<table width="100%">
    <tr><td valign="top">
		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_SQL_WS_SETTINGS') ?></legend>
		<table class="admintable">
	    	
	    	
	    	<tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_ws_sql_on', $this->config->get('soap_ws_sql_on')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_CACHE_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_CACHE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_ws_sql_cache_on', $this->config->get('soap_ws_sql_cache_on')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_EXECSQL') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_execsql', $this->config->get('soap_auth_execsql')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_EXECSELECT') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_execsql_select', $this->config->get('soap_auth_execsql_select')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_EXECINSERT') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_execsql_insert', $this->config->get('soap_auth_execsql_insert')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ONOFFAUTH_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_AUTH_EXECUPDATE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_auth_execsql_update', $this->config->get('soap_auth_execsql_update')); ?>
			</td>
		    </tr>
	

	    </table>
	    </fieldset>
	</td></tr>
</table>



<?php /* ----------------Custom service ---------------------- */ ?>
<table width="100%">
    <tr><td valign="top">
		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_CUSTOM_SETTINGS') ?></legend>
		<table class="admintable">
	    	
	    	
	    	<tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_CUSTOM_SETTINGS_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_ws_custom_on', $this->config->get('soap_ws_custom_on')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_CACHE_TIP'); ?>" >
			    <label for="pdf_button_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_ENABLE_WS_CACHE') ?></label>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('soap_ws_custom_cache_on', $this->config->get('soap_ws_custom_cache_on')); ?>
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_CUSTOM_WSDL_TIP'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_CUSTOM_WSDL') ?>
				</span>
				</td>
				<td>
				<input type="text" name="wsdl_custom" class="inputbox" value="<?php echo $this->config->get('wsdl_custom') ?>" />
			</td>
			</tr>
			
			<tr>
			<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_CUSTOM_ENDPOINT_TIP'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_CUSTOM_ENDPOINT') ?>
				</span>
				</td>
				<td>
				<input type="text" name="EP_custom" class="inputbox" width="300" value="<?php echo $this->config->get('EP_custom') ?>" />
			</td>
			</tr>
		    
	

	    </table>
	    </fieldset>
	</td></tr>
</table>



<?php /* ----------------WSDL BLOK ---------------------- */ ?>
<table width="100%">
    <tr><td valign="top">
		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SOAP_WSDL_VIEW') ?></legend>
		<table class="admintable">
	    	
	    	<?php 
			 echo "<ul>";
			 echo "<li><a href=./components/com_virtuemart/services/VM_CategoriesWSDL.php > View Categories WSDL definition </a></li>";
			 echo "<li><a href=./components/com_virtuemart/services/VM_ProductWSDL.php > View Product WSDL definition</a></li>";
			 echo "<li><a href=./components/com_virtuemart/services/VM_OrderWSDL.php > View Orders WSDL definition</a></li>";
			 echo "<li><a href=./components/com_virtuemart/services/VM_UsersWSDL.php > View Customers WSDL definition</a></li>";
			 echo "<li><a href=./components/com_virtuemart/services/VM_SQLQueriesWSDL.php > View SQL WSDL definition</a></li>";
			 echo "<li><a href=./components/com_virtuemart/services/VM_CustomizedWSDL.php > View Customized WSDL definition</a></li>";
			
			 echo "</ul>";
			
			?>
	    
	

	    </table>
	    </fieldset>
	</td></tr>
</table>



<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: admin.show_cfg.php 1771 2009-05-11 23:00:55Z macallf $
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
require_once(CLASSPATH.'ps_checkout.php');

global $acl, $VM_BROWSE_ORDERBY_FIELDS, $VM_MODULES_FORCE_HTTPS, $database;
if( !isset( $VM_BROWSE_ORDERBY_FIELDS )) { $VM_BROWSE_ORDERBY_FIELDS = array(); }
if( !isset( $VM_MODULES_FORCE_HTTPS )) { $VM_MODULES_FORCE_HTTPS = array('account','checkout'); }
if( !isset( $VM_CHECKOUT_MODULES )) {
	$VM_CHECKOUT_MODULES = array('CHECK_OUT_GET_SHIPPING_ADDR' => array('order' => 1,'enabled'=>1),
	'CHECK_OUT_GET_SHIPPING_METHOD' => array('order' => 2,'enabled'=>1),
	'CHECK_OUT_GET_PAYMENT_METHOD' => array('order' => 3,'enabled'=>1),
	'CHECK_OUT_GET_FINAL_CONFIRMATION' => array('order' => 4,'enabled'=>1)
	);
}

$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;

// Compose the Access DropDown List, for the first time used for setting Price Acess
$fieldname = 'group_id';
if( $_VERSION->PRODUCT == 'Joomla!' && $_VERSION->RELEASE >= 1.5 ) {
	$fieldname = 'id';
}
$db->query( 'SELECT `'.$fieldname.'` FROM #__core_acl_aro_groups WHERE name=\''.VM_PRICE_ACCESS_LEVEL.'\'' );
$db->next_record();
$gtree = $acl->get_group_children_tree( null, 'USERS', false );
$access_group_list = vmCommonHTML::selectList( $gtree, 'conf_VM_PRICE_ACCESS_LEVEL', 'size="4"', 'value', 'text', $db->f($fieldname), true );

$title = '&nbsp;&nbsp;<img src="'. ADMINICONURL .'icon_48'.DS.'vm_config_48.png" align="middle" border="0" alt="'.JText::_('VM_CONFIG').'" />&nbsp;';
$title .= JText::_('VM_CONFIG');

//First create the object and let it print a form heading
$formObj = &new formFactory( $title );
//Then Start the form
$formObj->startForm();

$ps_html->writableIndicator( $mosConfig_absolute_path.'/administrator/components/com_virtuemart/virtuemart.cfg.php' );

$tabs = new vmTabPanel(1, 1, "vmconfiguration");
$tabs->startPane("content-pane");
$tabs->startTab( JText::_('VM_ADMIN_CFG_GLOBAL'), "global-page");

?>
<br />
<table class="adminlist"><tr><td>

<fieldset style="width:48%;float:left;">
	<legend><?php echo JText::_('VM_ADMIN_CFG_GLOBAL') ?></legend>
	<table class="adminform">
		<tr>
			<td class="labelcell">
				<label for="conf_PSHOP_IS_OFFLINE"><?php echo JText::_('VM_ADMIN_CFG_SHOP_OFFLINE',false) ?></label>
				
			</td>
			<td>
				<input type="checkbox" id="conf_PSHOP_IS_OFFLINE" name="conf_PSHOP_IS_OFFLINE" class="inputbox" <?php if (PSHOP_IS_OFFLINE == 1) echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td><?php echo JText::_('VM_ADMIN_CFG_SHOP_OFFLINE_TIP') ?></td>
		</tr>
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_SHOP_OFFLINE_MSG') ?>:</td>
			<td colspan="2">
				<textarea rows="8" cols="35" name="conf_PSHOP_OFFLINE_MESSAGE"><?php echo shopMakeHtmlSafe(stripslashes(PSHOP_OFFLINE_MESSAGE)); ?></textarea>
			</td>
		</tr>  
		<tr>
			<td class="labelcell">
				<label for="conf_USE_AS_CATALOGUE"><?php echo JText::_('VM_ADMIN_CFG_USE_ONLY_AS_CATALOGUE') ?></label>
				
			</td>
			<td>
				<input type="checkbox" id="conf_USE_AS_CATALOGUE" name="conf_USE_AS_CATALOGUE" class="inputbox" <?php if (USE_AS_CATALOGUE == 1) echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td><?php echo JText::_('VM_ADMIN_CFG_USE_ONLY_AS_CATALOGUE_EXPLAIN') ?>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset style="width:48%;float:right;">
	<legend><?php echo JText::_('VM_ADMIN_CFG_PRICE_CONFIGURATION') ?></legend>
	<table class="adminform">
		<tr>
			<td class="labelcell">
				<label for="conf__SHOW_PRICES"><?php echo JText::_('VM_ADMIN_CFG_SHOW_PRICES') ?></label>
				
			</td>
			<td>
				<input type="checkbox" id="conf__SHOW_PRICES" name="conf__SHOW_PRICES" class="inputbox" <?php if (_SHOW_PRICES == 1) echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_SHOW_PRICES_EXPLAIN') ) ?></td>
		</tr>
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_PRICE_ACCESS_LEVEL') ?></td>
			<td><?php
			echo '<input type="checkbox" value="Y" name="use_price_access" onclick="document.adminForm.conf_VM_PRICE_ACCESS_LEVEL.disabled = document.adminForm.conf_VM_PRICE_ACCESS_LEVEL.disabled ? false : true;" id="use_price_access"';
			if( VM_PRICE_ACCESS_LEVEL != '0' ) { echo ' checked="checked"'; }
			echo ' /> ';
			echo '<label for="use_price_access"><strong>'.JText::_('VM_CFG_ENABLE_FEATURE') .'</strong></label><br />';
			echo $access_group_list;
				?>
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_PRICE_ACCESS_LEVEL_TIP') ) ?></td>
		</tr>

		<tr>
			<td class="labelcell">
				<label for="conf_VM_PRICE_SHOW_WITHOUTTAX"><?php echo JText::_('VM_ADMIN_CFG_PRICE_SHOW_WITHOUTTAX') ?></label>

			</td>
			<td>
				<input type="checkbox" id="conf_VM_PRICE_SHOW_WITHOUTTAX" name="conf_VM_PRICE_SHOW_WITHOUTTAX" class="inputbox" <?php if (defined('VM_PRICE_SHOW_WITHOUTTAX')) { if (VM_PRICE_SHOW_WITHOUTTAX == 1) { echo "checked=\"checked\"";}} ?> value="1" />
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_PRICE_SHOW_WITHOUTTAX_TIP') ) ?></td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf_VM_PRICE_SHOW_EXCLUDINGTAX"><?php echo JText::_('VM_ADMIN_CFG_PRICE_SHOW_EXCLUDINGTAX') ?></label>

			</td>
			<td>
				<input type="checkbox" id="conf_VM_PRICE_SHOW_EXCLUDINGTAX" name="conf_VM_PRICE_SHOW_EXCLUDINGTAX" class="inputbox" <?php if (defined('VM_PRICE_SHOW_EXCLUDINGTAX')) { if (VM_PRICE_SHOW_EXCLUDINGTAX == 1) { echo "checked=\"checked\""; }} ?> value="1" />
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_PRICE_SHOW_EXCLUDINGTAX_TIP') ) ?></td>
		</tr>

		<tr>
			<td class="labelcell">
				<label for="conf_VM_PRICE_SHOW_WITHTAX"><?php echo JText::_('VM_ADMIN_CFG_PRICE_SHOW_WITHTAX') ?></label>

			</td>
			<td>
				<input type="checkbox" id="conf_VM_PRICE_SHOW_WITHTAX" name="conf_VM_PRICE_SHOW_WITHTAX" class="inputbox" <?php if (defined('VM_PRICE_SHOW_WITHTAX')) { if (VM_PRICE_SHOW_WITHTAX == 1) { echo "checked=\"checked\""; }} ?> value="1" />
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_PRICE_SHOW_WITHTAX_TIP') ) ?></td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf_VM_PRICE_SHOW_INCLUDINGTAX"><?php echo JText::_('VM_ADMIN_CFG_PRICE_SHOW_INCLUDINGTAX') ?></label>
				
			</td>
			<td>
				<input type="checkbox" id="conf_VM_PRICE_SHOW_INCLUDINGTAX" name="conf_VM_PRICE_SHOW_INCLUDINGTAX" class="inputbox" <?php if (VM_PRICE_SHOW_INCLUDINGTAX == 1) echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_PRICE_SHOW_INCLUDINGTAX_TIP') ) ?></td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf_VM_PRICE_SHOW_PACKAGING_PRICELABEL"><?php echo JText::_('VM_ADMIN_CFG_PRICE_SHOW_PACKAGING_PRICELABEL') ?>
				</label>
				
			</td>
			<td>
				<input type="checkbox" id="conf_VM_PRICE_SHOW_PACKAGING_PRICELABEL" name="conf_VM_PRICE_SHOW_PACKAGING_PRICELABEL" class="inputbox" <?php if (VM_PRICE_SHOW_PACKAGING_PRICELABEL == 1) echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_PRICE_SHOW_PACKAGING_PRICELABEL_TIP') ) ?></td>
		</tr>
	</table>
</fieldset>

<br style="clear:both;" />

<fieldset style="width:48%;float:left;">
	<legend><?php echo JText::_('VM_ADMIN_CFG_FRONTEND_FEATURES') ?></legend>
	<table class="adminform">
		<tr>
			<td class="labelcell">
				<label for="conf_VM_CONTENT_PLUGINS_ENABLE"><?php echo JText::_('VM_CFG_CONTENT_PLUGINS_ENABLE') ?></label>
				
			</td>
			<td>
				<input type="checkbox" id="conf_VM_CONTENT_PLUGINS_ENABLE" name="conf_VM_CONTENT_PLUGINS_ENABLE" class="inputbox" <?php if (@VM_CONTENT_PLUGINS_ENABLE == '1') echo "checked='checked'"; ?> value="1" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_CFG_CONTENT_PLUGINS_ENABLE_TIP') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf_PSHOP_COUPONS_ENABLE"><?php echo JText::_('VM_COUPONS_ENABLE') ?></label>
				
			</td>
			<td>
				<input type="checkbox" id="conf_PSHOP_COUPONS_ENABLE" name="conf_PSHOP_COUPONS_ENABLE" class="inputbox" <?php if (PSHOP_COUPONS_ENABLE == '1') echo "checked='checked'"; ?> value="1" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_COUPONS_ENABLE_EXPLAIN') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf_PSHOP_ALLOW_REVIEWS"><?php echo JText::_('VM_ADMIN_CFG_REVIEW') ?></label>
				
			</td>
			<td>
				<input type="checkbox" id="conf_PSHOP_ALLOW_REVIEWS" name="conf_PSHOP_ALLOW_REVIEWS" class="inputbox" <?php if (PSHOP_ALLOW_REVIEWS == '1') echo "checked='checked'"; ?> value="1" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_REVIEW_EXPLAIN') ) ?>
			</td>
		</tr>
		
		<tr>
			<td class="labelcell">
				<label for="conf_VM_REVIEWS_AUTOPUBLISH"><?php echo JText::_('VM_REVIEWS_AUTOPUBLISH') ?></label>
				
			</td>
			<td>
				<input type="checkbox" id="conf_VM_REVIEWS_AUTOPUBLISH" name="conf_VM_REVIEWS_AUTOPUBLISH" class="inputbox" <?php if (@VM_REVIEWS_AUTOPUBLISH == '1') echo "checked='checked'"; ?> value="1" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_REVIEWS_AUTOPUBLISH_TIP') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf_VM_REVIEWS_MINIMUM_COMMENT_LENGTH"><?php echo JText::_('VM_ADMIN_CFG_REVIEW_MINIMUM_COMMENT_LENGTH') ?></label>
				
			</td>
			<td>
				<input type="text" size="6" id="conf_VM_REVIEWS_MINIMUM_COMMENT_LENGTH" name="conf_VM_REVIEWS_MINIMUM_COMMENT_LENGTH" class="inputbox" value="<?php echo @intval(VM_REVIEWS_MINIMUM_COMMENT_LENGTH); ?>" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_REVIEW_MINIMUM_COMMENT_LENGTH_TIP') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf_VM_REVIEWS_MAXIMUM_COMMENT_LENGTH"><?php echo JText::_('VM_ADMIN_CFG_REVIEW_MAXIMUM_COMMENT_LENGTH') ?></label>
				
			</td>
			<td>
				<input type="text" size="6" id="conf_VM_REVIEWS_MAXIMUM_COMMENT_LENGTH" name="conf_VM_REVIEWS_MAXIMUM_COMMENT_LENGTH" class="inputbox" value="<?php echo @intval(VM_REVIEWS_MAXIMUM_COMMENT_LENGTH); ?>" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_REVIEW_MAXIMUM_COMMENT_LENGTH_TIP') ) ?>
			</td>
		</tr>

	</table>
</fieldset>

<fieldset style="width:48%;float:right;">
	<legend><?php echo JText::_('VM_ADMIN_CFG_TAX_CONFIGURATION') ?></legend>
	<table class="adminform">
		<tr>
			<td class="labelcell">
				<label for="conf_TAX_VIRTUAL"><?php echo JText::_('VM_ADMIN_CFG_VIRTUAL_TAX') ?></label>
				
			</td>
			<td align="left">
				<input type="checkbox" name="conf_TAX_VIRTUAL" id="conf_TAX_VIRTUAL" class="inputbox" <?php if (TAX_VIRTUAL == 1) echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_VIRTUAL_TAX_EXPLAIN') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_TAX_MODE') ?></td>
			<td>
				<select name="conf_TAX_MODE" class="inputbox">
					<option value="0" <?php if (TAX_MODE == 0) echo 'selected="selected"'; ?>>
					<?php echo JText::_('VM_ADMIN_CFG_TAX_MODE_SHIP') ?>
					</option>
					<option value="1" <?php if (TAX_MODE == 1) echo 'selected="selected"'; ?>>
					<?php echo JText::_('VM_ADMIN_CFG_TAX_MODE_VENDOR') ?>
					</option>
					<option value="17749" <?php if (TAX_MODE == 17749) echo 'selected="selected"'; ?>>
					<?php echo JText::_('VM_ADMIN_CFG_TAX_MODE_EU') ?>
					</option>
				</select>
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_TAX_MODE_EXPLAIN') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf_MULTIPLE_TAXRATES_ENABLE"><?php echo JText::_('VM_ADMIN_CFG_MULTI_TAX_RATE') ?></label>
				
			</td>
			<td>
				<input type="checkbox" id="conf_MULTIPLE_TAXRATES_ENABLE" name="conf_MULTIPLE_TAXRATES_ENABLE" class="inputbox" <?php if (MULTIPLE_TAXRATES_ENABLE == '1') echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_MULTI_TAX_RATE_EXPLAIN') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf_PAYMENT_DISCOUNT_BEFORE"><?php echo JText::_('VM_ADMIN_CFG_SUBSTRACT_PAYEMENT_BEFORE') ?></label>
				
			</td>
			<td>
				<input type="checkbox" id="conf_PAYMENT_DISCOUNT_BEFORE" name="conf_PAYMENT_DISCOUNT_BEFORE" class="inputbox" <?php if (PAYMENT_DISCOUNT_BEFORE == '1') echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_SUBSTRACT_PAYEMENT_BEFORE_EXPLAIN') ) ?>
			</td>
		</tr>
	</table>
</fieldset>
<br style="clear:both;" />
<fieldset>
	<legend><?php echo JText::_('VM_ADMIN_CFG_USER_REGISTRATION_SETTINGS') ?></legend>
	<table class="adminform">
		<tr>
			<td class="labelcell">
				<label for="conf_VM_REGISTRATION_TYPE"><?php echo JText::_('VM_CFG_REGISTRATION_TYPE') ?></label>
				
			</td>
			<td>
				<select id="conf_VM_REGISTRATION_TYPE" name="conf_VM_REGISTRATION_TYPE" class="inputbox">
					<option value="NORMAL_REGISTRATION"<?php if( @VM_REGISTRATION_TYPE == 'NORMAL_REGISTRATION' ) echo "selected=\"selected\""; ?>><?php echo JText::_('VM_CFG_REGISTRATION_TYPE_NORMAL_REGISTRATION') ?></option>
					<option value="SILENT_REGISTRATION"<?php if( @VM_REGISTRATION_TYPE == 'SILENT_REGISTRATION' ) echo "selected=\"selected\""; ?>><?php echo JText::_('VM_CFG_REGISTRATION_TYPE_SILENT_REGISTRATION') ?></option>
					<option value="OPTIONAL_REGISTRATION"<?php if( @VM_REGISTRATION_TYPE == 'OPTIONAL_REGISTRATION' ) echo "selected=\"selected\""; ?>><?php echo JText::_('VM_CFG_REGISTRATION_TYPE_OPTIONAL_REGISTRATION') ?></option>
					<option value="NO_REGISTRATION"<?php if( @VM_REGISTRATION_TYPE == 'NO_REGISTRATION' ) echo "selected=\"selected\""; ?>><?php echo JText::_('VM_CFG_REGISTRATION_TYPE_NO_REGISTRATION') ?></option>
				</select>
			</td> 
			<td><?php echo vmToolTip( JText::_('VM_CFG_REGISTRATION_TYPE_TIP') ) ?>
			</td>
		</tr>
		
		<tr>
			<td class="labelcell">
				<label for="conf_VM_SHOW_REMEMBER_ME_BOX"><?php echo JText::_('VM_SHOW_REMEMBER_ME_BOX') ?></label>
				
			</td>
			<td>
				<input type="checkbox" id="conf_VM_SHOW_REMEMBER_ME_BOX" name="conf_VM_SHOW_REMEMBER_ME_BOX" class="inputbox" <?php if (@VM_SHOW_REMEMBER_ME_BOX == "1") echo "checked=\"checked\""; ?> value="1" />
			</td> 
			<td><?php echo vmToolTip( JText::_('VM_SHOW_REMEMBER_ME_BOX_TIP') ) ?>
			</td>
		</tr>
		
		<tr>
			<td class="labelcell"><?php
			echo $_VERSION->PRODUCT.': ' .  JText::_('VM_ADMIN_CFG_ALLOW_REGISTRATION');
			?></td>
			<td colspan="2"><?php
			if( $mosConfig_allowUserRegistration == '1' ) {
				echo '<span style="color:green;">'.JText::_('VM_ADMIN_CFG_YES').'</span>';
			}
			else {
				echo '<span style="color:red;font-weight:bold;">'.JText::_('VM_ADMIN_CFG_NO').'</span>';
			}
			echo ' <a href="'.$mosConfig_live_site.'/administrator/index2.php?option=com_config&amp;hidemainmenu=1"> ['.JText::_('VM_UPDATE').']</a>';
			?></td>
		</tr>
		<tr>
			<td class="labelcell"><?php
			echo $_VERSION->PRODUCT.': ' .  JText::_('VM_ADMIN_CFG_ACCOUNT_ACTIVATION');
			?></td>
			<td colspan="2"><?php
			if( $mosConfig_useractivation == '0' ) {
				echo '<span style="color:green;">'.JText::_('VM_ADMIN_CFG_NO').'</span>';
			}
			else {
				echo '<span style="color:red;font-weight:bold;">'.JText::_('VM_ADMIN_CFG_YES').'</span>';
			}
			echo ' <a href="'.$mosConfig_live_site.'/administrator/index2.php?option=com_config&amp;hidemainmenu=1"> ['.JText::_('VM_UPDATE').']</a>';
			?></td>
		</tr>
		
		<tr>
			<td class="labelcell">
				<label for="conf_PSHOP_AGREE_TO_TOS_ONORDER"><?php echo JText::_('VM_ADMIN_CFG_AGREE_TERMS_ONORDER') ?></label>
			</td>
			<td>
				<input type="checkbox" id="conf_PSHOP_AGREE_TO_TOS_ONORDER" name="conf_PSHOP_AGREE_TO_TOS_ONORDER" class="inputbox" <?php if (PSHOP_AGREE_TO_TOS_ONORDER == '1') echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_AGREE_TERMS_ONORDER_EXPLAIN') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf_VM_ONCHECKOUT_SHOW_LEGALINFO"><?php echo JText::_('VM_ADMIN_ONCHECKOUT_SHOW_LEGALINFO') ?></label>
			</td>
			<td>
				<input type="checkbox" id="conf_VM_ONCHECKOUT_SHOW_LEGALINFO" name="conf_VM_ONCHECKOUT_SHOW_LEGALINFO" class="inputbox" <?php if (@VM_ONCHECKOUT_SHOW_LEGALINFO == '1') echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_ONCHECKOUT_SHOW_LEGALINFO_TIP') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf_VM_ONCHECKOUT_LEGALINFO_SHORTTEXT"><?php echo JText::_('VM_ADMIN_ONCHECKOUT_LEGALINFO_SHORTTEXT') ?></label>
			</td>
			<td>
				<textarea rows="6" cols="40" id="conf_VM_ONCHECKOUT_LEGALINFO_SHORTTEXT" name="conf_VM_ONCHECKOUT_LEGALINFO_SHORTTEXT" class="inputbox"><?php if( @VM_ONCHECKOUT_LEGALINFO_SHORTTEXT=='' || !defined('VM_ONCHECKOUT_LEGALINFO_SHORTTEXT')) {echo JText::_('VM_LEGALINFO_SHORTTEXT');} else {echo @VM_ONCHECKOUT_LEGALINFO_SHORTTEXT;} ?></textarea>
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_ONCHECKOUT_LEGALINFO_SHORTTEXT_TIP') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf_VM_ONCHECKOUT_LEGALINFO_LINK"><?php echo JText::_('VM_ADMIN_ONCHECKOUT_LEGALINFO_LINK') ?></label>
			</td>
			<td>
			<?php
			$db->query( "SELECT id AS value, CONCAT( title, ' (', title_alias, ')' ) AS text FROM #__content ORDER BY id" );
			
			$select =  "<select size=\"5\" name=\"conf_VM_ONCHECKOUT_LEGALINFO_LINK\" id=\"conf_VM_ONCHECKOUT_LEGALINFO_LINK\" class=\"inputbox\" style=\"width: 300px;\">\n";
			while( $db->next_record()) {
				$selected = @VM_ONCHECKOUT_LEGALINFO_LINK == $db->f('value') ? 'selected="selected"' : '';
				$select .= "<option title=\"".$db->f('text')."\" value=\"".$db->f('value')."\" $selected>".$db->f('text')."</option>\n";
			}
			$select .=  "</select>\n";
			echo $select;
			?>
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_ONCHECKOUT_LEGALINFO_LINK_TIP') ) ?>
			</td>
		</tr>
		
		</table>
</fieldset>


<!--<fieldset style="width:48%;float:left;">-->
<fieldset>
	<legend><?php echo JText::_('VM_ADMIN_CFG_CORE_SETTINGS') ?></legend>
	<table class="adminform">
		<tr>
			<td class="labelcell">
				<label for="conf_CHECK_STOCK" onclick="var checkStock = document.adminForm.conf_CHECK_STOCK.checked;toggleVisibility( checkStock, 'cs1' );toggleVisibility( checkStock, 'cs2' );toggleVisibility( checkStock, 'cs3' );"><?php echo JText::_('VM_ADMIN_CFG_CHECK_STOCK') ?></label>
				
				<div style="display:none;visibility:hidden;" id="cs1"><br/><br/>
					<strong>
						<label for="conf_PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS">
							<?php echo JText::_('VM_ADMIN_CFG_SHOW_OUT_OF_STOCK_PRODUCTS') ?>
						</label>
					</strong>
				</div>
			</td>
			<td valign="top">
				<input  type="checkbox" name="conf_CHECK_STOCK" id="conf_CHECK_STOCK" class="inputbox" onchange="toggleVisibility( this.checked, 'cs1' );toggleVisibility( this.checked, 'cs2' );toggleVisibility( this.checked, 'cs3' );" <?php if (CHECK_STOCK == '1') echo "checked=\"checked\""; ?> value="1" />
				<div style="display:none;visibility:hidden;" id="cs2"><br/><br/><input type="checkbox" name="conf_PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS" id="conf_PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS" class="inputbox" <?php if (PSHOP_SHOW_OUT_OF_STOCK_PRODUCTS == '1') echo "checked=\"checked\""; ?> value="1" /></div>
			</td>
			<td valign="top"><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_CHECK_STOCK_EXPLAIN') ) ?>
				<div style="display:none;visibility:hidden;" align="left" id="cs3"><br /><br />
				<?php echo vmToolTip( JText::_('VM_ADMIN_CFG_SHOW_OUT_OF_STOCK_PRODUCTS_EXPLAIN') ) ?>
				</div>
			</td>
		</tr>
		  <tr>
			<td class="labelcell">
				<label for="conf_VM_ENABLE_COOKIE_CHECK"><?php echo JText::_('VM_ADMIN_CFG_COOKIE_CHECK') ?></label>
				
			</td>
			<td>
				<input type="checkbox" id="conf_VM_ENABLE_COOKIE_CHECK" name="conf_VM_ENABLE_COOKIE_CHECK" class="inputbox" <?php if (@VM_ENABLE_COOKIE_CHECK == '1') echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_COOKIE_CHECK_EXPLAIN') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell">
				<label for="conf__VM_CURRENCY_CONVERTER_MODULE"><?php echo JText::_('VM_CFG_CURRENCY_MODULE') ?></label>
				
			</td>
			<td>
				<select id="conf__VM_CURRENCY_CONVERTER_MODULE" name="conf__VM_CURRENCY_CONVERTER_MODULE" class="inputbox">
					<?php 
					$files = vmReadDirectory( CLASSPATH."currency/", "convert?.", true, true);
					foreach ($files as $file) {
						$file_info = pathinfo($file);
						$filename = $file_info['basename'];
						$checked = ($filename == @VM_CURRENCY_CONVERTER_MODULE.'.php') ? 'selected="selected"' : "";
						echo "<option value=\"".basename($filename, '.php' )."\" $checked>$filename</option>\n";
					}
	            ?>
				</select>
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_CFG_CURRENCY_MODULE_TIP') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_MAIL_FORMAT') ?></td>
			<td>
				<select name="conf_ORDER_MAIL_HTML" class="inputbox">
				<option value="0" <?php if (ORDER_MAIL_HTML == '0') echo 'selected="selected"'; ?>>
			   <?php echo JText::_('VM_ADMIN_CFG_MAIL_FORMAT_TEXT') ?>
				</option>
				<option value="1" <?php if (ORDER_MAIL_HTML == '1') echo 'selected="selected"'; ?>>
				<?php echo JText::_('VM_ADMIN_CFG_MAIL_FORMAT_HTML') ?>
				</option>
				</select>
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_MAIL_FORMAT_EXPLAIN') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell"><label for="conf_DEBUG"><?php echo JText::_('VM_ADMIN_CFG_DEBUG') ?></label></td>
			<td>
				<input type="checkbox" id="conf_DEBUG" name="conf_DEBUG" class="inputbox" <?php if (DEBUG == 1) echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td class="iconcell">
			<?php echo vmToolTip( JText::_('VM_ADMIN_CFG_DEBUG_EXPLAIN') ) ?>
			</td>
		</tr>
        <tr>
            <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_DEBUG_IP_ENABLED') ?></td>
            <td>
                <input type="checkbox" id="conf_VM_DEBUG_IP_ENABLED" name="conf_VM_DEBUG_IP_ENABLED" class="inputbox" <?php if (@VM_DEBUG_IP_ENABLED == 1) echo "checked=\"checked\""; ?> value="1" />
            </td>
            <td><label for="conf_VM_DEBUG_IP_ENABLED"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_DEBUG_IP_ENABLED_EXPLAIN')) ?></label>
            </td>
        </tr>
        <tr>
            <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_DEBUG_IP_ADDRESS') ?></td>
            <td>
                <input size="20" type="text" name="conf_VM_DEBUG_IP_ADDRESS" class="inputbox" value="<?php echo @VM_DEBUG_IP_ADDRESS ?>" />
            </td>
            <td><label for="conf_VM_DEBUG_IP_ADDRESS"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_DEBUG_IP_ADDRESS_EXPLAIN')) ?></label>
            </td>
        </tr>
	</table>
</fieldset>
</td></tr>
<tr><td>
<fieldset>
    <legend><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_HEADER') ?></legend>
    <table class="adminform">
        <tr>
            <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_ENABLED') ?></td>
            <td>
                <input type="checkbox" id="conf_VM_LOGFILE_ENABLED" name="conf_VM_LOGFILE_ENABLED" class="inputbox" <?php if (@VM_LOGFILE_ENABLED == 1) echo "checked=\"checked\""; ?> value="1" />
            </td>
            <td class="iconcell"><label for="conf_VM_LOGFILE_ENABLED"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_LOGFILE_ENABLED_EXPLAIN')) ?></label>
            </td>
        </tr>
        <tr>
            <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_NAME') ?></td>
            <td>
                <input size="65" type="text" name="conf_VM_LOGFILE_NAME" class="inputbox" value="<?php if(defined('VM_LOGFILE_NAME')) echo VM_LOGFILE_NAME ?>" />
            </td>
            <td class="iconcell"><label for="conf_VM_LOGFILE_NAME"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_LOGFILE_NAME_EXPLAIN')) ?></label>
            </td>
        </tr>
    <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL') ?></td>
        <td>
        <?php if (!defined('VM_LOGFILE_LEVEL')) define('VM_LOGFILE_LEVEL', 'PEAR_LOG_WARNING'); ?>
                <select class="inputbox" name="conf_VM_LOGFILE_LEVEL">
                        <option value="PEAR_LOG_TIP" <?php if (@VM_LOGFILE_LEVEL == 'PEAR_LOG_TIP') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_TIP') ?></option>
                        <option value="PEAR_LOG_DEBUG" <?php if (@VM_LOGFILE_LEVEL == 'PEAR_LOG_DEBUG') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_DEBUG') ?></option>
                        <option value="PEAR_LOG_INFO" <?php if (@VM_LOGFILE_LEVEL == 'PEAR_LOG_INFO') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_INFO') ?></option>
                        <option value="PEAR_LOG_NOTICE" <?php if (@VM_LOGFILE_LEVEL == 'PEAR_LOG_NOTICE') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_NOTICE') ?></option>
                        <option value="PEAR_LOG_WARNING" <?php if (@VM_LOGFILE_LEVEL == 'PEAR_LOG_WARNING') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_WARNING') ?></option>
                        <option value="PEAR_LOG_ERR" <?php if (@VM_LOGFILE_LEVEL == 'PEAR_LOG_ERR') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_ERR') ?></option>
                        <option value="PEAR_LOG_CRIT" <?php if (@VM_LOGFILE_LEVEL == 'PEAR_LOG_CRIT') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_CRIT') ?></option>
                        <option value="PEAR_LOG_ALERT" <?php if (@VM_LOGFILE_LEVEL == 'PEAR_LOG_ALERT') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_ALERT') ?></option>
                        <option value="PEAR_LOG_EMERG" <?php if (@VM_LOGFILE_LEVEL == 'PEAR_LOG_EMERG') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_EMERG') ?></option>
            </select>
        </td>
        <td class="iconcell"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_LOGFILE_LEVEL_EXPLAIN')) ?></td>
    </tr>
        <tr>
                        <?php
                        if(defined('VM_LOGFILE_FORMAT') && (VM_LOGFILE_FORMAT != '')) {
                            $logfile_format = VM_LOGFILE_FORMAT;
                        } else {
                            $logfile_format = '%{timestamp} %{ident} [%{priority}] [%{remoteip}] [%{username}] %{message}';
						}
                        ?>
            <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_FORMAT') ?></td>
            <td>
                <input size="65" type="text" name="conf_VM_LOGFILE_FORMAT" class="inputbox" value="<?php echo $logfile_format ?>" />
            </td>
            <td class="iconcell"><label for="conf_VM_LOGFILE_FORMAT"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_LOGFILE_FORMAT_EXPLAIN')) ?></label>
            </td>
        </tr>
                <tr>
                        <td colspan="3"><?php echo JText::_('VM_ADMIN_CFG_LOGFILE_FORMAT_EXPLAIN_EXTRA') ?></td>
                </tr>
    </table>
</fieldset>


</td></tr></table>
<?php

$tabs->endTab();
$tabs->startTab( JText::_('VM_ADMIN_SECURITY'), "security-page");
?>

<fieldset style="width:48%;float:left;">
	<legend><?php echo JText::_('VM_ADMIN_SECURITY_SETTINGS') ?></legend>
	<table class="adminform">
	<?php
	if( vmisJoomla('1.5')) {
		?><tr>
			<td class="labelcell">Site URL</td>
			<td>
				<input size="40" type="text" name="conf_URL" class="inputbox" value="<?php echo URL ?>" />
			</td>
			<td>&nbsp;</td>
		</tr>
		<?php
	}
	?>
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_URLSECURE') ?></td>
			<td>
				<input size="40" type="text" name="conf_SECUREURL" class="inputbox" value="<?php echo SECUREURL ?>" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_URLSECURE_EXPLAIN') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell">
				<?php echo JText::_('VM_MODULES_FORCE_HTTPS') ?>				
			</td>
			<td>
				<?php
				echo ps_module::list_modules( 'conf_VM_MODULES_FORCE_HTTPS[]', $VM_MODULES_FORCE_HTTPS, true );
				?>
			</td>
			<td><?php echo vmToolTip( JText::_('VM_MODULES_FORCE_HTTPS_TIP') ) ?>
			</td>
		</tr>
	
		<tr>
			<td class="labelcell">
				<input type="checkbox" id="conf_VM_GENERALLY_PREVENT_HTTPS" name="conf_VM_GENERALLY_PREVENT_HTTPS" class="inputbox" <?php if (@VM_GENERALLY_PREVENT_HTTPS == '1') echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td>
				<label for="conf_VM_GENERALLY_PREVENT_HTTPS"><?php echo JText::_('VM_GENERALLY_PREVENT_HTTPS') ?></label>
			</td>
			<td><?php echo vmToolTip( JText::_('VM_GENERALLY_PREVENT_HTTPS_TIP') ) ?>
			</td>
		</tr>
		<tr>
			<td colspan="3"><hr />&nbsp;</td>
		</tr>
		<?php
		if( version_compare( $database->getVersion(), '4.0.2', '>=') ) { ?>
			<tr>
				<td class="labelcell"><?php echo JText::_('VM_ADMIN_ENCRYPTION_FUNCTION') ?>&nbsp;&nbsp;</td>
				<td>
					<?php
					$options = array('ENCODE' => 'ENCODE (insecure)', 
								'AES_ENCRYPT' => 'AES_ENCRYPT (strong security)'
								);
					echo ps_html::selectList('conf_ENCRYPT_FUNCTION', @VM_ENCRYPT_FUNCTION, $options );
					?>
				</td>
				<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_ENCRYPTION_FUNCTION_TIP') ); ?></td>
			</tr>
		<?php
		}
		?>
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_ENCRYPTION_KEY') ?>&nbsp;&nbsp;</td>
			<td>
				<input type="text" name="conf_ENCODE_KEY" class="inputbox" value="<?php echo shopMakeHtmlSafe(ENCODE_KEY) ?>" />
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_ENCRYPTION_KEY_TIP') ); ?></td>
		</tr>
		<tr>
			<td class="labelcell">
				<input type="checkbox" name="conf_VM_STORE_CREDITCARD_DATA" id="conf_VM_STORE_CREDITCARD_DATA" class="inputbox" <?php if (@VM_STORE_CREDITCARD_DATA == '1') echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td>
				<label for="conf_VM_STORE_CREDITCARD_DATA"><?php echo JText::_('VM_ADMIN_STORE_CREDITCARD_DATA') ?>&nbsp;&nbsp;</label>
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_STORE_CREDITCARD_DATA_TIP') ); ?></td>
		</tr>	
		<tr>
			<td colspan="3"><hr />&nbsp;</td>
		</tr>
		<?php
	  if (stristr($my->usertype, "admin")) { ?>
		  <tr>
			<td class="labelcell">
			<input type="checkbox" id="conf_PSHOP_ALLOW_FRONTENDADMIN_FOR_NOBACKENDERS" name="conf_PSHOP_ALLOW_FRONTENDADMIN_FOR_NOBACKENDERS" class="inputbox" <?php if (PSHOP_ALLOW_FRONTENDADMIN_FOR_NOBACKENDERS == '1') echo "checked=\"checked\""; ?> value="1" />
			</td>
			<td>
				<label for="conf_PSHOP_ALLOW_FRONTENDADMIN_FOR_NOBACKENDERS"><?php echo JText::_('VM_ADMIN_CFG_FRONTENDAMDIN') ?></label>
			</td>
			<td class="iconcell"><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_FRONTENDAMDIN_EXPLAIN') ) ?>
			</td>
		</tr>
	<?php
	  }
	  else {
	  	echo '<input type="hidden" name="conf_PSHOP_ALLOW_FRONTENDADMIN_FOR_NOBACKENDERS" value="'.PSHOP_ALLOW_FRONTENDADMIN_FOR_NOBACKENDERS.'" />';
	  }
	?>
		</table>
</fieldset>
<br />
<fieldset style="width:48%;float:right;">
	<legend><?php echo JText::_('VM_ADMIN_CFG_MORE_CORE_SETTINGS') ?></legend>
	<table class="adminform">
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_TABLEPREFIX') ?></td>
			<td>
				<input size="40" type="text" name="conf_VM_TABLEPREFIX" class="inputbox" value="<?php echo VM_TABLEPREFIX ?>" readonly="readonly" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_TABLEPREFIX_TIP') ) ?>
			</td>
		</tr>
		<tr>
			<td colspan="3"><hr />&nbsp;</td>
		</tr>
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_HOMEPAGE') ?></td>
			<td>
				<input type="text" name="conf_HOMEPAGE" class="inputbox" value="<?php echo HOMEPAGE ?>" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_HOMEPAGE_EXPLAIN') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_ERRORPAGE') ?></td>
			<td>
				<input type="text" name="conf_ERRORPAGE" class="inputbox" value="<?php echo ERRORPAGE ?>" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_ERRORPAGE_EXPLAIN')) ?>
			</td>
		</tr>
	</table>
</fieldset>
<br />
<fieldset style="width:48%;float:right;">
	<legend><?php echo JText::_('VM_ADMIN_CFG_PROXY_SETTINGS') ?></legend>
	<table class="adminform">
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_PROXY_URL') ?></td>
			<td>
				<input size="40" type="text" name="conf_VM_PROXY_URL" class="inputbox" value="<?php echo defined('VM_PROXY_URL')?VM_PROXY_URL:''; ?>" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_PROXY_URL_TIP') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_PROXY_PORT') ?></td>
			<td>
				<input type="text" name="conf_VM_PROXY_PORT" class="inputbox" value="<?php echo defined('VM_PROXY_PORT')?VM_PROXY_PORT:''; ?>" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_PROXY_PORT_TIP') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_PROXY_USER') ?></td>
			<td>
				<input type="text" name="conf_VM_PROXY_USER" class="inputbox" value="<?php echo defined('VM_PROXY_USER')?VM_PROXY_USER:''; ?>" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_PROXY_USER_TIP') ) ?>
			</td>
		</tr>
		<tr>
			<td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_PROXY_PASS') ?></td>
			<td>
				<input autocomplete="off" type="password" name="conf_VM_PROXY_PASS" class="inputbox" value="<?php echo defined('VM_PROXY_PASS')?VM_PROXY_PASS:''; ?>" />
			</td>
			<td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_PROXY_PASS_TIP') ) ?>
			</td>
		</tr>
	</table>
</fieldset>
<br style="clear:both" />
<?php
$tabs->endTab();
$tabs->startTab( JText::_('VM_ADMIN_CFG_SITE'), "site-page");
?>

<fieldset style="width:48%;float:left;">
	<legend><?php echo JText::_('VM_ADMIN_CFG_DISPLAY') ?></legend>
<table class="adminlist">
    <tr>
        <td class="labelcell"><label for="conf_PSHOP_PDF_BUTTON_ENABLE"><?php echo JText::_('VM_ADMIN_CFG_PDF_BUTTON') ?></label></td>
        <td>
        <input type="checkbox" id="conf_PSHOP_PDF_BUTTON_ENABLE" name="conf_PSHOP_PDF_BUTTON_ENABLE" class="inputbox" <?php if (PSHOP_PDF_BUTTON_ENABLE == 1) echo "checked=\"checked\""; ?> value="1" />
        </td>
        <td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_PDF_BUTTON_EXPLAIN') ) ?></td>
    </tr>
    <tr>
        <td class="labelcell"><label for="conf_VM_SHOW_EMAILFRIEND"><?php echo JText::_('VM_ADMIN_SHOW_EMAILFRIEND') ?></label></td>
        <td>
        	<input type="checkbox" id="conf_VM_SHOW_EMAILFRIEND" name="conf_VM_SHOW_EMAILFRIEND" class="inputbox" <?php if (@VM_SHOW_EMAILFRIEND == 1) echo "checked=\"checked\""; ?> value="1" />
        </td>
        <td><?php echo vmToolTip( JText::_('VM_ADMIN_SHOW_EMAILFRIEND_TIP') ) ?></td>
    </tr>
    <tr>
        <td class="labelcell"><label for="conf_VM_SHOW_PRINTICON"><?php echo JText::_('VM_ADMIN_SHOW_PRINTICON') ?></label></td>
        <td>
        	<input type="checkbox" id="conf_VM_SHOW_PRINTICON" name="conf_VM_SHOW_PRINTICON" class="inputbox" <?php if (@VM_SHOW_PRINTICON == 1) echo "checked=\"checked\""; ?> value="1" />
        </td>
        <td><?php echo vmToolTip( JText::_('VM_ADMIN_SHOW_PRINTICON_TIP') ) ?></td>
    </tr>
    <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_NAV_AT_TOP') ?></td>
        <td>
            <input type="checkbox" name="conf_PSHOP_SHOW_TOP_PAGENAV" class="inputbox" <?php if (PSHOP_SHOW_TOP_PAGENAV == '1') echo "checked=\"checked\""; ?> value="1" />
        </td>
        <td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_NAV_AT_TOP_TIP') ) ?></td>
    </tr>
    <tr>
        <td class="labelcell"><?php echo JText::_('VM_BROWSE_ORDERBY_DEFAULT_FIELD_LBL') ?></td>
        <td>
                <select class="inputbox" name="conf_VM_BROWSE_ORDERBY_FIELD">
                        <option value="product_list" <?php if (@VM_BROWSE_ORDERBY_FIELD == 'product_list') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_DEFAULT') ?></option>
                        <option value="product_name" <?php if (@VM_BROWSE_ORDERBY_FIELD == 'product_name') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_PRODUCT_NAME_TITLE') ?></option>
                        <option value="product_price" <?php if (@VM_BROWSE_ORDERBY_FIELD == 'product_price') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_PRODUCT_PRICE_TITLE') ?></option>
                        <option value="product_sku" <?php if (@VM_BROWSE_ORDERBY_FIELD == 'product_sku') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_CART_SKU') ?></option>
                        <option value="product_cdate" <?php if (@VM_BROWSE_ORDERBY_FIELD == 'product_cdate') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_LATEST') ?></option>
                        <option value="product_sales" <?php if (@VM_BROWSE_ORDERBY_FIELD == 'product_sales') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_SALES') ?></option>
            </select>
        </td>
        <td><?php echo vmToolTip( JText::_('VM_BROWSE_ORDERBY_DEFAULT_FIELD_LBL_TIP') ) ?></td>
    </tr>
    <tr>
        <td class="labelcell"><?php echo JText::_('VM_BROWSE_ORDERBY_FIELDS_LBL') ?></td>
        <td>
                        <input name="conf_VM_BROWSE_ORDERBY_FIELDS[]" type="checkbox" value="product_list" <?php if (in_array( 'product_list', $VM_BROWSE_ORDERBY_FIELDS )) echo "checked=\"checked\""; ?> id="conf_VM_BROWSE_ORDERBY_FIELDS0" />
                        <label for="conf_VM_BROWSE_ORDERBY_FIELDS0"><?php echo JText::_('VM_DEFAULT');?></label><br />

                        <input name="conf_VM_BROWSE_ORDERBY_FIELDS[]" type="checkbox" value="product_name" <?php if (in_array( 'product_name', $VM_BROWSE_ORDERBY_FIELDS )) echo "checked=\"checked\""; ?> id="conf_VM_BROWSE_ORDERBY_FIELDS1" />
                        <label for="conf_VM_BROWSE_ORDERBY_FIELDS1"><?php echo JText::_('VM_PRODUCT_NAME_TITLE') ?></label><br />

                        <input name="conf_VM_BROWSE_ORDERBY_FIELDS[]" type="checkbox" value="product_price" <?php if (in_array( 'product_price', $VM_BROWSE_ORDERBY_FIELDS )) echo "checked=\"checked\""; ?> id="conf_VM_BROWSE_ORDERBY_FIELDS2" />
                        <label for="conf_VM_BROWSE_ORDERBY_FIELDS2"><?php echo JText::_('VM_PRODUCT_PRICE_TITLE') ?></label><br />

                        <input name="conf_VM_BROWSE_ORDERBY_FIELDS[]" type="checkbox" value="product_sku" <?php if (in_array( 'product_sku', $VM_BROWSE_ORDERBY_FIELDS )) echo "checked=\"checked\""; ?> id="conf_VM_BROWSE_ORDERBY_FIELDS4" />
                        <label for="conf_VM_BROWSE_ORDERBY_FIELDS4"><?php echo JText::_('VM_CART_SKU') ?></label><br />

                        <input name="conf_VM_BROWSE_ORDERBY_FIELDS[]" type="checkbox" value="product_cdate" <?php if (in_array( 'product_cdate', $VM_BROWSE_ORDERBY_FIELDS )) echo "checked=\"checked\""; ?> id="conf_VM_BROWSE_ORDERBY_FIELDS3" />
                        <label for="conf_VM_BROWSE_ORDERBY_FIELDS3"><?php echo JText::_('VM_LATEST') ?></label><br />

                        <input name="conf_VM_BROWSE_ORDERBY_FIELDS[]" type="checkbox" value="product_sales" <?php if (in_array( 'product_sales', $VM_BROWSE_ORDERBY_FIELDS )) echo "checked=\"checked\""; ?> id="conf_VM_BROWSE_ORDERBY_FIELDS5" />
                        <label for="conf_VM_BROWSE_ORDERBY_FIELDS5"><?php echo JText::_('VM_SALES') ?></label>
                        
        </td>
        <td><?php echo vmToolTip( JText::_('VM_BROWSE_ORDERBY_FIELDS_LBL_TIP') ) ?></td>
    </tr>
    <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_SHOW_PRODUCT_COUNT') ?></td>
        <td>
            <input type="checkbox" name="conf_PSHOP_SHOW_PRODUCTS_IN_CATEGORY" class="inputbox" <?php if (PSHOP_SHOW_PRODUCTS_IN_CATEGORY == '1') echo "checked=\"checked\""; ?> value="1" />
        </td>
        <td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_SHOW_PRODUCT_COUNT_TIP') ) ?></td>
    </tr>
    <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_NOIMAGEPAGE') ?></td>
        <td>
	        <?php
			$images = vmReadDirectory(VM_THEMEPATH.'images', '\.png$|\.bmp$|\.jpg$|\.jpeg$|\.gif$|\.ico$');
			foreach( $images as $image ) {
				$imageArr[basename($image)] = $image;
			}
			echo ps_html::selectList('conf_NO_IMAGE', NO_IMAGE, $imageArr );
	        ?>
        </td>
        <td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_NOIMAGEPAGE_EXPLAIN') ) ?>
        </td>
    </tr>
    <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_SHOWVM_VERSION') ?></td>
        <td>
            <input type="checkbox" name="conf_SHOWVERSION" class="inputbox" <?php if (SHOWVERSION == 1) echo "checked=\"checked\""; ?> value="1" />
        </td>
        <td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_SHOWVM_VERSION_EXPLAIN') ) ?>
        </td>
    </tr>
</table>
</fieldset>

<fieldset style="width:48%;float:right;">
<legend><?php echo JText::_('VM_ADMIN_CFG_LAYOUT') ?></legend>
<table class="adminlist">
    <tr>
        <td class="labelcell"><?php echo JText::_('VM_SELECT_THEME') ?></td>
        <td>
        <?php 
        echo ps_html::list_themes( 'conf_THEME', basename(VM_THEMEURL) );

        if( $vmLayout == 'standard') {
	        $link = $sess->url( $_SERVER['PHP_SELF'].'?page=admin.theme_config_form&amp;theme='.basename(VM_THEMEURL) );
	        $text = JText::_('VM_CONFIG');
			echo vmCommonHTML::hyperlink($link, JText::_('VM_CONFIG') );
		} else {
	        $link = $sess->url( $_SERVER['PHP_SELF'].'?page=admin.theme_config_form&amp;theme='.basename(VM_THEMEURL).'&amp;no_menu=1&amp;tmpl=component' );
	        
			$link = defined('_VM_IS_BACKEND') 
							? str_replace('index2.php', 'index3.php', str_replace('index.php', 'index3.php', $link )) 
							: str_replace('index.php', 'index2.php', $link );
	        $text = JText::_('VM_CONFIG');
			echo vmCommonHTML::hyperLink($link, $text, '', 'Edit: '.$text, 'onclick="parent.addSimplePanel( \''.$db->getEscaped($text).'\', \''.$link.'\' );return false;"');
		}  
        ?>
        </td>
        <td><?php echo vmToolTip( JText::_('VM_SELECT_THEME_TIP') ) ?></td>
    </tr> 
  
    <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_PRODUCTS_PER_ROW') ?></td>
        <td>
            <input type="text" name="conf_PRODUCTS_PER_ROW" size="4" class="inputbox" value="<?php echo PRODUCTS_PER_ROW ?>" />
        </td>
        <td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_PRODUCTS_PER_ROW_EXPLAIN') ) ?>
        </td>
    </tr>
    <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_CATEGORY_TEMPLATE') ?></td>
        <td>
        <?php
        echo ps_html::list_template_files( "conf_CATEGORY_TEMPLATE", 'browse', CATEGORY_TEMPLATE );
        ?>
        </td>
        <td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_CATEGORY_TEMPLATE_EXPLAIN') ) ?>
        </td>
    </tr>
   	<tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_FLYPAGE') ?></td>
        <td>
        	<?php
        	echo ps_html::list_template_files( "conf_FLYPAGE", 'product_details', str_replace('shop.', '', FLYPAGE ) );
        	?>
        </td>
        <td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_FLYPAGE_EXPLAIN') ) ?></td>
    </tr>
    <?php
    if( function_exists('imagecreatefromjpeg') ) {
    	?>
    
	    <tr>
	        <td class="labelcell">
	        	<label for="conf_PSHOP_IMG_RESIZE_ENABLE"><?php echo JText::_('VM_ADMIN_CFG_DYNAMIC_THUMBNAIL_RESIZING') ?></label></td>
	        <td>
	            <input type="checkbox" name="conf_PSHOP_IMG_RESIZE_ENABLE" id="conf_PSHOP_IMG_RESIZE_ENABLE" class="inputbox" <?php if (PSHOP_IMG_RESIZE_ENABLE == '1') echo "checked=\"checked\""; ?> value="1" />
	        </td>
	        <td width="55%"><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_DYNAMIC_THUMBNAIL_RESIZING_TIP') ) ?></td>
	    </tr>
	    <tr>
	        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_THUMBNAIL_WIDTH') ?></td>
	        <td>
	            <input type="text" name="conf_PSHOP_IMG_WIDTH" class="inputbox" value="<?php echo PSHOP_IMG_WIDTH ?>" />
	        </td>
	        <td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_THUMBNAIL_WIDTH_TIP') ) ?></td>
	    </tr>
	    <tr>
	        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_THUMBNAIL_HEIGHT') ?></td>
	        <td>
	            <input type="text" name="conf_PSHOP_IMG_HEIGHT" class="inputbox" value="<?php echo PSHOP_IMG_HEIGHT ?>" />
	        </td>
	        <td><?php echo vmToolTip( JText::_('VM_ADMIN_CFG_THUMBNAIL_HEIGHT_TIP') ) ?></td>
	    </tr>
	    <?php
    }
    else {
    	echo '<tr>
	        <td colspan="3"><strong>Dynamic Image Resizing is not available. The GD library seems to be missing.</strong>';
    	echo '<input type="hidden" name="conf_PSHOP_IMG_RESIZE_ENABLE" value="0" />';
    	echo '<input type="hidden" name="conf_PSHOP_IMG_WIDTH" value="'. PSHOP_IMG_WIDTH .'" />';
    	echo '<input type="hidden" name="conf_PSHOP_IMG_HEIGHT" value="'. PSHOP_IMG_HEIGHT .'" /></td></tr>';
    }
    ?>
    
</table>
</fieldset>
<br style="clear:both;" />
<?php
$tabs->endTab();


$tabs->startTab( JText::_('VM_ADMIN_CFG_CHECKOUT'), "checkout-page");
?>

<table class="adminform">
   	<tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_ENABLE_CHECKOUTBAR') ?></td>
        <td>
            <input type="checkbox" name="conf_SHOW_CHECKOUT_BAR" class="inputbox" <?php if (SHOW_CHECKOUT_BAR == 1) echo "checked=\"checked\""; ?> value="1" />
        </td>
        <td width="30%"><?php echo JText::_('VM_ADMIN_CFG_ENABLE_CHECKOUTBAR_EXPLAIN') ?>
        </td>
    </tr>
       	<tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_MAX_VENDOR_PRO_CART') ?></td>
        <td>
            <input type="text" name="conf_MAX_VENDOR_PRO_CART" class="inputbox" value=<?php echo MAX_VENDOR_PRO_CART; ?> />
        </td>
        <td width="30%"><?php echo JText::_('VM_ADMIN_CFG_MAX_VENDOR_PRO_CART_EXPLAIN') ?>
        </td>
    </tr>
    <tr>
        <td valign="top"><?php echo JText::_('VM_ADMIN_CFG_CHECKOUT_PROCESS') ?></td>
        <td valign="top">
            <?php
            $checkout_names = array_keys( $VM_CHECKOUT_MODULES );
            foreach( $VM_CHECKOUT_MODULES as $step ) {
            	$stepname = current($checkout_names);
            	$label = "VM_CHECKOUT_MSG_".constant($stepname);
            	$readonly = $checked = '';
            	if( $step['enabled'] > 0 ) {
            		$checked = ' checked="checked"';
            	}
            	if( $stepname == 'CHECK_OUT_GET_PAYMENT_METHOD' || $stepname == 'CHECK_OUT_GET_FINAL_CONFIRMATION') {
            		$readonly = 'disabled="disabled"';
            		$checked = ' checked="checked"';
            	}
            	echo '<input type="checkbox" name="VM_CHECKOUT_MODULES['.$stepname.'][enabled]" id="VM_CHECKOUT_MODULES_'.$stepname.'" value="1" '.$readonly.$checked.'/>
            			<label for="VM_CHECKOUT_MODULES_'.$stepname.'"><strong>&quot;'.JText::_($label).'&quot;</strong></label><br />
            			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            			'.sprintf( JText::_('VM_CFG_CHECKOUT_SHOWSTEPINCHECKOUT'), '<input type="text" name="VM_CHECKOUT_MODULES['.$stepname.'][order]" value="'.$step['order'].'" class="inputbox" size="2" />' )
            	.'<input type="hidden" name="VM_CHECKOUT_MODULES['.$stepname.'][name]" value="'.$stepname.'" />
            			<br /><br />';
            	next($checkout_names);
            }
            ?>
        </td>
        <td width="30%" valign="top"><?php 
        echo vmToolTip( JText::_('VM_CFG_CHECKOUT_SHOWSTEP_TIP') );
        	?>
        	</td>
    </tr>
  </table>

<?php
$tabs->endTab();
$tabs->startTab( JText::_('VM_ADMIN_CFG_DOWNLOADABLEGOODS'), "download-page");
?>

  <table class="adminform">
  <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_ENABLE_DOWNLOADS') ?></td>
        <td>
            <input type="checkbox" name="conf_ENABLE_DOWNLOADS" class="inputbox" <?php if (ENABLE_DOWNLOADS == 1) echo "checked=\"checked\""; ?> value="1" />
        </td>
        <td><?php echo JText::_('VM_ADMIN_CFG_ENABLE_DOWNLOADS_EXPLAIN') ?>
        </td>
    </tr>
    <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_ORDER_ENABLE_DOWNLOADS') ?></td>
        <td>
            <select name="conf_ENABLE_DOWNLOAD_STATUS" class="inputbox" >
            <?php
            $db = new ps_DB;
            $q = "SELECT order_status_name,order_status_code FROM #__{vm}_order_status ORDER BY list_order";
            $db->query($q);
            $order_status_code = Array();
            $order_status_name = Array();

            while ($db->next_record()) {
            	$order_status_code[] = $db->f("order_status_code");
            	$order_status_name[] =  $db->f("order_status_name");
            }

            for ($i = 0; $i < sizeof($order_status_code); $i++) {
            	echo "<option value=\"" . $order_status_code[$i];
            	if (ENABLE_DOWNLOAD_STATUS == $order_status_code[$i])
            	echo "\" selected=\"selected\">";
            	else
            	echo "\">";
            	echo $order_status_name[$i] . "</option>\n";
                }?>
                </select>
        </td>
        <td><?php echo JText::_('VM_ADMIN_CFG_ORDER_ENABLE_DOWNLOADS_EXPLAIN') ?>
        </td>
    </tr>
        <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_ORDER_DISABLE_DOWNLOADS') ?></td>
        <td>
            <select name="conf_DISABLE_DOWNLOAD_STATUS" class="inputbox" >
            <?php
            for ($i = 0; $i < sizeof($order_status_code); $i++) {
            	echo "<option value=\"" . $order_status_code[$i];
            	if (DISABLE_DOWNLOAD_STATUS == $order_status_code[$i])
            	echo "\" selected=\"selected\">";
            	else
            	echo "\">";
            	echo $order_status_name[$i] . "</option>\n";
                }?>
                </select>
        </td>
        <td><?php echo JText::_('VM_ADMIN_CFG_ORDER_DISABLE_DOWNLOADS_EXPLAIN') ?>
        </td>
    </tr>
      <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_DOWNLOADROOT') ?></td>
        <td valign="top">
            <input size="40" type="text" name="conf_DOWNLOADROOT" class="inputbox" value="<?php echo DOWNLOADROOT ?>" />
        </td>
        <td><?php echo JText::_('VM_ADMIN_CFG_DOWNLOADROOT_EXPLAIN') ?>
        </td>
    </tr>
    <tr>
      <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_MAX') ?></td>
        <td>
            <input size="3" type="text" name="conf_DOWNLOAD_MAX" class="inputbox" value="<?php echo DOWNLOAD_MAX ?>" />
        </td>
        <td><?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_MAX_EXPLAIN') ?>
        </td>
    </tr>
    <tr>
      <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_EXPIRE') ?></td>
        <td>
            <input size="8" type="text" name="conf_DOWNLOAD_EXPIRE" class="inputbox" value="<?php echo DOWNLOAD_EXPIRE ?>" />
        </td>
        <td><?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_EXPIRE_EXPLAIN') ?>
        </td>
    </tr>
    <tr>
      <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_KEEP_STOCKLEVEL') ?></td>
        <td>
            <input name="conf_VM_DOWNLOADABLE_PRODUCTS_KEEP_STOCKLEVEL" type="checkbox" <?php if (VM_DOWNLOADABLE_PRODUCTS_KEEP_STOCKLEVEL == 1) echo "checked=\"checked\""; ?> value="1" />
        </td>
        <td><?php echo JText::_('VM_ADMIN_CFG_DOWNLOAD_KEEP_STOCKLEVEL_TIP') ?>
        </td>
    </tr>
    </table>

<?php
$tabs->endTab();
$tabs->startTab( JText::_('VM_ADMIN_CFG_FEED_CONFIGURATION'), "feed-page");
  ?>
  <table class="adminform">
   <tr>
        <td class="labelcell">&nbsp;</td>
        <td>
            <input type="checkbox" name="conf_VM_FEED_ENABLED" id="conf_VM_FEED_ENABLED" class="inputbox" <?php if (@VM_FEED_ENABLED == 1) echo "checked=\"checked\""; ?> value="1" />
            <label for="conf_VM_FEED_ENABLED"><?php echo JText::_('VM_ADMIN_CFG_FEED_ENABLE') ?></label>
        </td>
        <td width="30%"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_FEED_ENABLE_TIP') ) ?>
        </td>
    </tr>
   <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_FEED_CACHE') ?></td>
        <td>
            <input type="checkbox" name="conf_VM_FEED_CACHE" id="conf_VM_FEED_CACHE" class="inputbox" <?php if (@VM_FEED_CACHE == 1) echo "checked=\"checked\""; ?> value="1" />
            <label for="conf_VM_FEED_CACHE"><?php echo JText::_('VM_ADMIN_CFG_FEED_CACHE_ENABLE') ?></label><br />
            <br />
			<input type="text" size="10" value="<?php echo defined('VM_FEED_CACHETIME') ? VM_FEED_CACHETIME : 1800  ?>" name="conf_VM_FEED_CACHETIME" id="conf_VM_FEED_CACHETIME" />
			<label for="conf_VM_FEED_CACHETIME"><?php echo JText::_('VM_ADMIN_CFG_FEED_CACHETIME') ?></label>
        </td>
        <td width="30%"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_FEED_CACHE_TIP') ) ?>
        </td>
    </tr>

   <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_FEED_TITLE') ?></td>
        <td>
			<input type="text" size="40" value="<?php echo @VM_FEED_TITLE ?>" name="conf_VM_FEED_TITLE" id="conf_VM_FEED_TITLE" /><br />
			<label for="conf_VM_FEED_TITLE"><?php echo JText::_('VM_ADMIN_CFG_FEED_TITLE') ?></label><br />
			<br />
			<input type="text" size="40" value="<?php echo @VM_FEED_TITLE_CATEGORIES ?>" name="conf_VM_FEED_TITLE_CATEGORIES" id="conf_VM_FEED_TITLE_CATEGORIES" /><br />
			<label for="conf_VM_FEED_TITLE"><?php echo JText::_('VM_ADMIN_CFG_FEED_TITLE_CATEGORIES') ?></label><br />
        </td>
        <td width="30%"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_FEED_TITLE_TIP')) ?><br />
			<br />
			<?php echo vmToolTip(JText::_('VM_ADMIN_CFG_FEED_TITLE_CATEGORIES_TIP')) ?>
        </td>
    </tr>
   <tr>
        <td class="labelcell">&nbsp;</td>
        <td>
            <input type="checkbox" name="conf_VM_FEED_SHOW_IMAGES" id="conf_VM_FEED_SHOW_IMAGES" class="inputbox" <?php if (@VM_FEED_SHOW_IMAGES == 1) echo "checked=\"checked\""; ?> value="1" />
            <label for="conf_VM_FEED_SHOW_IMAGES"><?php echo JText::_('VM_ADMIN_CFG_FEED_SHOWIMAGES') ?></label>
        </td>
        <td width="30%"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_FEED_SHOWIMAGES_TIP') ) ?>
        </td>
    </tr>
   <tr>
        <td class="labelcell">&nbsp;</td>
        <td>
            <input type="checkbox" name="conf_VM_FEED_SHOW_PRICES" id="conf_VM_FEED_SHOW_PRICES" class="inputbox" <?php if (@VM_FEED_SHOW_PRICES == 1) echo "checked=\"checked\""; ?> value="1" />
            <label for="conf_VM_FEED_SHOW_PRICES"><?php echo JText::_('VM_ADMIN_CFG_FEED_SHOWPRICES') ?></label>
        </td>
        <td width="30%"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_FEED_SHOWPRICES_TIP') ) ?>
        </td>
    </tr>
   <tr>
        <td class="labelcell">&nbsp;</td>
        <td>
            <input type="checkbox" name="conf_VM_FEED_SHOW_DESCRIPTION" id="conf_VM_FEED_SHOW_DESCRIPTION" class="inputbox" <?php if (@VM_FEED_SHOW_DESCRIPTION == 1) echo "checked=\"checked\""; ?> value="1" />
            <label for="conf_VM_FEED_SHOW_DESCRIPTION"><?php echo JText::_('VM_ADMIN_CFG_FEED_SHOWDESC') ?></label>
        </td>
        <td width="30%"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_FEED_SHOWDESC_TIP') ) ?>
        </td>
    </tr>
   <tr>
        <td class="labelcell"><?php echo JText::_('VM_ADMIN_CFG_FEED_DESCRIPTION_TYPE') ?></td>
        <td>
            <select name="conf_VM_FEED_DESCRIPTION_TYPE" id="conf_VM_FEED_DESCRIPTION_TYPE" class="inputbox">
            	<option value="product_s_desc" <?php if (@VM_FEED_DESCRIPTION_TYPE == 'product_s_desc') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_PRODUCT_FORM_S_DESC') ?></option>
            	<option value="product_desc" <?php if (@VM_FEED_DESCRIPTION_TYPE == 'product_desc') echo "selected=\"selected\""; ?>><?php echo JText::_('VM_PRODUCT_FORM_DESCRIPTION') ?></option>
			</select>
        </td>
        <td width="30%"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_FEED_DESCRIPTION_TYPE_TIP') ) ?>
        </td>
    </tr>
   <tr>
        <td class="labelcell">&nbsp;</td>
        <td>
            <input type="checkbox" name="conf_VM_FEED_LIMITTEXT" id="conf_VM_FEED_LIMITTEXT" class="inputbox" <?php if (@VM_FEED_LIMITTEXT == 1) echo "checked=\"checked\""; ?> value="1" />
            <label for="conf_VM_FEED_LIMITTEXT"><?php echo JText::_('VM_ADMIN_CFG_FEED_LIMITTEXT') ?></label><br />
            <br />
			<input type="text" size="10" value="<?php echo defined('VM_FEED_MAX_TEXT_LENGTH') ? VM_FEED_MAX_TEXT_LENGTH : 500  ?>" name="conf_VM_FEED_MAX_TEXT_LENGTH" id="conf_VM_FEED_MAX_TEXT_LENGTH" />
			<label for="conf_VM_FEED_MAX_TEXT_LENGTH"><?php echo JText::_('VM_ADMIN_CFG_FEED_MAX_TEXT_LENGTH') ?></label>
        </td>
        <td width="30%"><?php echo vmToolTip(JText::_('VM_ADMIN_CFG_MAX_TEXT_LENGTH_TIP') ) ?>
        </td>
    </tr>
    </table>
  <?php

  $tabs->endTab();

  $tabs->endPane();

  // Add necessary hidden fields
  $formObj->hiddenField( 'myname', 'Jabba Binks' );

  // Write your form with mixed tags and text fields
  // and finally close the form:
  $formObj->finishForm( 'writeConfig', 'store.index', $option );
?>
<br style="clear:both;" />
<script type="text/javascript">
function validateForm(pressbutton) {
	var form = document.adminForm;

	return true;

}
function toggleVisibility( makeVisible, ID ) {
	element = document.getElementById( ID );
	if( makeVisible ) {
		element.style.visibility='visible';
		if( element.style.display=='none' ) {
			element.style.display='block';
		}
	} else {
		element.style.visibility='hidden';
		element.style.display='none';
	}
}

var checkStock = document.adminForm.conf_CHECK_STOCK.checked;
toggleVisibility( checkStock, 'cs1' );toggleVisibility( checkStock, 'cs2' );toggleVisibility( checkStock, 'cs3' );
<?php
if( VM_PRICE_ACCESS_LEVEL == '0' ) { ?>
document.adminForm.conf_VM_PRICE_ACCESS_LEVEL.disabled = true;
<?php
} ?>
</script>

<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
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

include_class( "vendor" );

$option = empty($option)?JRequest::getVar(  'option', 'com_virtuemart'):$option;

$currency_style_positive = array('00Symb', '00 Symb', 'Symb00', 'Symb 00' );
$currency_style_negative = array('(Symb00)', '-Symb00', 'Symb-00', 'Symb00-', '(00Symb)', '-00Symb', '00-Symb', 
														'00Symb-', '-00 Symb', '-Symb 00', '00 Symb-', 'Symb 00-', 'Symb -00', '00- Symb', 
														'(Symb 00)',	'(00 Symb)');

//by Max Milbers
global $hVendor;
$vendor_id = $hVendor -> getLoggedVendor();
$db = ps_vendor::get_vendor_details($vendor_id);
$GLOBALS['vmLogger']->info('Store.store_form The vendor ID: '.$vendor_id);

$title = '<img src="'. VM_ADMIN_ICON_URL.'icon_48/vm_store_48.png" align="absmiddle" border="0" alt="Store" />'.'&nbsp;&nbsp;&nbsp;'. JText::_('VM_STORE_FORM_LBL');

//First create the object and let it print a form heading
$formObj = &new formFactory( $title );
//Then Start the form
$formObj->startForm( 'adminForm', 'enctype="multipart/form-data"' );
?>
<br /><br />
  <table class="adminform">
    <tr> 
 		<td width="50%" valign="top"><br />
		  <fieldset>
		  <legend><?php echo JText::_('VM_STORE_MOD') ?></legend>
	 		<table class="adminform">
			    <tr class="row0"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_STORE_NAME') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="vendor_store_name" value="<?php $db->sp("vendor_store_name") ?>" size="32" />
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_COMPANY_NAME') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="vendor_name" value="<?php $db->sp("vendor_name") ?>" size="32" />
			      </td>
			    </tr>
			    <tr class="row0"> 
			      <td class="labelcell"><?php echo JText::_('VM_PRODUCT_FORM_URL') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="vendor_url" value="<?php $db->sp("vendor_url") ?>" size="32" />
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_ADDRESS_1') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="address_1" value="<?php $db->sp("address_1") ?>" size="32" />
			      </td>
			    </tr>
			    <tr class="row0"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_ADDRESS_2') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="address_2" value="<?php $db->sp("address_2") ?>" size="32" />
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_CITY') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="city" value="<?php $db->sp("city") ?>" size="16" />
			      </td>
			    </tr>
			    <tr class="row0"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_COUNTRY') ?>:</td>
			      <td width="78%" > 
			        <?php $ps_html->list_country("country", $db->sf("country"), "onchange=\"changeStateList();\"") ?>
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_STATE') ?>:</td>
			      <td width="78%" ><?php 
			      		echo $ps_html->dynamic_state_lists( "country", "state", $db->sf("country"), $db->sf("state") );
			      	?>
			      </td>
			    </tr>
			    <tr class="row0"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_ZIP') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="zip" value="<?php $db->sp("zip") ?>" size="10" />
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_PHONE') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="vendor_phone" value="<?php $db->sp("vendor_phone") ?>" size="16" />
			      </td>
			    </tr>
			    <tr class="row0"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_ADDRESS_FORMAT') ?>:<br />
					<?php
					echo vmToolTip(JText::_('VM_STORE_ADDRESS_FORMAT_TIP') . ':<br />
			        		<strong>{storename}</strong>: '.JText::_('VM_STORE_FORM_STORE_NAME').'<br />
			        		<strong>{address_1}</strong>: '.JText::_('VM_STORE_FORM_ADDRESS_1').'<br />
			        		<strong>{address_2}</strong>: '.JText::_('VM_STORE_FORM_ADDRESS_2').'<br />
			        		<strong>{state}</strong>: '.JText::_('VM_STORE_FORM_STATE').'<br />
			        		<strong>{statename}</strong>: '.JText::_('VM_STATE_LIST_2_CODE').'<br />
			        		<strong>{city}</strong>: '.JText::_('VM_STORE_FORM_CITY').'<br />
			        		<strong>{zip}</strong>: '.JText::_('VM_STORE_FORM_ZIP').'<br />
			        		<strong>{country}</strong>: '.JText::_('VM_STORE_FORM_COUNTRY').'<br />
			        		<strong>{phone}</strong>: '.JText::_('VM_STORE_FORM_PHONE').'<br />
			        		<strong>{fax}</strong>: '.JText::_('VM_STORE_FORM_FAX').'<br />
			        		<strong>{email}</strong>: '.JText::_('VM_STORE_FORM_EMAIL').'<br />
			        		<strong>{url}</strong>: '.JText::_('VM_PRODUCT_FORM_URL').'<br />
			        		');
			        ?>
			        </td>
			      <td width="78%" valign="top"> 
			        <textarea class="inputbox" name="vendor_address_format" rows="4" cols="40"><?php $db->sp("vendor_address_format") ?></textarea>
			        
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_DATE_FORMAT') ?>:
				  </td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="vendor_date_format" value="<?php $db->sp("vendor_date_format") ?>" size="32" />
					<a href="http://www.php.net/manual/function.strftime.php" target="_blank">(info)</a>
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell">&nbsp;</td>
			      <td width="78%" > &nbsp;</td>
			    </tr>
			 </table>
			  </fieldset>
			  <br />
			<fieldset>
		  <legend><?php echo JText::_('VM_STORE_FORM_LBL') ?></legend>
	    	  <table class="adminform">
	    		<tr class="row0"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_FULL_IMAGE') ?>:</td>
			      <td width="78%" ><?php  
			        $hVendor -> show_image($db->f("vendor_full_image"), $vendor_id);
			        ?> 
			        <input type="hidden" name="vendor_full_image_curr" value="<?php $db->p("vendor_full_image"); ?>" />
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_UPLOAD') ?>:</td>
			      <td width="78%" > 
			        <input type="file" name="vendor_full_image" size="16" />
			      </td>
			    </tr>
			    <tr class="row0"> 
			      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_MPOV') ?>: </td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="vendor_min_pov" value="<?php $db->sp("vendor_min_pov") ?>" size="6" />
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell"><?php echo JText::_('VM_FREE_SHIPPING_AMOUNT') ?>: </td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="vendor_freeshipping" value="<?php $db->sp("vendor_freeshipping") ?>" size="6" />
			      <?php echo vmToolTip( JText::_('VM_FREE_SHIPPING_AMOUNT_TOOLTIP') ) ?>
			      </td>
			    </tr>
		      </table>
	      </fieldset>
 		</td>
 		
    	<td width="50%" valign="top"><br />
		  			  <fieldset>
			  <legend><?php echo JText::_('VM_STORE_FORM_CONTACT_LBL') ?></legend>
			  <table class="adminform">
			    <tr class="row0">
			      <td class="labelcell"> <?php echo JText::_('VM_STORE_FORM_LAST_NAME') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="last_name" value="<?php $db->sp("last_name") ?>" size="16" />
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell"> <?php echo JText::_('VM_STORE_FORM_FIRST_NAME') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="first_name" value="<?php $db->sp("first_name") ?>" size="16" />
			      </td>
			    </tr>
			    <tr class="row0"> 
			      <td class="labelcell"> <?php echo JText::_('VM_STORE_FORM_MIDDLE_NAME') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="middle_name" value="<?php $db->sp("middle_name") ?>" size="16">
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell"> <?php echo JText::_('VM_STORE_FORM_TITLE') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="title" value="<?php $db->sp("title") ?>" size="16" />
			      </td>
			    </tr>
			    <tr class="row0"> 
			      <td class="labelcell"> <?php echo JText::_('VM_STORE_FORM_PHONE_1') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="phone_1" value="<?php $db->sp("phone_1") ?>" size="16" />
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell"> <?php echo JText::_('VM_STORE_FORM_PHONE_2') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="phone_2" value="<?php $db->sp("phone_2") ?>" size="16" />
			      </td>
			    </tr>
			    <tr class="row0"> 
			      <td class="labelcell"> <?php echo JText::_('VM_STORE_FORM_FAX') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="fax" value="<?php $db->sp("fax") ?>" size="16" />
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td class="labelcell"> <?php echo JText::_('VM_STORE_FORM_EMAIL') ?>:</td>
			      <td width="78%" > 
			        <input type="text" class="inputbox" name="email" value="<?php $db->sp("email") ?>" size="32" />
			      </td>
			    </tr>
			    <tr class="row1"> 
			      <td colspan="2" align="center" >&nbsp;</td>
			    </tr>
			  </table>
			  </fieldset>
	      <br />
	      <fieldset><legend><?php echo JText::_('VM_CURRENCY_DISPLAY') ?></legend>
	      <table class="adminform"><?php
				/* Decode vendor_currency_display_style */
				$currency_display = $hVendor -> get_currency_display_style( $db->f("vendor_currency_display_style") );
				?>
		    <tr class="row0"> 
		      <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_CURRENCY') ?>: </td>
		      <td width="78%" > 
		        <?php $ps_html->list_currency("vendor_currency", $db->sf("vendor_currency")) ?>
		      </td>
		    </tr>
		    <tr class="row1">
		      <td class="labelcell"><?php echo JText::_('VM_CURRENCY_SYMBOL') ?>: </td>
		      <td>
		        <input type="hidden" name="display_style[0]" value="<?php echo $vendor_id; ?>" />
		        <input type="text" name="display_style[1]" value="<?php echo htmlspecialchars( $currency_display['symbol'] ); ?>" size="8" />
		        <?php echo vmToolTip( JText::_('VM_CURRENCY_SYMBOL_TOOLTIP')) ?>
		      </td>
		    </tr>
		    <tr class="row0">
		      <td class="labelcell"><?php echo JText::_('VM_CURRENCY_DECIMALS') ?>: </td>
		      <td><input type="text" name="display_style[2]" value="<?php echo $currency_display['nbdecimal']; ?>" size="1" />
		      <?php echo vmToolTip( JText::_('VM_CURRENCY_DECIMALS_TOOLTIP') ) ?>
		      </td>
		    </tr>
		    <tr class="row1">
		      <td class="labelcell"><?php echo JText::_('VM_CURRENCY_DECIMALSYMBOL') ?>: </td>
		      <td><input type="text" name="display_style[3]" value="<?php echo $currency_display['sdecimal']; ?>" size="1" />
		      <?php echo vmToolTip( JText::_('VM_CURRENCY_DECIMALSYMBOL_TOOLTIP') ) ?></td>
		    </tr>
		    <tr class="row0">
		      <td class="labelcell"><?php echo JText::_('VM_CURRENCY_THOUSANDS') ?>: </td>
		      <td><input type="text" name="display_style[4]" value="<?php echo $currency_display['thousands']; ?>" size="1" />
		      <?php echo vmToolTip( JText::_('VM_CURRENCY_THOUSANDS_TOOLTIP') )?></td>
		    </tr>
		    <tr class="row1">
		      <td class="labelcell"><?php echo JText::_('VM_CURRENCY_POSITIVE_DISPLAY') ?>: </td>
		      <td>
		        <?php 
		        ps_html::dropdown_display( 'display_style[5]', $currency_display['positive'], $currency_style_positive );
				echo vmToolTip( JText::_('VM_CURRENCY_POSITIVE_DISPLAY_TOOLTIP') ) ?>
		      </td>
		    </tr>
		    <tr class="row0">
		      <td class="labelcell"><?php echo JText::_('VM_CURRENCY_NEGATIVE_DISPLAY') ?>: </td>
		      <td>
		        <?php
		        ps_html::dropdown_display( 'display_style[6]', $currency_display['negative'], $currency_style_negative );
				echo vmToolTip( JText::_('VM_CURRENCY_NEGATIVE_DISPLAY_TOOLTIP') ) 
				?>
		      </td>
		    </tr>
		    <tr class="row1">
			    <td class="labelcell"><?php echo JText::_('VM_STORE_FORM_ACCEPTED_CURRENCIES') ?>:</td>
			    <td><?php 
			    	$currencies = $db->f('vendor_accepted_currencies') ? $db->f('vendor_accepted_currencies') : $vendor_currency;
			    	echo $ps_html->getCurrencyList('vendor_accepted_currencies[]', explode(',', $currencies), 'currency_code', '', 10, 'multiple="multiple"');
			    	echo ' '.vmToolTip( JText::_('VM_STORE_FORM_ACCEPTED_CURRENCIES_TIP') );
			     ?></td>
		    </tr>
		    </table>
		    </fieldset>
 		</td>
 	</tr>
</table><br />
<fieldset>
	<legend><?php echo JText::_('VM_STORE_FORM_DESCRIPTION') ?></legend>
	<?php
	editorArea( 'editor1', $db->f("vendor_store_desc"), 'vendor_store_desc', '400', '200', '70', '15' );
    ?>
</fieldset>
<fieldset>
	<legend><?php echo JText::_('VM_STORE_FORM_TOS') ?></legend>
    <?php
	editorArea( 'editor2', $db->f("vendor_terms_of_service"), 'vendor_terms_of_service', '400', '200', '70', '15' )
    ?>
</fieldset>
<?php

// Add necessary hidden fields
$formObj->hiddenField( 'vendor_id', $vendor_id );
$formObj->hiddenField( 'vendor_thumb_image_action', 'none' );
$formObj->hiddenField( 'vendor_full_image_action', 'none' );
$formObj->hiddenField( 'pshop_mode', 'admin' );

// Write your form with mixed tags and text fields
// and finally close the form:
$formObj->finishForm( "vendorUpdate", $modulename.'.display', $option );

?>

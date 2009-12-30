<?php 
defined('_JEXEC') or die('Restricted access');

AdminMenuHelper::startAdminArea(); 
?>
<form action="index.php" method="post" name="adminForm">

    <div class="col50">
	<table class="admintable">
	    <tr><td valign="top">
		    <fieldset class="adminform">
			<legend><?php echo JText::_('VM_STORE_MOD') ?></legend>
			<table class="admintable">
			    <tr>
				<td class="key">
				    <?php echo JText::_('VM_STORE_FORM_STORE_NAME'); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="vendor_store_name" id="vendor_store_name" size="50" value="<?php echo $this->store->vendor_store_name; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_('VM_STORE_FORM_COMPANY_NAME'); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="vendor_name" id="vendor_name" size="50" value="<?php echo $this->store->vendor_name; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_('VM_PRODUCT_FORM_URL'); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="vendor_url" id="vendor_url" size="50" value="<?php echo $this->store->vendor_url; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_('VM_STORE_FORM_ADDRESS_1'); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="address_1" id="address_1" size="50" value="<?php echo $this->store->userInfo->address_1; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_('VM_STORE_FORM_ADDRESS_2'); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="address_2" id="address_2" size="50" value="<?php echo $this->store->userInfo->address_2; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_('VM_STORE_FORM_CITY'); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="city" id="city" size="50" value="<?php echo $this->store->userInfo->city; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_('VM_STORE_FORM_COUNTRY'); ?>:
				</td>
				<td>
				    <?php echo ShopFunctions::renderCountryList($this->store->userInfo->country);?>
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_('VM_STORE_FORM_STATE'); ?>:
				</td>
				<td>
				    <?php echo ShopFunctions::renderStateList($this->store->userInfo->state, $this->store->userInfo->country, 'country_id');?>
				</td>
			    </tr>
			</table>
		    </fieldset>

		    <fieldset class="adminform">
			<legend><?php echo JText::_('VM_STORE_FORM_LBL') ?></legend>
			<table class="admintable">
			    <tr>
				<td class="key">
				    <?php echo JText::_('VM_STORE_FORM_FULL_IMAGE'); ?>:
				</td>
				<td>
				    <?php ImageHelper::displayShopImage($this->store->vendor_full_image, 'vendor', 'alt="Shop Image"', false); ?>
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_STORE_FORM_UPLOAD' ); ?>:
				</td>
				<td>
				    <input type="file" name="vendor_full_image" id="vendor_full_image" size="25" class="inputbox"  />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_STORE_FORM_MPOV' ); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="vendor_min_pov" id="vendor_min_pov" size="10" value="<?php echo $this->store->vendor_min_pov; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_FREE_SHIPPING_AMOUNT' ); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="vendor_freeshipping" id="vendor_freeshipping" size="10" value="<?php echo $this->store->vendor_freeshipping; ?>" />
				</td>
			    </tr>
			</table>
		    </fieldset>
		</td>
		<td valign="top">
		    <fieldset class="adminform">
			<legend><?php echo JText::_('VM_STORE_FORM_CONTACT_LBL') ?></legend>
			<table class="admintable">
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_STORE_FORM_LAST_NAME' ); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="last_name" id="last_name" size="50" value="<?php echo $this->store->userInfo->last_name; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_STORE_FORM_FIRST_NAME' ); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="first_name" id="first_name" size="50" value="<?php echo $this->store->userInfo->first_name; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_STORE_FORM_MIDDLE_NAME' ); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="middle_name" id="middle_name" size="20" value="<?php echo $this->store->userInfo->middle_name; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_STORE_FORM_TITLE' ); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="title" id="title" size="10" value="<?php echo $this->store->userInfo->title; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_STORE_FORM_PHONE_1' ); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="phone_1" id="phone_1" size="20" value="<?php echo $this->store->userInfo->phone_1; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_STORE_FORM_PHONE_2' ); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="phone_2" id="phone_2" size="20" value="<?php echo $this->store->userInfo->phone_2; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_STORE_FORM_FAX' ); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="fax" id="fax" size="20" value="<?php echo $this->store->userInfo->fax; ?>" />
				</td>
			    </tr>
			</table>
		    </fieldset>

		    <fieldset class="adminform">
			<legend><?php echo JText::_('VM_CURRENCY_DISPLAY') ?></legend>
			<table class="admintable">
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_STORE_FORM_CURRENCY' ); ?>:
				</td>
				<td>
				    <?php
				    echo JHTML::_('Select.genericlist', $this->currencies, 'vendor_currency', '', 'currency_id', 'currency_name', $this->store->vendor_currency);
				    ?>
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_CURRENCY_SYMBOL' ); ?>:
				</td>
				<td>
				    <input type="hidden" name="vendor_currency_display_style[0]" value="<?php echo $this->store->vendor_id; ?>" />
				    <input class="inputbox" type="text" name="vendor_currency_display_style[1]" id="currency_symbol" size="10" value="<?php echo CurrencyDisplay::getSymbol(); ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_CURRENCY_DECIMALS' ); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="vendor_currency_display_style[2]" id="currency_nbr_decimals" size="10" value="<?php echo CurrencyDisplay::getNbrDecimals();
					   ; ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_CURRENCY_DECIMALSYMBOL' ); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="vendor_currency_display_style[3]" id="currency_decimal_symbol" size="10" value="<?php echo CurrencyDisplay::getDecimalSymbol(); ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_CURRENCY_THOUSANDS' ); ?>:
				</td>
				<td>
				    <input class="inputbox" type="text" name="vendor_currency_display_style[4]" id="currency_thousands_seperator" size="10" value="<?php echo CurrencyDisplay::getThousandsSeperator(); ?>" />
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_CURRENCY_POSITIVE_DISPLAY' ); ?>:
				</td>
				<td>
				    <?php
				    $options = array();
				    $options[] = JHTML::_('select.option', '0', JText::_('00Symb') );
				    $options[] = JHTML::_('select.option', '1', JText::_('00 Symb'));
				    $options[] = JHTML::_('select.option', '2', JText::_('Symb00'));
				    $options[] = JHTML::_('select.option', '3', JText::_('Symb 00'));
				    echo JHTML::_('Select.genericlist', $options, 'vendor_currency_display_style[5]', 'size=1', 'value', 'text', CurrencyDisplay::getPositiveFormat());
				    ?>
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_CURRENCY_NEGATIVE_DISPLAY' ); ?>:
				</td>
				<td>
				    <?php
				    $options = array();
				    $options[] = JHTML::_('select.option', '0', JText::_('(Symb00)') );
				    $options[] = JHTML::_('select.option', '1', JText::_('-Symb00'));
				    $options[] = JHTML::_('select.option', '2', JText::_('Symb00-'));
				    $options[] = JHTML::_('select.option', '3', JText::_('(00Symb)'));
				    $options[] = JHTML::_('select.option', '4', JText::_('-00Symb') );
				    $options[] = JHTML::_('select.option', '5', JText::_('00-Symb'));
				    $options[] = JHTML::_('select.option', '6', JText::_('00Symb-'));
				    $options[] = JHTML::_('select.option', '7', JText::_('-00 Symb'));
				    $options[] = JHTML::_('select.option', '8', JText::_('-Symb 00'));
				    $options[] = JHTML::_('select.option', '9', JText::_('00 Symb-') );
				    $options[] = JHTML::_('select.option', '10', JText::_('Symb 00-'));
				    $options[] = JHTML::_('select.option', '11', JText::_('Symb -00'));
				    $options[] = JHTML::_('select.option', '12', JText::_('(Symb 00)'));
				    $options[] = JHTML::_('select.option', '13', JText::_('(00 Symb)'));
				    echo JHTML::_('Select.genericlist', $options, 'vendor_currency_display_style[6]', 'size=1', 'value', 'text', CurrencyDisplay::getNegativeFormat());
				    ?>
				</td>
			    </tr>
			    <tr>
				<td class="key">
				    <?php echo JText::_( 'VM_STORE_FORM_ACCEPTED_CURRENCIES' ); ?>:
				</td>
				<td>
				    <?php
				    echo JHTML::_('Select.genericlist', $this->currencies, 'vendor_accepted_currencies[]', 'size=10 multiple', 'currency_code', 'currency_name', $this->store->vendor_accepted_currencies);
				    ?>
				</td>
			    </tr>
			</table>
		    </fieldset>

		</td>
	    </tr>
	    <tr>
		<td colspan="2">
		    <fieldset>
			<legend><?php echo JText::_('VM_STORE_FORM_DESCRIPTION');?></legend>
			<?php echo $this->editor->display('vendor_store_desc', $this->store->vendor_store_desc, '100%', 220, 70, 15)?>
		    </fieldset>
		</td>
	    </tr>
	    <tr>
		<td colspan="2">
		    <fieldset>
			<legend><?php echo JText::_('VM_STORE_FORM_TOS');?></legend>
			<?php echo $this->editor->display('vendor_terms_of_service', $this->store->vendor_terms_of_service, '100%', 220, 70, 15)?>
		    </fieldset>
		</td>
	    </tr>
	</table>

    </div>

    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="view" value="store" />
    <input type="hidden" name="vendor_id" value="<?php echo $this->store->vendor_id; ?>" />
    <input type="hidden" name="cid" value="<?php echo $this->store->vendor_id; ?>" />
    <input type="hidden" name="user_info_id" value="<?php echo $this->store->userInfo->user_info_id; ?>" />
    <input type="hidden" name="user_id" value="<?php echo $this->store->userInfo->user_id; ?>" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
</form>


<?php AdminMenuHelper::endAdminArea(); ?> 
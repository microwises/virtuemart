<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Config
* @author RickG
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access'); 
?> 
<br />
<fieldset class="adminform">
    <legend><?php echo JText::_('VM_ADMIN_CFG_SHOP_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SHOP_OFFLINE_TIP'); ?>">
		<?php echo JText::_('VM_ADMIN_CFG_SHOP_OFFLINE',false); ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('shop_is_offline', $this->config->get('shop_is_offline')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key"><?php echo JText::_('VM_ADMIN_CFG_SHOP_OFFLINE_MSG') ?></td>
	    <td>
		<textarea rows="6" cols="35" name="offline_message"><?php echo $this->config->get('offline_message'); ?></textarea>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_USE_ONLY_AS_CATALOGUE_EXPLAIN'); ?>">
		<?php echo JText::_('VM_ADMIN_CFG_USE_ONLY_AS_CATALOGUE') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('use_as_catalog', $this->config->get('use_as_catalog')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SHOW_OUT_OF_STOCK_PRODUCTS_EXPLAIN'); ?>">
		<?php echo JText::_('VM_ADMIN_CFG_SHOW_OUT_OF_STOCK_PRODUCTS') ?>
		</span>
	    </td>
	    <td valign="top">
		<?php echo VmHTML::checkbox('show_out_of_stock_products', $this->config->get('show_out_of_stock_products')); ?>
	    </td>
	</tr>

	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_CFG_CURRENCY_MODULE_TIP'); ?>">
		<?php echo JText::_('VM_CFG_CURRENCY_MODULE') ?>
		</span>
	    </td>
	    <td>
		<?php echo JHTML::_('Select.genericlist', $this->currConverterList, 'currency_converter_module', 'size=1', 'value', 'text', $this->config->get('currency_converter_module')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_MAIL_FORMAT_EXPLAIN'); ?>">
		<?php echo JText::_('VM_ADMIN_CFG_MAIL_FORMAT') ?>
		</span>
		</td>
	    <td>
		<select name="order_mail_html" class="inputbox">
		    <option value="0" <?php if ($this->config->get('order_mail_html') == '0') echo 'selected="selected"'; ?>>
			<?php echo JText::_('VM_ADMIN_CFG_MAIL_FORMAT_TEXT') ?>
		    </option>
		    <option value="1" <?php if ($this->config->get('order_mail_html') == '1') echo 'selected="selected"'; ?>>
			<?php echo JText::_('VM_ADMIN_CFG_MAIL_FORMAT_HTML') ?>
		    </option>
		</select>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_CFG_CONTENT_PLUGINS_ENABLE_TIP'); ?>">
		<label for="conf_VM_CONTENT_PLUGINS_ENABLE"><?php echo JText::_('VM_CFG_CONTENT_PLUGINS_ENABLE') ?></label>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('content_plugins_enable', $this->config->get('content_plugins_enable')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_COUPONS_ENABLE_EXPLAIN'); ?>">
		<label for="coupons_enable"><?php echo JText::_('VM_COUPONS_ENABLE') ?></label>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('coupons_enable', $this->config->get('coupons_enable')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_COUPONS_EXPIRE_EXPLAIN'); ?>">
		<label for="coupons_default_expire"><?php echo JText::_('VM_COUPONS_EXPIRE') ?></label>
		</span>
	    </td>
		<td>
			<select name="coupons_default_expire" class="inputbox">
				<?php
					// TODO This must go to the view.html.php.... but then... that goes for most of the config sruff I'ld say :-S
					$_defaultExpTime = array(
						 '1,D' => '1 '.JText::_('DAY')
						,'1,W' => '1 '.JText::_('WEEK')
						,'2,W' => '2 '.JText::_('WEEKS')
						,'1,M' => '1 '.JText::_('MONTH')
						,'3,M' => '3 '.JText::_('MONTHS')
						,'6,M' => '6 '.JText::_('MONTHS')
						,'1,Y' => '1 '.JText::_('YEAR')
					);
					foreach ($_defaultExpTime as $_v => $_t) {
						echo '<option value="'.$_v.'"';
						if ($this->config->get('coupons_default_expire') == $_v) {
							echo ' selected="selected"';
						}
						echo ">$_t</option>\n";
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_REVIEW_EXPLAIN'); ?>">
		<label for="conf_PSHOP_ALLOW_REVIEWS"><?php echo JText::_('VM_ADMIN_CFG_REVIEW') ?></label>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('allow_reviews', $this->config->get('allow_reviews')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_REVIEWS_AUTOPUBLISH_TIP'); ?>">
		<label for="conf_VM_REVIEWS_AUTOPUBLISH"><?php echo JText::_('VM_REVIEWS_AUTOPUBLISH') ?></label>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('reviews_autopublish', $this->config->get('reviews_autopublish')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_REVIEW_MINIMUM_COMMENT_LENGTH_TIP'); ?>">
		<label for="conf_VM_REVIEWS_MINIMUM_COMMENT_LENGTH"><?php echo JText::_('VM_ADMIN_CFG_REVIEW_MINIMUM_COMMENT_LENGTH') ?></label>
		</span>
	    </td>
	    <td>
		<input type="text" size="6" id="reviews_minimum_comment_length" name="comment_min_length" class="inputbox" value="<?php echo $this->config->get('comment_min_length'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_REVIEW_MAXIMUM_COMMENT_LENGTH_TIP'); ?>" >
		<label for="conf_VM_REVIEWS_MAXIMUM_COMMENT_LENGTH"><?php echo JText::_('VM_ADMIN_CFG_REVIEW_MAXIMUM_COMMENT_LENGTH'); ?></label>
		</span>
	    </td>
	    <td>
		<input type="text" size="6" id="reviews_maximum_comment_length" name="comment_max_length" class="inputbox" value="<?php echo $this->config->get('comment_max_length'); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_AGREE_TERMS_ONORDER_EXPLAIN'); ?>">
		    <?php echo JText::_('VM_ADMIN_CFG_AGREE_TERMS_ONORDER') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('agree_to_tos_onorder', $this->config->get('agree_to_tos_onorder')); ?>
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_ONCHECKOUT_SHOW_LEGALINFO_TIP'); ?>">
		    <label for="conf_VM_ONCHECKOUT_SHOW_LEGALINFO"><?php echo JText::_('VM_ADMIN_ONCHECKOUT_SHOW_LEGALINFO') ?></label>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('oncheckout_show_legal_info', $this->config->get('oncheckout_show_legal_info')); ?>
	    </td>
	</tr>
    </table>
</fieldset>

<fieldset class="adminform">
    <legend><?php echo JText::_('VM_ADMIN_SECURITY_SETTINGS') ?></legend>
    <table class="admintable">
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_MODULES_FORCE_HTTPS_TIP'); ?>">
		<?php echo JText::_('VM_MODULES_FORCE_HTTPS') ?>
		</span>
	    </td>
	    <td>
		<?php
		echo JHTML::_('Select.genericlist', $this->moduleList, 'modules_force_https[]', 'size=4 multiple', 'module_id', 'module_name', $this->config->get('modules_force_https'));
		?>
	    </td>
	</tr>

	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_GENERALLY_PREVENT_HTTPS_TIP'); ?>">
		<?php echo JText::_('VM_GENERALLY_PREVENT_HTTPS') ?>
		</span>
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('generally_prevent_https', $this->config->get('generally_prevent_https')); ?>
	    </td>
	</tr>
	<?php
	//if( version_compare( $database->getVersion(), '4.0.2', '>=') ) { ?>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_ENCRYPTION_FUNCTION_TIP'); ?>">
		<?php echo JText::_('VM_ADMIN_ENCRYPTION_FUNCTION') ?>&nbsp;&nbsp;
		</span>
	    </td>
	    <td>
		<?php
		$options = array();
		$options[] = JHTML::_('select.option', 'ENCODE', JText::_('ENCODE (insecure)'));
		$options[] = JHTML::_('select.option', 'AES_ENCRYPT', JText::_('AES_ENCRYPT (strong security)'));
		echo JHTML::_('Select.genericlist', $options, 'encrypt_function', 'size=1', 'value', 'text', $this->config->get('encrypt_function'));
		?>
	    </td>
	</tr>
	<?php
	//}
	?>
	<tr>
	    <td class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_ENCRYPTION_KEY_TIP'); ?>">
		<?php echo JText::_('VM_ADMIN_ENCRYPTION_KEY') ?>&nbsp;&nbsp;
		</span>
		</td>
	    <td>
		<input type="text" name="encode_key" class="inputbox" size="40" value="<?php echo JText::_($this->config->get('encode_key')); ?>" />
	    </td>
	</tr>
	<tr>
	    <td class="key">
		<?php echo JText::_('VM_ADMIN_STORE_CREDITCARD_DATA'); ?>&nbsp;&nbsp;
	    </td>
	    <td>
		<?php echo VmHTML::checkbox('store_creditcard_data', $this->config->get('store_creditcard_data')); ?>
	    </td>
	</tr>
	<?php
	if (stristr(JFactory::getUser()->usertype, "admin")) { ?>
	<tr>
	    <td  class="key">
		<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_FRONTENDAMDIN_EXPLAIN'); ?>">
		    <?php echo JText::_('VM_ADMIN_CFG_FRONTENDAMDIN') ?>
		</span>
	    </td>
	    <td>
		    <?php echo VmHTML::checkbox('allow_frontendadmin_for_nonbackenders', $this->config->get('allow_frontendadmin_for_nonbackenders')); ?>
	    </td>
	</tr>
	    <?php
	}
	else {
	    echo '<input type="hidden" name="allow_frontendadmin_for_nonbackenders" value="'.$this->config->get('allow_frontendadmin_for_nonbackenders').'" />';
	}
	?>
    </table>
</fieldset>
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
$orderByFieldsArray = $this->config->get('browse_orderby_fields');
$orderByFields = array();
$orderByFields[] = JHTML::_('select.option', 'product_list', JText::_('VM_DEFAULT'));
$orderByFields[] = JHTML::_('select.option', 'product_name', JText::_('VM_PRODUCT_NAME_TITLE'));
$orderByFields[] = JHTML::_('select.option', 'product_price', JText::_('VM_PRODUCT_PRICE_TITLE'));
$orderByFields[] = JHTML::_('select.option', 'product_sku', JText::_('VM_CART_SKU'));
$orderByFields[] = JHTML::_('select.option', 'product_cdate', JText::_('VM_LATEST'));
$orderByFields[] = JHTML::_('select.option', 'product_sales', JText::_('VM_SALES'));
?> 
<br />
<table>
    <tr><td valign="top">

	    <fieldset class="adminform">
		<legend><?php echo JText::_('VM_ADMIN_CFG_SHOPFRONT_SETTINGS') ?></legend>
		<table class="admintable">
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_ERRORPAGE_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_ERRORPAGE') ?>
			</td>
			<td>
			    <input type="text" name="errorpage" class="inputbox" value="<?php echo JText::_($this->config->get('errorpage')); ?>" />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_URLSECURE_EXPLAIN'); ?>" />
			    <?php echo JText::_('VM_ADMIN_CFG_PDF_BUTTON') ?>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('pdf_button_enable', $this->config->get('pdf_button_enable')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_SHOW_EMAILFRIEND_TIP'); ?>" />
			    <label for="conf_VM_SHOW_EMAILFRIEND"><?php echo JText::_('VM_ADMIN_SHOW_EMAILFRIEND') ?></label>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_emailfriend', $this->config->get('show_emailfriend')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_SHOW_PRINTICON_TIP'); ?>" />
			    <label for="conf_VM_SHOW_PRINTICON"><?php echo JText::_('VM_ADMIN_SHOW_PRINTICON') ?></label></td>
			<td>
			    <?php echo VmHTML::checkbox('show_printicon', $this->config->get('show_printicon')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_NAV_AT_TOP_TIP'); ?>" />
			    <?php echo JText::_('VM_ADMIN_CFG_NAV_AT_TOP') ?>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_top_pagenav', $this->config->get('show_top_pagenav')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_BROWSE_ORDERBY_DEFAULT_FIELD_LBL_TIP'); ?>" />
			    <?php echo JText::_('VM_BROWSE_ORDERBY_DEFAULT_FIELD_LBL') ?>
			</td>
			<td>
			    <?php echo JHTML::_('Select.genericlist', $orderByFields, 'browse_orderby_field', 'size=1', 'value', 'text', $this->config->get('browse_orderby_field')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_BROWSE_ORDERBY_FIELDS_LBL_TIP'); ?>" />
			    <?php echo JText::_('VM_BROWSE_ORDERBY_FIELDS_LBL') ?>
			    fix
			</td>
			<td>
			    <?php
			    for ($i=0, $n=count($orderByFields); $i < $n; $i++) {
				$field = $orderByFields[$i];
				echo VmHTML::checkbox('browse_orderby_fields[]', in_array($field->value, $orderByFieldsArray));
				echo $field->text.'<br />';
			    } ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SHOW_PRODUCT_COUNT_TIP'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_SHOW_PRODUCT_COUNT') ?></td>
			<td>
			    <?php echo VmHTML::checkbox('show_products_in_category', $this->config->get('show_products_in_category')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_NOIMAGEPAGE_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_NOIMAGEPAGE') ?>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->noimagelist, 'no_image', 'size=1', 'value', 'text', $this->config->get('no_image'));
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SHOWVM_VERSION_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_SHOWVM_VERSION') ?>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_footer', $this->config->get('show_footer')); ?>
			</td>
		    </tr>
		</table>
	    </fieldset>

	</td><td valign="top">

	    <fieldset class="adminform">
		<legend><?php echo JText::_('VM_ADMIN_CFG_MORE_CORE_SETTINGS') ?></legend>
		<table class="admintable">
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_SELECT_THEME_TIP'); ?>"/>
			    <?php echo JText::_('VM_SELECT_THEME') ?>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->themelist, 'theme', 'size=1', 'value', 'text', $this->config->get('theme'));
			    $link = JROUTE::_('index.php?option=com_virtuemart&page=admin.theme_config_form&amp;theme='.basename(VM_THEMEURL));
			    $text = JText::_('VM_CONFIG');
			    ?>
			    <a href="<?php echo $link; ?>"><?php echo $text; ?></a>
			    fix
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRODUCTS_PER_ROW_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_PRODUCTS_PER_ROW') ?>
			</td>
			<td>
			    <input type="text" name="products_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('products_per_row') ?>" />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_CATEGORY_TEMPLATE_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_CATEGORY_TEMPLATE') ?>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->templatelist, 'category_template', 'size=1', 'value', 'text', $this->config->get('category_template'));
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_FLYPAGE_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_FLYPAGE') ?>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->flypagelist, 'flypage', 'size=1', 'value', 'text', $this->config->get('flypage'));
			    ?>
			</td>
		    </tr>
		    <tr>
				<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_MEDIA_CATEGORY_PATH_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_MEDIA_CATEGORY_PATH') ?>
				</td>
				<td>
					<input type="text" name="media_category_path"  size="40" class="inputbox" value="<?php echo $this->config->get('media_category_path') ?>" />
				</td>
		    </tr>
		    <tr>
				<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_MEDIA_PRODUCT_PATH_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_MEDIA_PRODUCT_PATH') ?>
				</td>
				<td>
					<input type="text" name="media_product_path"  size="40" class="inputbox" value="<?php echo $this->config->get('media_product_path') ?>" />
				</td>
		    </tr>
		    <?php
		    if( function_exists('imagecreatefromjpeg') ) {
			?>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_DYNAMIC_THUMBNAIL_RESIZING_TIP'); ?>"/>
			    <label for="conf_PSHOP_IMG_RESIZE_ENABLE"><?php echo JText::_('VM_ADMIN_CFG_DYNAMIC_THUMBNAIL_RESIZING') ?></label>
			</td>
			<td>
				<?php echo VmHTML::checkbox('img_resize_enable', $this->config->get('img_resize_enable')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_THUMBNAIL_WIDTH_TIP'); ?>"/>
				<?php echo JText::_('VM_ADMIN_CFG_THUMBNAIL_WIDTH') ?>
			</td>
			<td>
			    <input type="text" name="img_width" class="inputbox" value="<?php echo $this->config->get('img_width') ?>" />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SHOWVM_VERSION_EXPLAIN'); ?>"/>
				<?php echo JText::_('VM_ADMIN_CFG_THUMBNAIL_HEIGHT') ?>
			</td>
			<td>
			    <input type="text" name="img_height" class="inputbox" value="<?php echo $this->config->get('img_height') ?>" />
			</td>
		    </tr>
			<?php
		    }
		    else {
			echo '<tr>
        <td colspan="2"><strong>Dynamic Image Resizing is not available. The GD library seems to be missing.</strong>';
			echo '<input type="hidden" name="conf_PSHOP_IMG_RESIZE_ENABLE" value="0" />';
			echo '<input type="hidden" name="conf_PSHOP_IMG_WIDTH" value="'. $this->config->get('img_width') .'" />';
			echo '<input type="hidden" name="conf_PSHOP_IMG_HEIGHT" value="'. $this->config->get('img_height') .'" /></td></tr>';
		    }
		    ?>
		</table>
	    </fieldset>

	</td></tr>
</table>
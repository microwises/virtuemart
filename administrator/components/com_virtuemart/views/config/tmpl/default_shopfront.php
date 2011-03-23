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
/*$orderByFieldsArray = $this->config->get('browse_orderby_fields');
$orderByFields = array();
$orderByFields[] = JHTML::_('select.option', 'product_list', JText::_('VM_DEFAULT'));
$orderByFields[] = JHTML::_('select.option', 'product_name', JText::_('VM_PRODUCT_NAME_TITLE'));
$orderByFields[] = JHTML::_('select.option', 'product_price', JText::_('VM_PRODUCT_PRICE_TITLE'));
$orderByFields[] = JHTML::_('select.option', 'product_sku', JText::_('VM_CART_SKU'));
$orderByFields[] = JHTML::_('select.option', 'product_cdate', JText::_('VM_LATEST'));
$orderByFields[] = JHTML::_('select.option', 'product_sales', JText::_('VM_SALES'));*/
?>
<br />
<table>
    <tr><td valign="top">

	    <fieldset class="adminform">
		<legend><?php echo JText::_('VM_ADMIN_CFG_SHOPFRONT_SETTINGS') ?></legend>
		<table class="admintable">
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_ERRORPAGE_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_ERRORPAGE') ?>
			</span>
			</td>
			<td>
			    <input type="text" name="errorpage" class="inputbox" value="<?php echo JText::_($this->config->get('errorpage')); ?>" />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_URLSECURE_EXPLAIN'); ?>" >
			    <?php echo JText::_('VM_ADMIN_CFG_PDF_BUTTON') ?>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('pdf_button_enable', $this->config->get('pdf_button_enable')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_SHOW_EMAILFRIEND_TIP'); ?>">
			    <label for="conf_VM_SHOW_EMAILFRIEND"><?php echo JText::_('VM_ADMIN_SHOW_EMAILFRIEND') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_emailfriend', $this->config->get('show_emailfriend')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_SHOW_PRINTICON_TIP'); ?>" >
			    <label for="conf_VM_SHOW_PRINTICON"><?php echo JText::_('VM_ADMIN_SHOW_PRINTICON') ?></label>
			    </span>
			    </td>
			<td>
			    <?php echo VmHTML::checkbox('show_printicon', $this->config->get('show_printicon')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_NAV_AT_TOP_TIP'); ?>" >
			    <?php echo JText::_('VM_ADMIN_CFG_NAV_AT_TOP') ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_top_pagenav', $this->config->get('show_top_pagenav')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_BROWSE_ORDERBY_DEFAULT_FIELD_LBL_TIP'); ?>" >
			    <?php echo JText::_('VM_BROWSE_ORDERBY_DEFAULT_FIELD_LBL') ?>
			    </span>
			</td>
			<td>

			    <?php echo JHTML::_('Select.genericlist', $this->orderByFields->select, 'browse_orderby_field', 'size=1', 'value', 'text', $this->config->get('browse_orderby_field')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_BROWSE_ORDERBY_FIELDS_LBL_TIP'); ?>">
			    <?php echo JText::_('VM_BROWSE_ORDERBY_FIELDS_LBL') ?>
			    </span>
			</td>
			<td>
			    <?php echo $this->orderByFields->checkbox ; ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_BROWSE_SEARCH_FIELDS_LBL_TIP'); ?>">
			    <?php echo JText::_('VM_BROWSE_SEARCH_FIELDS_LBL') ?>
			    </span>
			</td>
			<td>
			    <?php echo $this->searchFields ; ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SHOW_PRODUCT_COUNT_TIP'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_SHOW_PRODUCT_COUNT') ?>
			    </span>
			    </td>
			<td>
			    <?php echo VmHTML::checkbox('show_products_in_category', $this->config->get('show_products_in_category')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_NOIMAGEPAGE_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_NOIMAGEPAGE') ?>
			    </span>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->noimagelist, 'no_image_set', 'size=1', 'value', 'text', $this->config->get('no_image_set'));
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_NOIMAGEFOUND_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_NOIMAGEFOUND') ?>
			    </span>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->noimagelist, 'no_image_found', 'size=1', 'value', 'text', $this->config->get('no_image_found'));
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SHOWVM_VERSION_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_SHOWVM_VERSION') ?>
			    </span>
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
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_SELECT_DEFAULT_SHOP_TEMPLATE_TIP'); ?>">
			    <?php echo JText::_('VM_SELECT_DEFAULT_SHOP_TEMPLATE') ?>
			    </span>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->jTemplateList, 'vmtemplate', 'size=1', 'value', 'name', $this->config->get('vmtemplate'));
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_MAIN_LAYOUT_TIP'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_MAIN_LAYOUT') ?>
			    </span>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->vmLayoutList, 'vmlayout', 'size=1', 'text', 'text', $this->config->get('vmlayout'));
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_CATEGORY_TEMPLATE_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_CATEGORY_TEMPLATE') ?>
			    </span>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->jTemplateList, 'categorytemplate', 'size=1', 'value', 'name', $this->config->get('categorytemplate'));
			    ?>
			</td>
		    </tr>
			<tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_CATEGORY_LAYOUT_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_CATEGORY_LAYOUT') ?>
			    </span>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->categoryLayoutList, 'categorylayout', 'size=1', 'text', 'text', $this->config->get('categorylayout'));
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRODUCT_LAYOUT_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_PRODUCT_LAYOUT') ?>
			    </span>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->productLayoutList, 'productlayout', 'size=1', 'text', 'text', $this->config->get('productlayout'));
			    ?>
			</td>
		    </tr>

		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_CATEGORIES_PER_ROW_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_CATEGORIES_PER_ROW') ?>
			    </span>
			</td>
			<td>
			    <input type="text" name="categories_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('categories_per_row') ?>" />
			</td>
		    </tr>
		    <tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_PRODUCTS_PER_ROW_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_PRODUCTS_PER_ROW') ?>
			    </span>
			</td>
			<td>
			    <input type="text" name="products_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('products_per_row') ?>" />
			</td>
		    </tr>
		    
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_FEATURED_PRODUCTS_PER_ROW_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_FEATURED_PRODUCTS_PER_ROW') ?>
			    </span>
			</td>
			<td>
			    <input type="text" name="featured_products_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('featured_products_per_row') ?>" />
			</td>
		    </tr>
		    
		     <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_TOPTEN_PRODUCTS_PER_ROW_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_TOPTEN_PRODUCTS_PER_ROW') ?>
			    </span>
			</td>
			<td>
			    <input type="text" name="topten_products_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('topten_products_per_row') ?>" />
			</td>
		    </tr>

		    <tr>
				<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_ASSETS_GENERAL_PATH_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_ASSETS_GENERAL_PATH') ?>
			    </span>
				</td>
				<td>
					<input type="text" name="assets_general_path"  size="60" class="inputbox" value="<?php echo $this->config->get('assets_general_path') ?>" />
				</td>
		    </tr>		    <tr>
				<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_MEDIA_CATEGORY_PATH_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_MEDIA_CATEGORY_PATH') ?>
			    </span>
				</td>
				<td>
					<input type="text" name="media_category_path"  size="60" class="inputbox" value="<?php echo $this->config->get('media_category_path') ?>" />
				</td>
		    </tr>
		    <tr>
				<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_MEDIA_PRODUCT_PATH_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_MEDIA_PRODUCT_PATH') ?>
			    </span>
				</td>
				<td>
					<input type="text" name="media_product_path"  size="60" class="inputbox" value="<?php echo $this->config->get('media_product_path') ?>" />
				</td>
		    </tr>
		    <tr>
				<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_MEDIA_MANUFACTURER_PATH_EXPLAIN'); ?>">
			    <?php echo JText::_('VM_ADMIN_CFG_MEDIA_MANUFACTURER_PATH') ?>
			    </span>
				</td>
				<td>
					<input type="text" name="media_manufacturer_path"  size="60" class="inputbox" value="<?php echo $this->config->get('media_manufacturer_path') ?>" />
				</td>
		    </tr>
		    <?php
		    if( function_exists('imagecreatefromjpeg') ) {
			?>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_DYNAMIC_THUMBNAIL_RESIZING_TIP'); ?>">
			    <label for="conf_PSHOP_IMG_RESIZE_ENABLE"><?php echo JText::_('VM_ADMIN_CFG_DYNAMIC_THUMBNAIL_RESIZING') ?></label>
			    </span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('img_resize_enable', $this->config->get('img_resize_enable')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_THUMBNAIL_WIDTH_TIP'); ?>">
				<?php echo JText::_('VM_ADMIN_CFG_THUMBNAIL_WIDTH') ?>
				</span>
			</td>
			<td>
			    <input type="text" name="img_width" class="inputbox" value="<?php echo $this->config->get('img_width') ?>" />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SHOWVM_VERSION_EXPLAIN'); ?>">
				<?php echo JText::_('VM_ADMIN_CFG_THUMBNAIL_HEIGHT') ?>
				</span>
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
			echo '<input type="hidden" name="img_resize_enable" value="0" />';
			echo '<input type="hidden" name="img_width" value="'. $this->config->get('img_width',90) .'" />';
			echo '<input type="hidden" name="img_height" value="'. $this->config->get('img_height',90) .'" /></td></tr>';
		    }
		    ?>
			<tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_DATEFORMAT_EXPLAIN'); ?>">
				<?php echo JText::_('VM_ADMIN_CFG_DATEFORMAT') ?>
				</span>
			</td>
			<td>
			    <input type="text" name="dateformat" class="inputbox" value="<?php echo $this->config->get('dateformat') ?>" />
			</td>
		    </tr>
		</table>
	    </fieldset>

	</td></tr>
</table>
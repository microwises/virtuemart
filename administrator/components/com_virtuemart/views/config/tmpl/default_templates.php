<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Config
* @author Max Milbers
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default_templates.php 4115 2011-09-15 $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>
<br />
<table width="100%">
   <tr>
	<td valign="top" width="50%">
		<fieldset>
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOPFRONT_SETTINGS') ?></legend>
		<table class="admintable">
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_SELECT_DEFAULT_SHOP_TEMPLATE_TIP'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_SELECT_DEFAULT_SHOP_TEMPLATE') ?>
			    </span>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->jTemplateList, 'vmtemplate', 'size=1 width=200', 'value', 'name', $this->config->get('vmtemplate'));
			    ?>
			</td>
		    </tr>

		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CATEGORY_TEMPLATE_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CATEGORY_TEMPLATE') ?>
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
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_CATEGORY_EXPLAIN'); ?>">
			    <label for="showCategory"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_CATEGORY') ?></label>
			    </span>
			</td>
			<td>
			   <?php echo VmHTML::checkbox('showCategory', $this->config->get('showCategory',1)); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_MANUFACTURERS_EXPLAIN'); ?>">
			    <label for="show_manufacturers"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_MANUFACTURERS') ?></label>
			    </span>
			</td>
			<td>
			   <?php echo VmHTML::checkbox('show_manufacturers', $this->config->get('show_manufacturers', 1)); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CATEGORY_LAYOUT_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CATEGORY_LAYOUT') ?>
			    </span>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->categoryLayoutList, 'categorylayout', 'size=1', 'value', 'text', $this->config->get('categorylayout'));
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CATEGORIES_PER_ROW_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CATEGORIES_PER_ROW') ?>
			    </span>
			</td>
			<td>
			    <input type="text" name="categories_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('categories_per_row') ?>" />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRODUCT_LAYOUT_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRODUCT_LAYOUT') ?>
			    </span>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->productLayoutList, 'productlayout', 'size=1', 'value', 'text', $this->config->get('productlayout'));
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRODUCTS_PER_ROW_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRODUCTS_PER_ROW') ?>
			    </span>
			</td>
			<td>
			    <input type="text" name="products_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('products_per_row') ?>" />
			</td>
		    </tr>

	 	<tr>
		<td class="key">
		    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MANUFACTURER_PER_ROW_EXPLAIN'); ?>">
		    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MANUFACTURER_PER_ROW') ?>
		    </span>
		</td>
		<td>
		    <input type="text" name="manufacturer_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('manufacturer_per_row') ?>" />
		</td>
	    </tr>
	    <tr>
		<td class="key">
		    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PAGINATION_SEQUENCE_EXPLAIN'); ?>">
		    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PAGINATION_SEQUENCE') ?>
		    </span>
		</td>
		<td>
		    <input type="text" name="pagination_sequence" class="inputbox" value="<?php echo $this->config->get('pagination_sequence') ?>" />
		</td>
	    </tr>
      </table>
      </fieldset>
	</td>
	<td>
      <fieldset>
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_HOMEPAGE_SETTINGS') ?></legend>
                    <table class="admintable">
                           <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIN_LAYOUT_TIP'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIN_LAYOUT') ?>
			    </span>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->vmLayoutList, 'vmlayout', 'size=1', 'value', 'text', $this->config->get('vmlayout'));
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_FEATURED_TIP'); ?>" >
			    <label for="show_featured"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_FEATURED') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_featured', $this->config->get('show_featured')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEATURED_PRODUCTS_PER_ROW_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEATURED_PRODUCTS_PER_ROW') ?>
			    </span>
			</td>
			<td>
			    <input type="text" name="featured_products_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('featured_products_per_row') ?>" />
			</td>
		    </tr>
			<tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_TOPTEN_TIP'); ?>" >
			    <label for="show_topTen"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_TOPTEN') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_topTen', $this->config->get('show_topTen')); ?>
			</td>
		    </tr>
		     <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_TOPTEN_PRODUCTS_PER_ROW_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_TOPTEN_PRODUCTS_PER_ROW') ?>
			    </span>
			</td>
			<td>
			    <input type="text" name="topten_products_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('topten_products_per_row') ?>" />
			</td>
		    </tr>
			<tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_RECENT_TIP'); ?>" >
			    <label for="show_recent"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_RECENT') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_recent', $this->config->get('show_recent')); ?>
			</td>
		    </tr>

		    <tr>
			<td class="key">
			    <span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_LATEST_TIP'); ?>" >
			    <label for="show_latest"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_LATEST') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_latest', $this->config->get('show_latest')); ?>
			</td>
		    </tr>
		</table>
	    </fieldset>
	</td>
</tr>

<tr>
	<td>
	<fieldset>
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_TITLE') ?></legend>
		<table class="admintable">
			<tr>
				<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ASSETS_GENERAL_PATH_EXPLAIN'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ASSETS_GENERAL_PATH') ?>
				</span>
				</td>
				<td>
					<input type="text" name="assets_general_path"  size="60" class="inputbox" value="<?php echo $this->config->get('assets_general_path') ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_CATEGORY_PATH_EXPLAIN'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_CATEGORY_PATH') ?>
				</span>
				</td>
				<td>
					<input type="text" name="media_category_path"  size="60" class="inputbox" value="<?php echo $this->config->get('media_category_path') ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_PRODUCT_PATH_EXPLAIN'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_PRODUCT_PATH') ?>
				</span>
				</td>
				<td>
					<input type="text" name="media_product_path"  size="60" class="inputbox" value="<?php echo $this->config->get('media_product_path') ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_MANUFACTURER_PATH_EXPLAIN'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_MANUFACTURER_PATH') ?>
				</span>
				</td>
				<td>
					<input type="text" name="media_manufacturer_path"  size="60" class="inputbox" value="<?php echo $this->config->get('media_manufacturer_path') ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_VENDOR_PATH_EXPLAIN'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_VENDOR_PATH') ?>
				</span>
				</td>
				<td>
					<input type="text" name="media_vendor_path"  size="60" class="inputbox" value="<?php echo $this->config->get('media_vendor_path') ?>" />
				</td>
			</tr>
			<tr>
				<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH_EXPLAIN'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH') ?>
				</span>
			</td>
			<td>
				<input type="text" name="forSale_path"  size="60" class="inputbox" value="<?php echo $this->config->get('forSale_path') ?>" />
			</td>
			</tr>
			<tr>
			<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH_THUMB_EXPLAIN'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH_THUMB') ?>
				</span>
			</td>
			<td>
				<input type="text" name="forSale_path_thumb"  size="60" class="inputbox" value="<?php echo $this->config->get('forSale_path_thumb') ?>" />
			</td>
			</tr>
			<?php
			if( function_exists('imagecreatefromjpeg') ) {
				?>
				<tr>
					<td class="key">
						<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DYNAMIC_THUMBNAIL_RESIZING_TIP'); ?>">
						<label for="img_resize_enable"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DYNAMIC_THUMBNAIL_RESIZING') ?></label>
						</span>
					</td>
					<td>
						<?php echo VmHTML::checkbox('img_resize_enable', $this->config->get('img_resize_enable')); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_THUMBNAIL_WIDTH_TIP'); ?>">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_THUMBNAIL_WIDTH') ?>
						</span>
					</td>
					<td>
						<input type="text" name="img_width" class="inputbox" value="<?php echo $this->config->get('img_width') ?>" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_THUMBNAIL_HEIGHT_TIP'); ?>">
						<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_THUMBNAIL_HEIGHT') ?>
						</span>
					</td>
					<td>
						<input type="text" name="img_height" class="inputbox" value="<?php echo $this->config->get('img_height') ?>" />
					</td>
				</tr>
				<?php
			}
			else { ?>
				<tr>
					<td colspan="2"><strong><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_GD_MISSING') ?></strong>
						<input type="hidden" name="img_resize_enable" value="0" />
						<input type="hidden" name="img_width" value="<?php echo  $this->config->get('img_width',90) ?>" />
						<input type="hidden" name="img_height" value="<?php echo  $this->config->get('img_height',90) ?>" />
					</td>
				</tr>
			<?php }
			?>
			<tr>
			<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_NOIMAGEPAGE_EXPLAIN'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_NOIMAGEPAGE') ?>
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
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_NOIMAGEFOUND_EXPLAIN'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_NOIMAGEFOUND') ?>
				</span>
			</td>
			<td>
				<?php
				echo JHTML::_('Select.genericlist', $this->noimagelist, 'no_image_found', 'size=1', 'value', 'text', $this->config->get('no_image_found'));
				?>
			</td>
			</tr>
		</table>
		</fieldset>

	<td>
		<fieldset>
		<legend class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FRONT_CSS_JS_SETTINGS_TIP'); ?>"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FRONT_CSS_JS_SETTINGS') ?></legend>
		<table class="admintable">
			<tr>
			<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FRONT_CSS_TIP'); ?>">
				<label for="css"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FRONT_CSS') ?></label>
				</span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('css', $this->config->get('css',1)); ?>
			</td>
			</tr>
			<tr>
			<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FRONT_JQUERY_TIP'); ?>">
				<label for="jquery"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FRONT_JQUERY') ?></label>
				</span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('jquery', $this->config->get('jquery',1)); ?>
			</td>
			</tr>
			<tr>
			<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FRONT_JPRICE_TIP'); ?>">
				<label for="jprice"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FRONT_JPRICE') ?></label>
				</span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('jprice', $this->config->get('jprice',1)); ?>
			</td>
			</tr>
			<tr>
			<td class="key">
				<span class="hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FRONT_JSITE_TIP'); ?>">
				<label for="jsite"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FRONT_JSITE') ?></label>
				</span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('jsite', $this->config->get('jsite',1)); ?>
			</td>
			</tr>
		</table>
	    </fieldset>
	</td>

  </tr>
</table>

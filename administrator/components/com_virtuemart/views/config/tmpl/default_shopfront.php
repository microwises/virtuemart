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
$orderByFields[] = JHTML::_('select.option', 'ordering', JText::_('COM_VIRTUEMART_DEFAULT'));
$orderByFields[] = JHTML::_('select.option', 'product_name', JText::_('COM_VIRTUEMART_PRODUCT_NAME'));
$orderByFields[] = JHTML::_('select.option', 'product_price', JText::_('COM_VIRTUEMART_PRODUCT_PRICE'));
$orderByFields[] = JHTML::_('select.option', 'product_sku', JText::_('COM_VIRTUEMART_CART_SKU'));
$orderByFields[] = JHTML::_('select.option', 'product_cdate', JText::_('COM_VIRTUEMART_LATEST'));
$orderByFields[] = JHTML::_('select.option', 'product_sales', JText::_('COM_VIRTUEMART_SALES'));*/
?>
<br />
<table width="100%">
    <tr><td valign="top" width="50%">
	    <fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MORE_CORE_SETTINGS') ?></legend>
		<table class="admintable">
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ERRORPAGE_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ERRORPAGE') ?>
			</span>
			</td>
			<td>
			    <input type="text" name="errorpage" class="inputbox" value="<?php echo $this->config->get('errorpage'); ?>" />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PDF_BUTTON_EXPLAIN'); ?>" >
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PDF_BUTTON') ?>
			</span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('pdf_button_enable', $this->config->get('pdf_button_enable')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_SHOW_EMAILFRIEND_TIP'); ?>">
			    <label for="conf_VM_SHOW_EMAILFRIEND"><?php echo JText::_('COM_VIRTUEMART_ADMIN_SHOW_EMAILFRIEND') ?></label>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_emailfriend', $this->config->get('show_emailfriend')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_SHOW_PRINTICON_TIP'); ?>" >
			    <label for="conf_VM_SHOW_PRINTICON"><?php echo JText::_('COM_VIRTUEMART_ADMIN_SHOW_PRINTICON') ?></label>
			    </span>
			    </td>
			<td>
			    <?php echo VmHTML::checkbox('show_printicon', $this->config->get('show_printicon')); ?>
			</td>
		    </tr>
			<tr>
	    	<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_CFG_CONTENT_PLUGINS_ENABLE_TIP'); ?>">
				<label for="conf_VM_CONTENT_PLUGINS_ENABLE"><?php echo JText::_('COM_VIRTUEMART_CFG_CONTENT_PLUGINS_ENABLE') ?></label>
				</span>
	    	</td>
	    	<td>
				<?php echo VmHTML::checkbox('content_plugins_enable', $this->config->get('content_plugins_enable')); ?>
	    	</td>
			</tr>
			<tr>
	    	<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_OUT_OF_STOCK_PRODUCTS_EXPLAIN'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_OUT_OF_STOCK_PRODUCTS') ?>
				</span>
	    	</td>
	    	<td valign="top">
				<?php echo VmHTML::checkbox('show_out_of_stock_products', $this->config->get('show_out_of_stock_products')); ?>
	    	</td>
			</tr>
			<tr>
	    	<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_COUPONS_ENABLE_EXPLAIN'); ?>">
				<label for="coupons_enable"><?php echo JText::_('COM_VIRTUEMART_COUPONS_ENABLE') ?></label>
				</span>
	   	 	</td>
	    	<td>
				<?php echo VmHTML::checkbox('coupons_enable', $this->config->get('coupons_enable')); ?>
	    	</td>
			</tr>
			<tr>
	    	<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_COUPONS_EXPIRE_EXPLAIN'); ?>">
				<label for="coupons_default_expire"><?php echo JText::_('COM_VIRTUEMART_COUPONS_EXPIRE') ?></label>
				</span>
	    	</td>
			<td>
			<select name="coupons_default_expire" class="inputbox">
				<?php
					// TODO This must go to the view.html.php.... but then... that goes for most of the config sruff I'ld say :-S
					$_defaultExpTime = array(
						 '1,D' => '1 '.JText::_('COM_VIRTUEMART_DAY')
						,'1,W' => '1 '.JText::_('COM_VIRTUEMART_WEEK')
						,'2,W' => '2 '.JText::_('COM_VIRTUEMART_WEEK_S')
						,'1,M' => '1 '.JText::_('COM_VIRTUEMART_MONTH')
						,'3,M' => '3 '.JText::_('COM_VIRTUEMART_MONTH_S')
						,'6,M' => '6 '.JText::_('COM_VIRTUEMART_MONTH_S')
						,'1,Y' => '1 '.JText::_('COM_VIRTUEMART_YEAR')
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
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOWVM_VERSION_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOWVM_VERSION') ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_footer', $this->config->get('show_footer')); ?>
			</td>
		    </tr>
		</table>
	    </fieldset>

	    <fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_TITLE') ?></legend>
		<table class="admintable">
			<tr>
		    	<td class="key">
					<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_EXPLAIN'); ?>">
					<label ><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW') ?></label>
					</span>
		    	</td>
		    	<td><fieldset class="radio">
		    	<?php
		    		$showReviewFor = array(	0 => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_NONE'),
											1 => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_REGISTERED'),
											2 => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_SHOW_ALL')
											); //showReviewFor
					echo VmHTML::radioList('showReviewFor', $this->config->get('showReviewFor',2),$showReviewFor); ?>

		    	</fieldset></td>
			</tr>
			<tr>
	    	<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_EXPLAIN'); ?>">
				<label ><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW') ?></label>
				</span>
	    	</td>
	    	<td><fieldset class="radio">
				<?php
				 $showReviewFor = array(0 => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_NONE'),
				 						1 => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_BOUGHT_PRODUCT'),
				 						2 => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_REGISTERED'),
				 						3 => JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MODE_ALL')
										); //showReviewFor
				echo VmHTML::radioList('reviewMode', $this->config->get('reviewMode',2),$showReviewFor); ?>
	    	</fieldset></td>
			</tr>
			<tr>
		    	<td class="key">
					<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW_EXPLAIN'); ?>">
					<label><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW') ?></label>
					</span>
		    	</td>
		    	<td><fieldset class="radio">
		    	<?php
		    		$showReviewFor = array(	0 => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW_NONE'),
		    								1 => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW_REGISTERED'),
											2 => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_SHOW_ALL')
											); //showReviewFor
					echo VmHTML::radioList('showRatingFor', $this->config->get('showRatingFor',2),$showReviewFor); ?>

		    	</fieldset></td>
				</tr>
			<tr>
	    	<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_EXPLAIN'); ?>">
				<label ><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING') ?></label>
				</span>
	    	</td>
	    	<td><fieldset class="radio">
				<?php
				 $showReviewFor = array(0 => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_MODE_NONE'),
				 						1 => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_MODE_BOUGHT_PRODUCT'),
										2 => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_MODE_REGISTERED'),
										3 => JText::_('COM_VIRTUEMART_ADMIN_CFG_RATING_MODE_ALL')
										); //showReviewFor
				echo VmHTML::radioList('ratingMode', $this->config->get('ratingMode',2),$showReviewFor); ?>
	    	</fieldset></td>
			</tr>
			<tr>
	    	<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_REVIEWS_AUTOPUBLISH_TIP'); ?>">
				<label for="conf_VM_REVIEWS_AUTOPUBLISH"><?php echo JText::_('COM_VIRTUEMART_REVIEWS_AUTOPUBLISH') ?></label>
			</span>
	    	</td>
	    	<td>
				<?php echo VmHTML::checkbox('reviews_autopublish', $this->config->get('reviews_autopublish')); ?>
	    	</td>
			</tr>
			<tr>
	    	<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MINIMUM_COMMENT_LENGTH_TIP'); ?>">
				<label for="conf_VM_REVIEWS_MINIMUM_COMMENT_LENGTH"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MINIMUM_COMMENT_LENGTH') ?></label>
				</span>
	    	</td>
	    	<td>
				<input type="text" size="6" id="reviews_minimum_comment_length" name="reviews_minimum_comment_length" class="inputbox" value="<?php echo $this->config->get('reviews_minimum_comment_length'); ?>" />
	   	 	</td>
			</tr>
			<tr>
	    	<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MAXIMUM_COMMENT_LENGTH_TIP'); ?>" >
				<label for="conf_VM_REVIEWS_MAXIMUM_COMMENT_LENGTH"><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_REVIEW_MAXIMUM_COMMENT_LENGTH'); ?></label>
				</span>
	   	 	</td>
	    	<td>
			<input type="text" size="6" id="reviews_maximum_comment_length" name="reviews_maximum_comment_length" class="inputbox" value="<?php echo $this->config->get('reviews_maximum_comment_length'); ?>" />
	    	</td>
			</tr>
		</table>
		</fieldset>

	</td><td valign="top" width="50%">

	    <fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOPFRONT_SETTINGS') ?></legend>
		<table class="admintable">
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_SELECT_DEFAULT_SHOP_TEMPLATE_TIP'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_SELECT_DEFAULT_SHOP_TEMPLATE') ?>
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
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIN_LAYOUT_TIP'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MAIN_LAYOUT') ?>
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
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CATEGORY_TEMPLATE_EXPLAIN'); ?>">
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
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_CATEGORY_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_CATEGORY') ?>
			    </span>
			</td>
			<td>
			   <?php echo VmHTML::checkbox('showCategory', $this->config->get('showCategory',1)); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CATEGORY_LAYOUT_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CATEGORY_LAYOUT') ?>
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
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CATEGORIES_PER_ROW_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_CATEGORIES_PER_ROW') ?>
			    </span>
			</td>
			<td>
			    <input type="text" name="categories_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('categories_per_row') ?>" />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRODUCT_LAYOUT_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRODUCT_LAYOUT') ?>
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
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRODUCTS_PER_ROW_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_PRODUCTS_PER_ROW') ?>
			    </span>
			</td>
			<td>
			    <input type="text" name="products_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('products_per_row') ?>" />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_NAV_AT_TOP_TIP'); ?>" >
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_NAV_AT_TOP') ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_top_pagenav', $this->config->get('show_top_pagenav')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_FEATURED_TIP'); ?>" >
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_FEATURED') ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_featured', $this->config->get('show_featured')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEATURED_PRODUCTS_PER_ROW_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_FEATURED_PRODUCTS_PER_ROW') ?>
			    </span>
			</td>
			<td>
			    <input type="text" name="featured_products_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('featured_products_per_row') ?>" />
			</td>
		    </tr>
			<tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_TOPTEN_TIP'); ?>" >
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_TOPTEN') ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_topTen', $this->config->get('show_topTen')); ?>
			</td>
		    </tr>
		     <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_TOPTEN_PRODUCTS_PER_ROW_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_TOPTEN_PRODUCTS_PER_ROW') ?>
			    </span>
			</td>
			<td>
			    <input type="text" name="topten_products_per_row" size="4" class="inputbox" value="<?php echo $this->config->get('topten_products_per_row') ?>" />
			</td>
		    </tr>
			<tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_RECENT_TIP'); ?>" >
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_RECENT') ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_recent', $this->config->get('show_recent')); ?>
			</td>
		    </tr>

		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_LATEST_TIP'); ?>" >
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOW_LATEST') ?>
			    </span>
			</td>
			<td>
			    <?php echo VmHTML::checkbox('show_latest', $this->config->get('show_latest')); ?>
			</td>
		    </tr>
		</table>
	    </fieldset>
	</td></tr>
</table>

<table width="100%">
    <tr><td valign="top">
		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_TITLE') ?></legend>
		<table class="admintable">
	    	<tr>
				<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ASSETS_GENERAL_PATH_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_ASSETS_GENERAL_PATH') ?>
			    </span>
				</td>
				<td>
					<input type="text" name="assets_general_path"  size="60" class="inputbox" value="<?php echo $this->config->get('assets_general_path') ?>" />
				</td>
		    </tr>
		    <tr>
				<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_CATEGORY_PATH_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_CATEGORY_PATH') ?>
			    </span>
				</td>
				<td>
					<input type="text" name="media_category_path"  size="60" class="inputbox" value="<?php echo $this->config->get('media_category_path') ?>" />
				</td>
		    </tr>
		    <tr>
				<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_PRODUCT_PATH_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_PRODUCT_PATH') ?>
			    </span>
				</td>
				<td>
					<input type="text" name="media_product_path"  size="60" class="inputbox" value="<?php echo $this->config->get('media_product_path') ?>" />
				</td>
		    </tr>
		    <tr>
				<td class="key">
				<span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_MANUFACTURER_PATH_EXPLAIN'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_MANUFACTURER_PATH') ?>
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
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DYNAMIC_THUMBNAIL_RESIZING_TIP'); ?>">
			    <label ><?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_DYNAMIC_THUMBNAIL_RESIZING') ?></label>
			    </span>
			</td>
			<td>
				<?php echo VmHTML::checkbox('img_resize_enable', $this->config->get('img_resize_enable')); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_THUMBNAIL_WIDTH_TIP'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_THUMBNAIL_WIDTH') ?>
				</span>
			</td>
			<td>
			    <input type="text" name="img_width" class="inputbox" value="<?php echo $this->config->get('img_width') ?>" />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_SHOWVM_VERSION_EXPLAIN'); ?>">
				<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_THUMBNAIL_HEIGHT') ?>
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
        <td colspan="2"><strong>'.JText::_('COM_VIRTUEMART_ADMIN_CFG_GD_MISSING') .'</strong>';
			echo '<input type="hidden" name="img_resize_enable" value="0" />';
			echo '<input type="hidden" name="img_width" value="'. $this->config->get('img_width',90) .'" />';
			echo '<input type="hidden" name="img_height" value="'. $this->config->get('img_height',90) .'" /></td></tr>';
		    }
		    ?>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_NOIMAGEPAGE_EXPLAIN'); ?>">
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
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_ADMIN_CFG_NOIMAGEFOUND_EXPLAIN'); ?>">
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
	</td></tr>
</table>

<table>
    <tr><td valign="top">
		<fieldset class="adminform">
		<legend><?php echo JText::_('COM_VIRTUEMART_BROWSE_ORDERBY_DEFAULT_FIELD_TITLE') ?></legend>
		<table class="admintable">
			<tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_BROWSE_ORDERBY_DEFAULT_FIELD_LBL_TIP'); ?>" >
			    <?php echo JText::_('COM_VIRTUEMART_BROWSE_ORDERBY_DEFAULT_FIELD_LBL') ?>
			    </span>
			</td>
			<td>

			    <?php echo JHTML::_('Select.genericlist', $this->orderByFields->select, 'browse_orderby_field', 'size=1', 'value', 'text', $this->config->get('browse_orderby_field'),'virtuemart_product_id'); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_BROWSE_ORDERBY_FIELDS_LBL_TIP'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_BROWSE_ORDERBY_FIELDS_LBL') ?>
			    </span>
			</td>
			<td><fieldset class="radio">
			    <?php echo $this->orderByFields->checkbox ; ?>
			</fieldset></td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('COM_VIRTUEMART_BROWSE_SEARCH_FIELDS_LBL_TIP'); ?>">
			    <?php echo JText::_('COM_VIRTUEMART_BROWSE_SEARCH_FIELDS_LBL') ?>
			    </span>
			</td>
			<td><fieldset class="radio">
			    <?php echo $this->searchFields ; ?>
			</fieldset></td>
		    </tr>
		    <tr>
		</table>
		</fieldset>
	</td></tr>
</table>
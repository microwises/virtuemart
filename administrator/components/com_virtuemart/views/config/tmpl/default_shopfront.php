<?php
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
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_HOMEPAGE_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_HOMEPAGE') ?>
			</td>
			<td>
			    <input type="text" name="homepage" class="inputbox" value="<?php echo JText::_($this->config->get('homepage')); ?>" />
			</td>
		    </tr>
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
			    <?php
			    $checked = '';
			    if ($this->config->get('pdf_button_enable')) $checked = 'checked="checked"'; ?>
			    <input type="checkbox" name="pdf_button_enable" value="1" <?php echo $checked; ?> />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_SHOW_EMAILFRIEND_TIP'); ?>" />
			    <label for="conf_VM_SHOW_EMAILFRIEND"><?php echo JText::_('VM_ADMIN_SHOW_EMAILFRIEND') ?></label>
			</td>
			<td>
			    <?php
			    $checked = '';
			    if ($this->config->get('show_emailfriend')) $checked = 'checked="checked"'; ?>
			    <input type="checkbox" name="show_emailfriend" value="1" <?php echo $checked; ?> />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_SHOW_PRINTICON_TIP'); ?>" />
			    <label for="conf_VM_SHOW_PRINTICON"><?php echo JText::_('VM_ADMIN_SHOW_PRINTICON') ?></label></td>
			<td>
			    <?php
			    $checked = '';
			    if ($this->config->get('show_printicon')) $checked = 'checked="checked"'; ?>
			    <input type="checkbox" name="show_printicon" value="1" <?php echo $checked; ?> />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_NAV_AT_TOP_TIP'); ?>" />
			    <?php echo JText::_('VM_ADMIN_CFG_NAV_AT_TOP') ?>
			</td>
			<td>
			    <?php
			    $checked = '';
			    if ($this->config->get('show_top_pagenav')) $checked = 'checked="checked"'; ?>
			    <input type="checkbox" name="show_top_pagenav" value="1" <?php echo $checked; ?> />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_BROWSE_ORDERBY_DEFAULT_FIELD_LBL_TIP'); ?>" />
			    <?php echo JText::_('VM_BROWSE_ORDERBY_DEFAULT_FIELD_LBL') ?>
			</td>
			<td>
			    <?php echo JHTML::_('Select.genericlist', $orderByFields, 'browse_orderby_field', 'size=1'); ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_BROWSE_ORDERBY_FIELDS_LBL_TIP'); ?>" />
			    <?php echo JText::_('VM_BROWSE_ORDERBY_FIELDS_LBL') ?>
			</td>
			<td>
			    <?php
			    for ($i=0, $n=count($orderByFields); $i < $n; $i++) {
				$field = $orderByFields[$i];
				$checked = '';
				if (in_array($field->value, $orderByFieldsArray)) $checked = 'checked="checked"'; ?>
			    <input type="checkbox" name="browse_orderby_fields[]" value="1" <?php echo $checked; ?> />
				<?php echo $field->text.'<br />';
			    } ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SHOW_PRODUCT_COUNT_TIP'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_SHOW_PRODUCT_COUNT') ?></td>
			<td>
			    <?php
			    $checked = '';
			    if ($this->config->get('show_products_in_category')) $checked = 'checked="checked"'; ?>
			    <input type="checkbox" name="show_products_in_category" value="1" <?php echo $checked; ?> />
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_NOIMAGEPAGE_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_NOIMAGEPAGE') ?>
			</td>
			<td>
			    <?php
			    echo JHTML::_('Select.genericlist', $this->noimagelist, 'no_image', 'size=1');
			    //$images = vmReadDirectory(VM_THEMEPATH.'images', '\.png$|\.bmp$|\.jpg$|\.jpeg$|\.gif$|\.ico$');
			    //foreach( $images as $image ) {
			    //		$imageArr[basename($image)] = $image;
			    //	}
			    //echo ps_html::selectList('conf_NO_IMAGE', NO_IMAGE, $imageArr );
			    ?>
			</td>
		    </tr>
		    <tr>
			<td class="key">
			    <span class="editlinktip hasTip" title="<?php echo JText::_('VM_ADMIN_CFG_SHOWVM_VERSION_EXPLAIN'); ?>"/>
			    <?php echo JText::_('VM_ADMIN_CFG_SHOWVM_VERSION') ?>
			</td>
			<td>
			    <?php
			    $checked = '';
			    if ($this->config->get('show_footer')) $checked = 'checked="checked"'; ?>
			    <input type="checkbox" name="show_footer" value="1" <?php echo $checked; ?> />
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
			    echo JHTML::_('Select.genericlist', $this->themelist, 'theme', 'size=1');
			    $link = JROUTE::_('index.php?option=com_virtuemart&page=admin.theme_config_form&amp;theme='.basename(VM_THEMEURL));
			    $text = JText::_('VM_CONFIG');
			    ?>
			    <a href="<?php echo $link; ?>"><?php echo $text; ?></a>
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
			    echo JHTML::_('Select.genericlist', $this->templatelist, 'category_template', 'size=1');
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
			    echo JHTML::_('Select.genericlist', $this->flypagelist, 'flypage', 'size=1');
			    ?>
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
				<?php
				$checked = '';
				if ($this->config->get('img_resize_enable')) $checked = 'checked="checked"'; ?>
			    <input type="checkbox" name="img_resize_enable" value="1" <?php echo $checked; ?> />
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
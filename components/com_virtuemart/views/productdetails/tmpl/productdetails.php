<?php
/**
* Show the product details page
*
* @package	VirtueMart
* @subpackage 
* @author RolandD
* @todo handle child products
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*/
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/* Let's see if we found the product */
if (!$this->product) {
	echo JText::_('VM_PRODUCT_NOT_FOUND');
}
else { ?>
	<div class="buttons_heading">
		<?php 
		/** @todo Fix links to MVC */
		//$pdf_link = "index2.php?option=$option&amp;page=shop.pdf_output&amp;showpage=$page&amp;pop=1&amp;output=pdf&amp;product_id=$product_id&amp;category_id=$category_id";
		$pdf_link = 'index2.php';
		?>
		<?php echo shopFunctionsF::PdfIcon( $pdf_link ); ?>
		<?php echo shopFunctionsF::PrintIcon(); ?>
		<?php echo shopFunctionsF::EmailIcon($this->product->product_id); ?>
	</div>
	<?php
	if (VmConfig::get('product_navigation', 1)) {
		if (!empty($this->product->neighbours['previous'])) {
			$prev_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$this->product->neighbours['previous']['product_id'].'&flypage='.JRequest::getVar('flypage').'&category_id='.$this->product->category_id);
			echo JHTML::_('link', $prev_link, $this->product->neighbours['previous']['product_name'], array('class' => 'previous_page'));
		}
		if (!empty($this->product->neighbours['next'])) {
			$next_link = JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$this->product->neighbours['next']['product_id'].'&flypage='.JRequest::getVar('flypage').'&category_id='.$this->product->category_id);
			echo JHTML::_('link', $next_link, $this->product->neighbours['next']['product_name'], array('class' => 'next_page'));
		}
	}
	?>
	<br style="clear:both;" />
	<table border="0" align="center" style="width: 100%;" >
		<tr>
			<td rowspan="1" colspan="2" align="center">
				<div style="text-align: center;">
					<h1>
						<?php echo $this->product->product_name.' '.$this->edit_link; ?>
					</h1>
				</div>
				<div style="text-align: center; padding: 0px 0px 10px 0px">
					<?php 
						/** @todo enable below line when Vmstore is available. Rick, go go go */
						// echo "<strong>". JText::_('VM_PRODUCT_DETAILS_VENDOR_LBL'). " </strong>".Vmstore::getVar('vendor_store_name');
						echo "<strong>". JText::_('VM_PRODUCT_DETAILS_VENDOR_LBL'). " </strong>";
					?>
				</div>
			</td>
			<td>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo $this->product->product_s_desc ?>
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr style="width: 100%; height: 2px;" /></td>
		</tr>
		<tr>
			<td align="left" valign="top" width="220">
			<?php /** @todo make the image popup */ ?>
			<div><?php echo ImageHelper::displayShopImage($this->product->product_full_image, 'product'); ?></div>
			</td>
			<td valign="top">
				<?php if (VmConfig::get('use_as_catalogue') != '1') { ?>
					<div style="text-align: center;">
						<?php
							echo $this->loadTemplate('addtocart');
						?>
					</div>
				<?php } ?>
			</td>
		</tr>
		<tr>
			<td rowspan="1" colspan="2">
				<?php 
					$link = JRoute::_('index2.php?view=manufacturer&manufacturer_id='.$this->product->manufacturer_id.'&output=lite&option=com_virtuemart');
					$text = '( '.$this->product->mf_name.' )';
					/* Avoid JavaScript on PDF Output */
					if (strtolower(JRequest::getVar('output')) == "pdf") echo JHTML::_('link', $link, $text);
					else echo shopFunctionsF::vmPopupLink( $link, $text );
				?>
				<br />
			</td>
		</tr>
		<tr>
			<td valign="top" align="left">
				<?php
				/** @todo format price */
				if (VmConfig::get('show_prices') == '1') {
					if( $this->product->product_unit && VmConfig::get('vm_price_show_packaging_pricelabel')) {
						echo "<strong>". JText::_('VM_CART_PRICE_PER_UNIT').' ('.$this->product->product_unit."):</strong>";
					}
					else echo "<strong>". JText::_('VM_CART_PRICE'). ": </strong>";
				
					if ($this->auth["show_price_including_tax"] == 1) echo $this->product->product_price['salesPrice'];
					else echo $this->product->product_price['priceWithoutTax'];
				}
				?><br />
			</td>
		</tr>
		<tr>
			<td valign="top">
				<?php 
				$product_packaging = '';
				if ($this->product->packaging) {
					$product_packaging .= JText::_('VM_PRODUCT_PACKAGING1').$this->product->packaging;
					if ($this->product->box) $product_packaging .= '<br />';
				}
				if ($this->product->box) $product_packaging .= JText::_('VM_PRODUCT_PACKAGING2').$this->product->box;
				
				echo str_replace("{unit}",$this->product->product_unit ? $this->product->product_unit : JText::_('VM_PRODUCT_FORM_UNIT_DEFAULT'), $product_packaging);
				?><br />
			</td>
		</tr>
		<tr>
		<td ><?php
				$url = JRoute::_('index.php?view=productdetails&task=askquestion&flypage='.JRequest::getVar('flypage').'&product_id='.$this->product->product_id.'&category_id='.$this->product->category_id);
				echo JHTML::_('link', $url, JText::_('VM_PRODUCT_ENQUIRY_LBL'), array('class' => 'button'));  
			?></td>
		</tr>
		<tr>
			<td rowspan="1" colspan="2">
				<hr style="width: 100%; height: 2px;" />
				<?php /** @todo Test if content plugins modify the product description */ ?>
				<?php echo $this->product->product_desc; ?>
				<br />
				<span style="font-style: italic;">
					<?php
					foreach ($this->product->files as $fkey => $file) {
						if( $file->filesize > 0.5) $filesize_display = ' ('. number_format($file->filesize, 2,',','.')." MB)";
						else $filesize_display = ' ('. number_format($file->filesize*1024, 2,',','.')." KB)";
						
						/* Show pdf in a new Window, other file types will be offered as download */
						$target = stristr($file->file_mimetype, "pdf") ? "_blank" : "_self";
						$link = JRoute::_('index.php?view=productdetails&task=getfile&file_id='.$file->file_id.'&product_id='.$this->product->product_id);
						echo JHTMl::_('link', $link, $file->file_title.$filesize_display, array('target' => $target));
					}
					?>
				</span>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?php
					if (is_array($this->product->related)) {
						foreach ($this->product->related as $rkey => $related) {
							JRequest::setVar('related', $related);
							echo $this->loadTemplate('relatedproducts');
						}
					}
				?>	
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr style="width: 100%; height: 2px;" />
				<div style="text-align: center;">
				</div>
				<?php
				/* Child categories */
				if ($this->category->haschildren && !empty($this->category->children)) {
					echo $this->loadTemplate('categorychildlist');
				}
				?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?php echo $this->loadTemplate('reviews');?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<div style="text-align: center;">
					<?php 
						$link = JRoute::_('index2.php?option=com_virtuemart&view=virtuemart&task=vendorinfo&vendor_id='.$this->product->vendor_id);
						$text = JText::_('VM_VENDOR_FORM_INFO_LBL');
						echo shopFunctionsF::vmPopupLink( $link, $text ); 
					?><br />
				</div>
				<br />
			</td>
		</tr>
	</table>
<?php } ?>
<?php
/**
 *TODO Improve the CSS , ADD CATCHA ?
 * Show the form Ask a Question
 *
 * @package	VirtueMart
 * @subpackage
 * @author Kohl Patrick
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
* @version $Id: default.php 2810 2011-03-02 19:08:24Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined ( '_JEXEC' ) or die ( 'Restricted access' );
// addon for joomla form validator
JHTML::_('behavior.formvalidation');
/* Let's see if we found the product */
if (empty ( $this->product )) {
	echo JText::_ ( 'VM_PRODUCT_NOT_FOUND' );
	echo '<br /><br />  ' . $this->continue_link_html;
} else { ?>
<div class="productdetails-view" style="margin:20px;">
	<h4><?php echo JText::_('VM_PRODUCT_ASK_QUESTION')  ?></h4>
	<div>
		<div class="width30 floatleft center">

		<?php // Product Image
		/** @todo make the image popup */
		echo $this->productImage->displayImage('class="product-image"',$this->product->product_name,1,0, 'class="modal"');
		?>
		</div>

		<div class="width70 floatleft">
			<h1><?php echo $this->product->product_name ?></h1>

			<?php // Product Short Description
			if (!empty($this->product->product_s_desc)) { ?>
			<p class="short-description">
				<?php
				echo '<span class="bold">'.JText::_('VM_PRODUCT_DETAILS_SHORE_DESC_LBL').'</span><br />';
				echo $this->product->product_s_desc ?>
			</p>
			<?php } // Product Short Description END ?>
			<div class="margintop8">
			<span class="bold"><?php echo JText::_('VM_PRODUCT_DETAILS_MANUFACTURER_LBL')?></span><?php echo $this->product->mf_name; ?>

			</div>
		</div>

	<div class="clear"></div>
	</div>

	<div class="horizontal-separator margintop15 marginbottom15"></div>
<table border="0" align="center" style="width: 100%;">

	<tr>
		<td colspan="2">
		<!-- List of product ASKs -->
				<script language="javascript" type="text/javascript">
				function myValidate(f) {
					if (f.comment.value.length < <?php echo VmConfig::get('vm_asks_minimum_comment_length', 50); ?>) {
						alert('<?php echo JText::sprintf('VM_ASK_ERR_COMMENT1', VmConfig::get('vm_asks_minimum_comment_length', 50)); ?>');
						return false;
					} else 
					if (f.comment.value.length > <?php echo VmConfig::get('vm_asks_maximum_comment_length', 2000); ?>) {
						alert('<?php echo JText::sprintf('VM_ASK_ERR_COMMENT2', VmConfig::get('vm_asks_maximum_comment_length', 2000)); ?>');
						 return false;
					}
					if (document.formvalidator.isValid(f)) {
						f.check.value='<?php echo JUtility::getToken(); ?>'; //send token
						return true; 
					} else {
						var msg = '';
						//Example on how to test specific fields
						if($('email').hasClass('invalid')){
							msg += "\n\n\t<?php echo JText::_('VM_ENTER_A_VALID_EMAIL_ADDRESS')  ?>";
						}
						alert(msg);
					}
					return false;
				}

				function refresh_counter() {
					var form = document.getElementById('askform');
					form.counter.value= form.comment.value.length;
				}
				</script>
					<?php
					if (!empty($this->user->id)) { 
						$user =& JFactory::getUser();
						
					}
					?>
				<form method="post" class="form-validate" action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=productdetails&product_id='.$this->product->product_id.'&category_id='.$this->product->category_id).'&tmpl=component' ; ?>" name="askform" id="askform" onSubmit="return myValidate(this);">
					 <?php echo JText::_('VM_USER_FORM_EMAIL')  ?> : <input type="text" value="<?php echo $this->user->email ?>" name="email" id="email" size="30"  class="required validate-email"/>
					<br />
					<?php
					$ask_comment = JText::sprintf('VM_ASK_COMMENT', VmConfig::get('vm_asks_minimum_comment_length', 50), VmConfig::get('vm_asks_maximum_comment_length', 2000));
					echo $ask_comment;
					?>
					<br />
					<textarea title="<?php echo $ask_comment ?>" class="inputbox" id="comment" onblur="refresh_counter();" onfocus="refresh_counter();" OnKeyUp="refresh_counter();" name="comment" rows="10" cols="55"></textarea>
					<br />
					<input class="button" type="submit" name="submit_ask" title="<?php echo JText::_('VM_ASK_SUBMIT')  ?>" value="<?php echo JText::_('VM_ASK_SUBMIT')  ?>" />
					<div align="right"><?php echo JText::_('VM_REVIEW_COUNT')  ?>
						<input type="text" value="0" size="4" class="inputbox" name="counter" maxlength="4" readonly="readonly" />
					</div>
					<input type="hidden" name="cid[]" value="<?php echo JRequest::getInt('product_id'); ?>" />
					<input type="hidden" name="product_id" value="<?php echo JRequest::getInt('product_id'); ?>" />
					<input type="hidden" name="option" value="<?php echo JRequest::getVar('option'); ?>" />
					<input type="hidden" name="category_id" value="<?php echo JRequest::getInt('category_id'); ?>" />
					<input type="hidden" name="task" value="mailAskquestion" />
					<?php echo JHTML::_( 'form.token' ); ?>
				</form>

		</td>
	</tr>

</table>
</div>
<?php } ?>

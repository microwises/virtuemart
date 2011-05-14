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
JHTML::_ ( 'behavior.formvalidation' );

// Loading Modal Box Effects
JHTML::_ ( 'behavior.modal' );

/* Let's see if we found the product */
if (empty ( $this->product )) {
	echo JText::_ ( 'COM_VIRTUEMART_PRODUCT_NOT_FOUND' );
	echo '<br /><br />  ' . $this->continue_link_html;
} else { ?>

<div class="ask-a-question-view">
	<div class="spacer">
		<h1><?php echo JText::_('COM_VIRTUEMART_PRODUCT_ASK_QUESTION')  ?></h1>
		
		<div class="product-summary">
			<div class="width70 floatleft">
				<h2><?php echo $this->product->product_name ?></h2>

				<?php // Product Short Description
				if (!empty($this->product->product_s_desc)) { ?>
					<div class="short-description">
						<?php echo $this->product->product_s_desc ?>
					</div>
				<?php } // Product Short Description END ?>
			
			</div>
		
			<div class="width30 floatleft center">
				<?php // Product Image
				echo $this->product->images[0]->displayMediaThumb('class="modal product-image"'); ?>
			</div>

		<div class="clear"></div>
		</div>
		
		<?php // Get User
		if (!empty($this->user->id)) {
			$user =& JFactory::getUser();
		} ?>
		
		<div class="form-field">

			<form method="post" class="form-validate" action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id='.$this->product->virtuemart_product_id.'&virtuemart_category_id='.$this->product->virtuemart_category_id).'&tmpl=component' ; ?>" name="askform" id="askform" onSubmit="return myValidate(this);">
				<?php echo JText::_('COM_VIRTUEMART_USER_FORM_EMAIL')  ?> : <input type="text" value="<?php echo $this->user->email ?>" name="email" id="email" size="30"  class="required validate-email"/>
					<br /><br />
					<?php
					$ask_comment = JText::sprintf('COM_VIRTUEMART_ASK_COMMENT', VmConfig::get('vm_asks_minimum_comment_length', 50), VmConfig::get('vm_asks_maximum_comment_length', 2000));
					echo $ask_comment;
					?>
					<br />
					<textarea title="<?php echo $ask_comment ?>" class="field" id="comment" onblur="refresh_counter();" onfocus="refresh_counter();" OnKeyUp="refresh_counter();" name="comment" rows="10"></textarea>
					<br /><br />
					
					<div class="submit">
						<input class="highlight-button" type="submit" name="submit_ask" title="<?php echo JText::_('COM_VIRTUEMART_ASK_SUBMIT')  ?>" value="<?php echo JText::_('COM_VIRTUEMART_ASK_SUBMIT')  ?>" />
						
						<div class="width50 floatright right paddingtop">
							<?php echo JText::_('COM_VIRTUEMART_ASK_COUNT')  ?>
							<input type="text" value="0" size="4" class="counter" name="counter" maxlength="4" readonly="readonly" />
						</div>
					</div>
					
					<input type="hidden" name="cid[]" value="<?php echo JRequest::getInt('virtuemart_product_id'); ?>" />
					<input type="hidden" name="virtuemart_product_id" value="<?php echo JRequest::getInt('virtuemart_product_id'); ?>" />
					<input type="hidden" name="option" value="<?php echo JRequest::getVar('option'); ?>" />
					<input type="hidden" name="virtuemart_category_id" value="<?php echo JRequest::getInt('virtuemart_category_id'); ?>" />
					<input type="hidden" name="task" value="mailAskquestion" />
					<?php echo JHTML::_( 'form.token' ); ?>
				</form>
		
		</div>
	</div>	
</div>

<?php } ?>

<script language="javascript" type="text/javascript">
	function myValidate(f) {
		if (f.comment.value.length < <?php echo VmConfig::get('vm_asks_minimum_comment_length', 50); ?>) {
			alert('<?php echo JText::sprintf('COM_VIRTUEMART_ASK_ERR_COMMENT1', VmConfig::get('vm_asks_minimum_comment_length', 50)); ?>');
			return false;
		} else
		if (f.comment.value.length > <?php echo VmConfig::get('vm_asks_maximum_comment_length', 2000); ?>) {
			alert('<?php echo JText::sprintf('COM_VIRTUEMART_ASK_ERR_COMMENT2', VmConfig::get('vm_asks_maximum_comment_length', 2000)); ?>');
			return false;
		}
		if (document.formvalidator.isValid(f)) {
			f.check.value='<?php echo JUtility::getToken(); ?>'; //send token
			return true;
		} else {
			var msg = '';
			//Example on how to test specific fields
			if($('email').hasClass('invalid')){
				msg += "\n\n\t<?php echo JText::_('COM_VIRTUEMART_ENTER_A_VALID_EMAIL_ADDRESS')  ?>";
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
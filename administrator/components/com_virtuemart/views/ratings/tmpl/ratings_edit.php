<?php
defined('_JEXEC') or die('Restricted access'); 
AdminMenuHelper::startAdminArea(); 
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminform">
	<tr>
		<td>
			<?php echo JText::_('VM_REVIEW_RATE'); ?>
		</td>
		<td>
			<table cellpadding="5">
				<tr>
					<th id="five_stars">
					<label for="user_rating5"><?php echo JHTML::_('image', VM_THEMEURL."images/stars/5.gif", JTEXT::_('5_STARS')); ?></label>
					</th>
					<th id="four_stars">
						<label for="user_rating4"><?php echo JHTML::_('image', VM_THEMEURL."images/stars/4.gif", JTEXT::_('4_STARS')); ?></label>
					</th>
					<th id="three_stars">
						<label for="user_rating3"><?php echo JHTML::_('image', VM_THEMEURL."images/stars/3.gif", JTEXT::_('3_STARS')); ?></label>
					</th>
					<th id="two_stars">
						<label for="user_rating2"><?php echo JHTML::_('image', VM_THEMEURL."images/stars/2.gif", JTEXT::_('2_STARS')); ?></label>
					</th>
					<th id="one_star">
						<label for="user_rating1"><?php echo JHTML::_('image', VM_THEMEURL."images/stars/1.gif", JTEXT::_('1_STARS')); ?></label>
					</th>
					<th id="null_stars">
						<label for="user_rating0"><?php echo JHTML::_('image', VM_THEMEURL."images/stars/0.gif", JTEXT::_('0_STARS')); ?></label>
					</th>
				</tr>
				<tr>
					<td headers="five_stars" style="text-align:center;">
						<input type="radio" id="user_rating5" name="user_rating" value="5" />
					</td>
					<td headers="four_stars" style="text-align:center;">
						<input type="radio" id="user_rating4" name="user_rating" value="4" />
					</td>
					<td headers="three_stars" style="text-align:center;">
						<input type="radio" id="user_rating3" name="user_rating" value="3" />
					</td>
					<td headers="two_stars" style="text-align:center;">
						<input type="radio" id="user_rating2" name="user_rating" value="2" />
					</td>
					<td headers="one_star" style="text-align:center;">
						<input type="radio" id="user_rating1" name="user_rating" value="1" />
					</td>
					<td headers="null_stars" style="text-align:center;">
						<input type="radio" id="user_rating0" name="user_rating" value="0" />
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr> 
		<td width="24%" align="right" valign="top">
			<?php echo JTEXT::_('REVIEW'); ?>
        </td>
		<td width="76%" align="left">
			<textarea onblur="refresh_counter();" onfocus="refresh_counter();" onkeypress="refresh_counter();" rows="20" cols="60" name="comment"><?php echo $this->rating->comment; ?></textarea>
	        <div align="right"><?php echo JText::_('VM_REVIEW_COUNT') ?>
                <input type="text" value="0" size="4" class="inputbox" name="counter" maxlength="4" readonly="readonly" />
            </div>
		</td>
	</tr>
	<tr>
		<td>
			<?php echo JText::_('PUBLISHED'); ?>
		</td>
		<td>
			<?php echo JHTML::_('select.booleanlist', 'published', '', $this->rating->published); ?>
		</td>
	</tr>
</table>
<!-- Hidden Fields -->
<input type="hidden" name="task" value="ratings" />
<input type="hidden" name="option" value="com_virtuemart" />
<input type="hidden" name="pshop_mode" value="admin" />
<input type="hidden" name="view" value="ratings" />
<input type="hidden" name="review_id" value="<?php echo $this->rating->review_id; ?>" />
<input type="hidden" name="product_id" value="<?php echo $this->rating->product_id; ?>" />
<input type="hidden" name="userid" value="<?php echo $this->rating->userid; ?>" />
<input type="hidden" name="time" value="<?php echo $this->rating->time; ?>" />
<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php AdminMenuHelper::endAdminArea(); ?>
<script type="text/javascript">
function refresh_counter() {
    var form = document.adminForm;
    form.counter.value = form.comment.value.length;
}
refresh_counter();
// Preselect the userrating
try {
	document.getElementById('user_rating<?php echo $this->rating->user_rating; ?>').checked = true;
}
catch(e) {}

function submitbutton(pressbutton) {
	
	 if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}
	else {
		if (document.adminForm.counter.value > <?php echo VmConfig::get('comment_max_length'); ?>) alert('<?php echo JText::_('COMMENT_MAX_LENGTH_PASSED'); ?>');
		else if (document.adminForm.counter.value < <?php echo VmConfig::get('comment_min_length'); ?>) alert('<?php echo JText::_('COMMENT_MIN_LENGTH_PASSED'); ?>');
		else submitform( pressbutton );
		return;
	}
}
</script>
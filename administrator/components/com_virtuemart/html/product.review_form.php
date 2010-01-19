<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2005 Benjamin Schirmer. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
mm_showMyFileName( __FILE__ );

$product_id = JRequest::getVar(  'product_id' );
$userid = JRequest::getVar(  'userid' );
$nextpage = JRequest::getVar(  'nextpage', 'product.review_list' );
$review_id = intval( JRequest::getVar(  'review_id' ) );

$funcname = $review_id ? "reviewUpdate" : "addReview";

if ($ps_function->userCanExecuteFunc($funcname)) {
	
if( empty($userid )) {
	$userid = $my->id;
}
$q = "SELECT review_id, product_id, userid, comment, user_rating FROM #__{vm}_product_reviews WHERE review_id=".$review_id;
$db->query($q);

$uid = $db->f('userid') ? $db->f("userid") : $my->id;
if( empty($product_id)) $product_id = $db->f('product_id');

// Create the Form Control Object
$formObj = &new formFactory( JText::_('VM_REVIEW_EDIT') );

// Start the the Form
$formObj->startForm();
// Add necessary hidden fields
$formObj->hiddenField( 'review_id', $review_id );
$formObj->hiddenField( 'product_id', $product_id );
$formObj->hiddenField( 'userid', $uid );
$formObj->hiddenField( 'pshop_mode', 'admin' );

$rating_table = "<table cellpadding=\"5\" summary=\"".JText::_('VM_REVIEW_RATE')."\">
              <tr>
                <th id=\"five_stars\">
                	<label for=\"user_rating5\"><img alt=\"5 stars\" src=\"".VM_THEMEURL."images/stars/5.gif\" border=\"0\" /></label>
                </th>
                <th id=\"four_stars\">
                	<label for=\"user_rating4\"><img alt=\"4 stars\" src=\"".VM_THEMEURL."images/stars/4.gif\" border=\"0\" /></label>
                </th>
                <th id=\"three_stars\">
                	<label for=\"user_rating3\"><img alt=\"3 stars\" src=\"".VM_THEMEURL."images/stars/3.gif\" border=\"0\" /></label>
                </th>
                <th id=\"two_stars\">
                	<label for=\"user_rating2\"><img alt=\"2 stars\" src=\"".VM_THEMEURL."images/stars/2.gif\" border=\"0\" /></label>
                </th>
                <th id=\"one_star\">
                	<label for=\"user_rating1\"><img alt=\"1 star\" src=\"".VM_THEMEURL."images/stars/1.gif\" border=\"0\" /></label>
                </th>
                <th id=\"null_stars\">
                	<label for=\"user_rating0\"><img alt=\"0 stars\" src=\"".VM_THEMEURL."images/stars/0.gif\" border=\"0\" /></label>
                </th>
              </tr>
              <tr>
                <td headers=\"five_stars\" style=\"text-align:center;\">
                  <input type=\"radio\" id=\"user_rating5\" name=\"user_rating\" value=\"5\" />
                </td>
                <td headers=\"four_stars\" style=\"text-align:center;\">
                	<input type=\"radio\" id=\"user_rating4\" name=\"user_rating\" value=\"4\" />
                </td>
                <td headers=\"three_stars\" style=\"text-align:center;\">
                	<input type=\"radio\" id=\"user_rating3\" name=\"user_rating\" value=\"3\" />
                </td>
                <td headers=\"two_stars\" style=\"text-align:center;\">
                	<input type=\"radio\" id=\"user_rating2\" name=\"user_rating\" value=\"2\" />
                </td>
                <td headers=\"one_star\" style=\"text-align:center;\">
                	<input type=\"radio\" id=\"user_rating1\" name=\"user_rating\" value=\"1\" />
                </td>
                <td headers=\"null_stars\" style=\"text-align:center;\">
                	<input type=\"radio\" id=\"user_rating0\" name=\"user_rating\" value=\"0\" />
                </td>
              </tr>
            </table>";
?>
<table class="adminform">
	<tr> 
		<td ><?php echo JText::_('VM_REVIEW_RATE') ?></td>
		<td ><?php echo $rating_table ?></td>
	</tr>
	<tr> 
		<td width="24%" align="right" valign="top">Review:<br/>
        </td>
		<td width="76%" align="left"> 
			<textarea onblur="refresh_counter();" onfocus="refresh_counter();" onkeypress="refresh_counter();" rows="20" cols="60" name="comment"><?php $db->sp("comment") ?></textarea>
	        <div align="right"><?php echo JText::_('VM_REVIEW_COUNT') ?>
                <input type="text" value="0" size="4" class="inputbox" name="counter" maxlength="4" readonly="readonly" />
            </div>
		</td>
	</tr>
</table>
<?php

// Write common hidden input fields
// and close the form
$formObj->finishForm( $funcname, $nextpage);
}
?>
<script type="text/javascript">
function refresh_counter() {
    var form = document.adminForm;
    form.counter.value= form.comment.value.length;
}
refresh_counter();
// Preselect the userrating
try {
	document.getElementById('user_rating<?php echo $db->f('user_rating')?>').checked = true;
}
catch(e) {}
</script>
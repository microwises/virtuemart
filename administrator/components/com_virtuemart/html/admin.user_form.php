<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
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

global $ps_shopper_group, $ps_product, $hVendor;
global $acl, $database;
include_class( 'shopper' );
include_class( 'product' );

if( !isset($ps_shopper_group)) {
    $ps_shopper_group = new ps_shopper_group();
}

$user_id = intval( JRequest::getVar(  'user_id' ) );
$cid		= vmRequest::getVar( 'cid', array(0), '', 'array' );

if( !empty($user_id) ) {
    $q = "SELECT * FROM `#__users` AS `u` LEFT JOIN `#__{vm}_user_info` AS `ui`  ON `u`.`id`=`ui`.`user_id` ";
    $q .= "LEFT JOIN `#__{vm}_auth_user_vendor` AS `au` ON `u`.`id`= `au`.`user_id` ";
    $q .= "WHERE id=$user_id ";
    $q .= "AND (address_type='BT' OR address_type IS NULL ) ";
    $q .= "AND gid <= ".$my->gid;
    $db->query($q);
	$db->next_record();

}

// Set up the CMS General User Information
$row = new mosUser( $database );
$row->load( (int) $user_id );

if ( $user_id ) {
	$query = "SELECT *"
	. "\n FROM #__contact_details"
	. "\n WHERE user_id = " . (int) $row->id
	;
	$database->setQuery( $query );
	$contact = $database->loadObjectList();

	$row->name = trim( $row->name );
	$row->email = trim( $row->email );
	$row->username = trim( $row->username );
	$row->password = trim( $row->password );

} else {
	$contact 	= NULL;
	$row->block = 0;
}

// check to ensure only super admins can edit super admin info
if ( ( $my->gid < 25 ) && ( $row->gid == 25 ) ) {
	vmRedirect( 'index2.php?option=com_users', _NOT_AUTH );
}

$my_group = strtolower( $acl->get_group_name( $row->gid, 'ARO' ) );
if ( $my_group == 'super administrator' && $my->gid != 25 ) {
	$lists['gid'] = '<input type="hidden" name="gid" value="'. $my->gid .'" /><strong>Super Administrator</strong>';
} else if ( $my->gid == 24 && $row->gid == 24 ) {
	$lists['gid'] = '<input type="hidden" name="gid" value="'. $my->gid .'" /><strong>Administrator</strong>';
} else {
	// ensure user can't add group higher than themselves
	$my_groups = $acl->get_object_groups( 'users', $my->id, 'ARO' );
	if (is_array( $my_groups ) && count( $my_groups ) > 0) {
		$ex_groups = $acl->get_group_children( $my_groups[0], 'ARO', 'RECURSE' );
		if (!$ex_groups) $ex_groups = array();
	} else {
		$ex_groups = array();
	}

	$gtree = $acl->get_group_children_tree( null, 'USERS', false );

	// remove users 'above' me
	$i = 0;
	while ($i < count( $gtree )) {
		if (in_array( $gtree[$i]->value, $ex_groups )) {
			array_splice( $gtree, $i, 1 );
		} else {
			$i++;
		}
	}

	$lists['gid'] 		= vmCommonHTML::selectList( $gtree, 'gid', 'size="10"', 'value', 'text', $row->gid, true );
}

// build the html select list
$lists['block'] 		= vmCommonHTML::yesnoRadioList( 'block', 'class="inputbox" size="1"', 'value', 'text', $row->block );
// build the html select list
$lists['sendEmail'] 	= vmCommonHTML::yesnoRadioList( 'sendEmail', 'class="inputbox" size="1"', 'value', 'text', $row->sendEmail );

$canBlockUser 	= $acl->acl_check( 'administration', 'edit', 'users', $my->usertype, 'user properties', 'block_user' );
$canEmailEvents = $acl->acl_check( 'workflow', 'email_events', 'users', $acl->get_group_name( $row->gid, 'ARO' ) );

// Get the user parameters
//if( vmIsJoomla( '1.5' ) ) {
	require_once( JPATH_ADMINISTRATOR.DS.'components'.DS.'com_users'.DS.'users.class.php' );
	$user =& JUser::getInstance( $user_id );
	$params = $user->getParameters(true);
//} elseif( file_exists( $mosConfig_absolute_path . '/administrator/components/com_users/users.class.php' ) ) {
//	require_once( $mosConfig_absolute_path . '/administrator/components/com_users/users.class.php' );
//	$file 	= $mainframe->getPath( 'com_xml', 'com_users' );
//	$params =& new mosUserParameters( $row->params, $file, 'component' );
//}

// Set the last visit date
$lvisit = $row->lastvisitDate;
if ($lvisit == "0000-00-00 00:00:00") {
	$lvisit = '***' . JText::_('VM_USER_FORM_LASTVISIT_NEVER');
}

//First create the object and let it print a form heading
$formObj = &new formFactory( vmCommonHTML::imageTag(VM_ADMIN_ICON_URL.'icon_48/vm_user_48.png', 'User Icon', 'absmiddle' ) 
							.'&nbsp;&nbsp;&nbsp;' 
							. JText::_('VM_USER_FORM_LBL') );
//Then Start the form
$formObj->startForm();

$tabs = new vmTabPanel(0, 1, "userform");
$tabs->startPane("userform-pane");

$tabs->startTab( JText::_('VM_USER_FORM_TAB_GENERALINFO'), "userform-page");

?>
<script language="javascript" type="text/javascript">
function gotocontact( id ) {
	var form = document.adminForm;
	form.target = "_parent";
	form.contact_id.value = id;
	form.option.value = 'com_users';
	submitform( 'contact' );
}
</script>

<fieldset class="adminform">
<legend><?php echo JText::_('VM_USER_FORM_LEGEND_USERDETAILS'); ?></legend>
	<table class="admintable" cellspacing="1">
		<tr>
			<td width="150" class="key">
				<label for="name">
					<?php echo JText::_('VM_USER_FORM_NAME'); ?>
				</label>
			</td>
			<td>
				<input type="text" name="name" id="name" class="inputbox" size="40" value="<?php echo $row->name; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="username">
					<?php echo JText::_('VM_USER_FORM_USERNAME'); ?>
				</label>
			</td>
			<td>
				<input type="text" name="username" id="username" class="inputbox" size="40" value="<?php echo $row->username; ?>" autocomplete="off" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="email">
					<?php echo JText::_('VM_USER_FORM_EMAIL'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="text" name="email" id="email" size="40" value="<?php echo $row->email; ?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="password">
					<?php echo JText::_('VM_USER_FORM_NEWPASSWORD'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="password" name="password" id="password" size="40" value=""/>
			</td>
		</tr>
		<tr>
			<td class="key">
				<label for="password2">
					<?php echo JText::_('VM_USER_FORM_VERIFYPASSWORD'); ?>
				</label>
			</td>
			<td>
				<input class="inputbox" type="password" name="password2" id="password2" size="40" value=""/>
			</td>
		</tr>
		<tr>
			<td valign="top" class="key">
				<label for="gid">
					<?php echo JText::_('VM_USER_FORM_GROUP'); ?>
				</label>
			</td>
			<td>
				<?php echo $lists['gid']; 
				?>
			</td>
		</tr>
		<?php if ( $canBlockUser ) : ?>
		<tr>
			<td class="key">
				<?php echo JText::_('VM_USER_FORM_BLOCKUSER'); ?>
			</td>
			<td>
				<?php echo $lists['block']; 
				?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if ( $canEmailEvents ) : ?>
		<tr>
			<td class="key">
				<?php echo JText::_('VM_USER_FORM_RECEIVESYSTEMEMAILS'); ?>
			</td>
			<td>
				<?php echo $lists['sendEmail']; 
				?>
			</td>
		</tr>
		<?php endif; ?>
		<?php if( $user_id ) : ?>
		<tr>
			<td class="key">
				<?php echo JText::_('VM_USER_FORM_REGISTERDATE'); ?>
			</td>
			<td>
				<?php echo $row->registerDate;?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('VM_USER_FORM_LASTVISITDATE'); ?>
			</td>
			<td>
				<?php echo $lvisit; ?>
			</td>
		</tr>
		<?php endif; ?>
	</table>
</fieldset>
<fieldset class="adminform">
<legend><?php echo JText::_('VM_USER_FORM_LEGEND_PARAMETERS'); ?></legend>
	<table class="admintable" cellspacing="1">
		<tr>
			<td>
			<?php if( is_callable(array($params, 'render'))) echo $params->render( 'params' );?>
			</td>
		</tr>
	</table>
</fieldset>
<fieldset class="adminform">
<legend><?php echo JText::_('VM_USER_FORM_LEGEND_CONTACTINFO'); ?></legend>
	<?php if ( !$contact ) : ?>
	<table class="admintable" cellspacing="1">
		<tr>
			<td>
			<br />
			<?php echo JText::_('VM_USER_FORM_NOCONTACTDETAILS_1'); ?>
			<br />
			<?php echo JText::_('VM_USER_FORM_NOCONTACTDETAILS_2'); ?>
			<br /><br />
			</td>
		</tr>
	</table>
	<?php else : ?>
	<table class="admintable" cellspacing="1">
		<tr>
			<td width="15%">
			<?php echo JText::_('VM_USER_FORM_CONTACTDETAILS_NAME'); ?>:
			</td>
			<td>
			<strong>
			<?php echo $contact[0]->name;?>
			</strong>
			</td>
		</tr>
		<tr>
			<td>
			<?php echo JText::_('VM_USER_FORM_CONTACTDETAILS_POSITION'); ?>:
			</td>
			<td >
			<strong>
			<?php echo $contact[0]->con_position;?>
			</strong>
			</td>
		</tr>
		<tr>
			<td>
			<?php echo JText::_('VM_USER_FORM_CONTACTDETAILS_TELEPHONE'); ?>:
			</td>
			<td >
			<strong>
			<?php echo $contact[0]->telephone;?>
			</strong>
			</td>
		</tr>
		<tr>
			<td>
			<?php echo JText::_('VM_USER_FORM_CONTACTDETAILS_FAX'); ?>:
			</td>
			<td >
			<strong>
			<?php echo $contact[0]->fax;?>
			</strong>
			</td>
		</tr>
		<tr>
			<td></td>
			<td >
			<strong>
			<?php echo $contact[0]->misc;?>
			</strong>
			</td>
		</tr>
		<?php if ($contact[0]->image) : ?>
		<tr>
			<td></td>
			<td valign="top">
			<img src="<?php echo $mosConfig_live_site;?>/images/stories/<?php echo $contact[0]->image; ?>" align="middle" alt="Contact" />
			</td>
		</tr>
		<?php endif; ?>
		<tr>
			<td colspan="2">
			<br />
			<input class="button" type="button" value="<?php echo JText::_('VM_USER_FORM_CONTACTDETAILS_CHANGEBUTTON'); ?>" onclick="javascript: gotocontact( '<?php echo $contact[0]->id; ?>' )">
			</td>
		</tr>
	</table>
	<?php endif; ?>

<input type="hidden" name="id" value="<?php echo $user_id; ?>" />
<input type="hidden" name="cid[]" value="<?php echo $user_id; ?>" />
<input type="hidden" name="contact_id" value="" />
<?php if ( !$canEmailEvents ) : ?>
<input type="hidden" name="sendEmail" value="0" />
<?php endif; ?>


<?php

$tabs->endTab();
$tabs->startTab( JText::_('VM_SHOPPER_FORM_LBL'), "third-page");

?>

<fieldset style="width:48%;"><legend><?php echo JText::_('VM_SHOPPER_FORM_LBL') ?></legend>
<table class="adminform">  
    <tr> 
        <td style="text-align:right;"><?php echo JText::_('VM_PRODUCT_FORM_VENDOR') ?>:</td>
        <td><?php $hVendor -> list_vendor($db->f("vendor_id"),true);  ?></td>
    </tr>
	<tr> 
        <td nowrap="nowrap" style="text-align:right;" width="38%" ><?php echo JText::_('VM_USER_FORM_PERMS') ?>:</td> 
        <td width="62%" > 
                <?php
                if( !isset( $ps_perms)) { $ps_perms = new ps_perm(); }
                $ps_perms->list_perms("perms", $db->sf("perms"));
                ?> 
        </td> 
    </tr> 
      <tr> 
    	<td style="text-align:right;"><?php echo JText::_('VM_USER_FORM_CUSTOMER_NUMBER') ?>:</td>
        <td > 
      	<input type="text" class="inputbox" name="customer_number" size="40" value="<?php echo $ps_shopper_group->get_customer_num($db->f("user_id")) ?>" />
        </td>
     </tr>
     <tr> 
    	<td style="text-align:right;"> <?php echo JText::_('VM_SHOPPER_FORM_GROUP') ?>:</td>
        <td ><?php
            include_class('shopper');
            $sg_id = $ps_shopper_group->get_shoppergroup_by_id($db->f("user_id"));
            echo ps_shopper_group::list_shopper_groups("shopper_group_id",$sg_id["shopper_group_id"]);?>
        </td>
    </tr>
</table> 
</fieldset>
       
<?php 
if( $db->f("user_id") ) {
?> 
    <fieldset><legend><?php echo JText::_('VM_USER_FORM_SHIPTO_LBL') ?></legend>
    
    <a class="vmicon vmicon-16-editadd" href="<?php $sess->purl($_SERVER['PHP_SELF'] . "?page=$modulename.user_address_form&amp;user_id=$user_id") ?>" >
	(<?php echo JText::_('VM_USER_FORM_ADD_SHIPTO_LBL') ?>)</a> 
	
	<table class="adminlist"> 
		<tr> 
			<td > 
				  <?php
			$qt = "SELECT * from #__{vm}_user_info WHERE user_id='$user_id' AND address_type='ST'"; 
			$dbt = new ps_DB;
			$dbt->query($qt);
			if (!$dbt->num_rows()) {
			  echo "No shipping addresses.";
			}
			else {
			  while ($dbt->next_record()) {
				$url = $sess->url( $_SERVER['PHP_SELF'] . "?page=$modulename.user_address_form&user_id=$user_id&user_info_id=" . $dbt->f("user_info_id"));
				echo '&raquo; <a href="' . $sess->url($url) . '">';
				echo $dbt->f("address_type_name") . "</a><br/>";
			  }
			} ?> 
			</td> 
		</tr> 
	</table>
	</fieldset>
         <?php 
}

require_once( CLASSPATH.'ps_userfield.php');
// Get only those fields that are NOT system fields
$userFields = ps_userfield::getUserFields( 'account' );
$skipFields = array( 'delimiter_userinfo', 'username', 'email', 'password', 'password2', 'agreed' );

echo '<table class="adminform"><tr><td>';
ps_userfield::listUserFields( $userFields, $skipFields, $db, false );
echo '</td></tr></table>';

$tabs->endTab();

require_once( CLASSPATH . 'pageNavigation.class.php' );
require_once( CLASSPATH . 'htmlTools.class.php' );
require_once(CLASSPATH.'ps_order_status.php');
$ps_order_status = new ps_order_status;

$q = "";
$list  = "SELECT * FROM #__{vm}_orders ";
$count = "SELECT count(*) as num_rows FROM #__{vm}_orders ";
//Hmm which vendor is needed here? The orders that a user have in a shop? The vendor to which the user is assigned?
//or the vendor_id of the user if he is vendor himself? A lot of possibilities,.. sorry cant 
//write it because need to find the senxe for orders in the vendor/userform first.
//I set now the vendor to 1,.. because the ps_vendor_id was everytime 1 (in 1.1.3)  Max Milbers
$vendor_id = 1;
$q .= "WHERE  #__{vm}_orders.vendor_id='".$vendor_id."' AND #__{vm}_orders.user_id=".$user_id." ";
$q .= "ORDER BY #__{vm}_orders.cdate DESC ";
$count .= $q;
$list .= $q;

$db->query($count);
$db->next_record();

require_once(CLASSPATH.'ps_order.php');
$db = ps_order::list_order_resultSet($order_status);
$num_rows = $db->f("num_rows");
if( $num_rows ) {
	$tabs->startTab( JText::_('VM_ORDER_LIST_LBL') . ' ('.$num_rows.')', "order-list");
	?>
	        
	<h3><?php echo JText::_('VM_ORDER_LIST_LBL') ?> </h3>
	
	<?php
	
	// Create the Page Navigation
	$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );
	
	// Create the List Object with page navigation
	$listObj = new listFactory( $pageNav );
	
	$listObj->startTable();
	
	// these are the columns in the table
	$columns = Array(  "#" => "width=\"20\"", 
	                                        JText::_('VM_ORDER_LIST_ID') => '',
	                                        JText::_('VM_CHECK_OUT_THANK_YOU_PRINT_VIEW') => '',
	                                        JText::_('VM_ORDER_LIST_CDATE') => '',
	                                        JText::_('VM_ORDER_LIST_MDATE') => '',
	                                        JText::_('VM_ORDER_LIST_STATUS') => '',
	                                        JText::_('VM_ORDER_LIST_TOTAL') => '',
	                                        JText::_('E_REMOVE') => "width=\"5%\""
	                                );
	$listObj->writeTableHeader( $columns );
	
	$db->query($list);
	$i = 0;
	while ($db->next_record()) { 
	    
	    $listObj->newRow();
	    
	    // The row number
	    $listObj->addCell( $pageNav->rowNumber( $i ) );
	    
	    $url = $_SERVER['PHP_SELF']."?page=order.order_print&limitstart=$limitstart&keyword=".urlencode($keyword)."&order_id=". $db->f("order_id");
	    $tmp_cell = "<a href=\"" . $sess->url($url) . "\">".sprintf("%08d", $db->f("order_id"))."</a><br />";
	    $listObj->addCell( $tmp_cell );
	    
	    $details_url = $sess->url( $_SERVER['PHP_SELF']."?page=order.order_printdetails&amp;order_id=".$db->f("order_id")."&amp;no_menu=1");
	    $details_url = stristr( $_SERVER['PHP_SELF'], "index2.php" ) ? str_replace( "index2.php", "index3.php", $details_url ) : str_replace( "index.php", "index2.php", $details_url );
	        
	    $details_link = "&nbsp;<a href=\"javascript:void window.open('$details_url', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');\">";
	    $details_link .= "<img src=\"$mosConfig_live_site/images/M_images/printButton.png\" align=\"center\" height=\"16\" width=\"16\" border=\"0\" /></a>"; 
	    $listObj->addCell( $details_link );
	
	    $listObj->addCell( strftime("%d-%b-%y %H:%M", $db->f("cdate")));
	    $listObj->addCell( strftime("%d-%b-%y %H:%M", $db->f("mdate")));
	    
	    $listObj->addCell(  $ps_order_status->getOrderStatusName($db->f("order_status")));
	    
	    $listObj->addCell( $CURRENCY_DISPLAY->getFullValue($db->f("order_total")));
	
	    $listObj->addCell( $ps_html->deleteButton( "order_id", $db->f("order_id"), "orderDelete", $keyword, $limitstart. '&user_id='.$user_id ) );
	
	    $i++; 
	}
	
	$listObj->writeTable();
	
	$listObj->endTable();
	
	$tabs->endTab();
}
//$tabs->endPane();


/******************************
 * 
 */
//When the user is a vendor than add the tabs with the specific vendor information
//global $hVendor;
//if(!empty($vendor_id)){
if($hVendor->isVendor($user_id)){
	
$vendor_id = $hVendor->getVendorIdByUserId($user_id);

	$db = ps_vendor::get_vendor_details($vendor_id);
	/* Build up the Tabs */
//$tabs = new vmTabPanel(0, 1, "vendorform");
//$tabs->startPane("content-pane");
echo('Start VM_STORE_MOD $vendor_id: '.$vendor_id);
$tabs->startTab( JText::_('VM_STORE_MOD'), "info-page");
?>

<table class="adminform">
    <tr> 
      <td><strong><?php echo JText::_('VM_VENDOR_FORM_INFO_LBL') ?></strong></td>
      <td>&nbsp;</td>
    </tr>
    <tr> 
      <td align="right" ><?php echo JText::_('VM_VENDOR_FORM_FULL_IMAGE') ?>:</td>
      <td><?php  
        $hVendor -> show_image($db->f("vendor_full_image"), $vendor_id); ?> 
        <input type="hidden" name="vendor_full_image_curr" value="<?php $db->p("vendor_full_image"); ?>" />
      </td>
    </tr>
    <tr> 
      <td align="right" ><?php echo JText::_('VM_VENDOR_FORM_UPLOAD') ?>:</td>
      <td> 
        <input type="file" name="vendor_full_image" size="16" />
      </td>
    </tr>
    <tr> 
      <td align="right" ><?php echo JText::_('VM_VENDOR_FORM_STORE_NAME') ?>:</td>
      <td> 
        <input type="text" class="inputbox" name="vendor_store_name" value="<?php $db->sp("vendor_store_name") ?>" size="16" />
      </td>
    </tr>
    <tr> 
      <td align="right"><?php echo JText::_('VM_VENDOR_FORM_COMPANY_NAME') ?></td>
      <td> 
        <input type="text" class="inputbox" name="vendor_name" value="<?php $db->sp("vendor_name") ?>" size="16" />
      </td>
    </tr>
    <tr> 
      <td width="22%" align="right" ><?php echo JText::_('VM_PRODUCT_FORM_URL') ?>:</td>
      <td width="78%" > 
        <input type="text" class="inputbox" name="vendor_url" value="<?php $db->sp("vendor_url") ?>" size="32" />
      </td>
    </tr>
    <tr> 
      <td width="22%" align="right" ><?php echo JText::_('VM_STORE_FORM_MPOV') ?>: </td>
      <td width="78%" > 
        <input type="text" class="inputbox" name="vendor_min_pov" value="<?php $db->sp("vendor_min_pov") ?>" size="6" />
      </td>
    </tr>
    <tr> 
      <td width="22%" align="right" ><?php echo JText::_('VM_FREE_SHIPPING_AMOUNT') ?>: </td>
      <td width="78%" > 
        <input type="text" class="inputbox" name="vendor_freeshipping" value="<?php $db->sp("vendor_freeshipping") ?>" size="6" />
      <?php echo vmToolTip( JText::_('VM_FREE_SHIPPING_AMOUNT_TOOLTIP') ) ?>
      </td>
    </tr>
    <tr> 
      <td align="right"><?php echo JText::_('VM_VENDOR_FORM_CATEGORY') ?>:</td>
      <td ><?php 
      	global $hVendor_category;
          $hVendor_category->list_category($db->sf("vendor_category_id"));
          ?>
      </td>
    </tr>
    <tr>
      <td colspan="2" style="font-weight:bold;"><hr /><?php echo JText::_('VM_CURRENCY_DISPLAY') ?></td>
    </tr>
    
<?php
/* Decode vendor_currency_display_style */
$currency_display =& $hVendor -> get_currency_display_style( $db->f("vendor_currency_display_style") );
?>
    <tr>        
      <td align="right"><?php echo JText::_('VM_VENDOR_FORM_CURRENCY') ?>:</td>
      <td> 
        <?php $ps_html->list_currency("vendor_currency", $db->sf("vendor_currency")); ?>
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo JText::_('VM_CURRENCY_SYMBOL') ?> : </td>
      <td>
        <input type="hidden" name="display_style[0]" value="<?php echo $vendor_id; ?>" size="4">
        <input type="text" name="display_style[1]" value="<?php echo $currency_display['symbol']; ?>" size="4" />
        <?php echo vmToolTip( JText::_('VM_CURRENCY_SYMBOL_TOOLTIP') )?>
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo JText::_('VM_CURRENCY_DECIMALS') ?> : </td>
      <td><input type="text" name="display_style[2]" value="<?php echo $currency_display['nbdecimal']; ?>" size="1" />
      <?php echo vmToolTip( JText::_('VM_CURRENCY_DECIMALS_TOOLTIP') ) ?>
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo JText::_('VM_CURRENCY_DECIMALSYMBOL') ?> : </td>
      <td><input type="text" name="display_style[3]" value="<?php echo $currency_display['sdecimal']; ?>" size="1" />
      <?php echo vmToolTip( JText::_('VM_CURRENCY_DECIMALSYMBOL_TOOLTIP') ) ?></td>
    </tr>
    <tr>
      <td align="right"><?php echo JText::_('VM_CURRENCY_THOUSANDS') ?> : </td>
      <td><input type="text" name="display_style[4]" value="<?php echo $currency_display['thousands']; ?>" size="1" />
      <?php echo vmToolTip( JText::_('VM_CURRENCY_THOUSANDS_TOOLTIP') )?></td>
    </tr>
    <tr>
      <td align="right"><?php echo JText::_('VM_CURRENCY_POSITIVE_DISPLAY') ?> : </td>
      <td>
        <select name="display_style[5]">
			<option value="0"<?php if ($currency_display['positive']=='0') echo ' selected=\"selected\" ';?>>00Symb</option>
	   		<option value="1"<?php if ($currency_display['positive']=='1') echo ' selected=\"selected\" ';?>>00 Symb</option>
	   		<option value="2"<?php if ($currency_display['positive']=='2') echo ' selected=\"selected\" ';?>>Symb00</option>
		   	<option value="3"<?php if ($currency_display['positive']=='3') echo ' selected=\"selected\" ';?>>Symb 00</option>
        </select>
        <?php echo vmToolTip( JText::_('VM_CURRENCY_POSITIVE_DISPLAY_TOOLTIP') ) ?>
      </td>
    </tr>
    <tr>
      <td align="right"><?php echo JText::_('VM_CURRENCY_NEGATIVE_DISPLAY') ?> : </td>
      <td>
        <select name="display_style[6]">
			<option value="0"<?php if ($currency_display['negative']=='0') echo ' selected=\"selected\" ';?>>(Symb00)</option>
		   	<option value="1"<?php if ($currency_display['negative']=='1') echo ' selected=\"selected\" ';?>>-Symb00</option>
		   	<option value="2"<?php if ($currency_display['negative']=='2') echo ' selected=\"selected\" ';?>>Symb-00</option>
		   	<option value="3"<?php if ($currency_display['negative']=='3') echo ' selected=\"selected\" ';?>>Symb00-</option>
		   	<option value="4"<?php if ($currency_display['negative']=='4') echo ' selected=\"selected\" ';?>>(00Symb)</option>
		   	<option value="5"<?php if ($currency_display['negative']=='5') echo ' selected=\"selected\" ';?>>-00Symb</option>
		   	<option value="6"<?php if ($currency_display['negative']=='6') echo ' selected=\"selected\" ';?>>00-Symb</option>
		   	<option value="7"<?php if ($currency_display['negative']=='7') echo ' selected=\"selected\" ';?>>00Symb-</option>
		   	<option value="8"<?php if ($currency_display['negative']=='8') echo ' selected=\"selected\" ';?>>-00 Symb</option>
		   	<option value="9"<?php if ($currency_display['negative']=='9') echo ' selected=\"selected\" ';?>>-Symb 00</option>
		   	<option value="10"<?php if ($currency_display['negative']=='10') echo ' selected=\"selected\" ';?>>00 Symb-</option>
		   	<option value="11"<?php if ($currency_display['negative']=='11') echo ' selected=\"selected\" ';?>>Symb 00-</option>
		   	<option value="12"<?php if ($currency_display['negative']=='12') echo ' selected=\"selected\" ';?>>Symb -00</option>
		   	<option value="13"<?php if ($currency_display['negative']=='13') echo ' selected=\"selected\" ';?>>00- Symb</option>
		   	<option value="14"<?php if ($currency_display['negative']=='14') echo ' selected=\"selected\" ';?>>(Symb 00)</option>
		   	<option value="15"<?php if ($currency_display['negative']=='15') echo ' selected=\"selected\" ';?>>(00 Symb)</option>
        </select>
        <?php echo vmToolTip( JText::_('VM_CURRENCY_NEGATIVE_DISPLAY_TOOLTIP') ) ?>
      </td>
    </tr>
  </table>
  
   <table class="adminform">
    <tr> 
      <td width="22%" align="right"  valign="top"><?php echo JText::_('VM_VENDOR_FORM_DESCRIPTION') ?>:</td>
      <td width="78%" ><?php
	  	 editorArea( 'editor1', $db->f("vendor_store_desc"), 'vendor_store_desc', '400', '200', '70', '15' );
			?>
      </td>
    </tr>
            <tr> 
      <td width="22%" align="right"  valign="top"><?php echo JText::_('VM_STORE_FORM_TOS') ?>:</td>
      <td width="78%" ><?php
	  	  editorArea( 'editor2', $db->f("vendor_terms_of_service"), 'vendor_terms_of_service', '400', '200', '70', '15' );
		?>
      </td>
    </tr>
    <tr align="center"> 
      <td colspan="2" >&nbsp;</td>
    </tr> 
  </table>
  
<?php
  $tabs->endTab();
}

$tabs->endPane();

// Add necessary hidden fields
$formObj->hiddenField( 'address_type', 'BT' );
$formObj->hiddenField( 'address_type_name', '-default-' );
$formObj->hiddenField( 'user_id', $user_id );

//this is not really necessary anymore
$funcname = $user_id ? "userUpdate" : "userAdd";

// Write your form with mixed tags and text fields
// and finally close the form:
$formObj->finishForm( $funcname, 'admin.user_list', $option );

?>
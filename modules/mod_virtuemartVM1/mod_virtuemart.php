<?php
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct access to '.basename(__FILE__).' is not allowed.' );
/**
* mambo-phphop Main Module
* NOTE: THIS MODULE REQUIRES AN INSTALLED MAMBO-PHPSHOP COMPONENT!
*
* @version $Id$
* @package VirtueMart
* @subpackage modules
* 
* @copyright (C) 2004-2008 soeren - All Rights reserved
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*
* VirtueMart is Free Software.
* VirtueMart comes with absolute no warranty.
*
* www.virtuemart.net
*/

/* Load the virtuemart main parse code */
if( !isset( $mosConfig_absolute_path ) ) {
	$mosConfig_absolute_path = $GLOBALS['mosConfig_absolute_path']	= JPATH_SITE;
}
global $mosConfig_absolute_path, $page;
require_once( $mosConfig_absolute_path.'/components/com_virtuemart/virtuemart_parser.php' );

require_once(CLASSPATH.'ps_product_category.php');
$ps_product_category =& new ps_product_category();

global $my, $root_label, $mosConfig_allowUserRegistration, $jscook_type, $jscookMenu_style, $jscookTree_style, $VM_LANG, $sess, $mm_action_url;

$category_id = vmRequest::getInt( 'category_id' );

$mod_dir = dirname( __FILE__ );

/* Get module parameters */
$show_login_form = $params->get( 'show_login_form', 'no' );
$show_categories = $params->get( 'show_categories', 'yes' );
$show_listall = $params->get( 'show_listall', 'yes' );
$show_adminlink = $params->get('show_adminlink', 'yes' );
$show_accountlink = $params->get('show_accountlink', 'yes' );
$useGreyBox_accountlink = $params->get('useGreyBox_accountlink', '0' );
$show_minicart = $params->get( 'show_minicart', 'yes' );
$useGreyBox_cartlink = $params->get( 'useGreyBox_cartlink', '0' );
$show_productsearch = $params->get( 'show_productsearch', 'yes' );
$show_product_parameter_search = $params->get( 'show_product_parameter_search', 'no' );
$menutype = $params->get( 'menutype', "links" );
$class_sfx = $params->get( 'class_sfx', '' );
$pretext = $params->get( 'pretext', '' );
$jscookMenu_style = $params->get( 'jscookMenu_style', 'ThemeOffice' );
$jscookTree_style = $params->get( 'jscookTree_style', 'ThemeXP' );
$jscook_type = $params->get( 'jscook_type', 'menu' );
$menu_orientation = $params->get( 'menu_orientation', 'hbr' );
$_REQUEST['root_label'] = $params->get( 'root_label', 'Shop' );

$class_mainlevel = "mainlevel".$class_sfx;
$db = new ps_DB();
// This is "Categories:" by default. Change it in the Module Parameters Form
echo $pretext;

// update the cart because something could have 
// changed while running a function
$cart = $_SESSION["cart"];
$auth = $_SESSION["auth"];

if( $show_categories == "yes" ) {
  
  
  if ( $menutype == 'links' ) {
	/* MENUTPYE LINK LIST */
    echo $ps_product_category->get_category_tree( $category_id, $class_mainlevel );

  } 
  elseif( $menutype == "transmenu" ) {
      /* TransMenu script to display a DHTML Drop-Down Menu */
      include_once( $mod_dir.'/vm_transmenu.php' );
    
  }
  elseif( $menutype == "dtree" ) {
      /* dTree script to display structured categories */
      include_once( $mod_dir.'/vm_dtree.php' );
    
  }
  elseif( $menutype == "jscook" ) {
      /* JSCook Script to display structured categories */
      include_once( $mod_dir.'/vm_JSCook.php' );
    
  }
  elseif( $menutype == "tigratree" ) {
      /* TigraTree script to display structured categories */
      include_once( $mod_dir . '/vm_tigratree.php' );
  }

}
?>
<table cellpadding="1" cellspacing="1" border="0" width="100%">
<?php
// "List all Products" Link
if ( $show_listall == 'yes' ) { ?>
    <tr> 
      <td colspan="2"><br />
          <a href="<?php $sess->purl($mm_action_url."index.php?page=shop.browse&category=") ?>">
          <?php echo $VM_LANG->_('PHPSHOP_LIST_ALL_PRODUCTS') ?>
          </a>
      </td>
    </tr>
  <?php
}

// Product Search Box
if ( $show_productsearch == 'yes' ) { ?>
  
  <!--BEGIN Search Box --> 
  <tr> 
    <td colspan="2">
	  <hr />
      <label for="shop_search_field"><?php echo $VM_LANG->_('PHPSHOP_PRODUCT_SEARCH_LBL') ?></label>
      <form action="<?php echo $mm_action_url."index.php" ?>" method="get">
        <input id="shop_search_field" title="<?php echo $VM_LANG->_('PHPSHOP_SEARCH_TITLE') ?>" class="inputbox" type="text" size="12" name="keyword" />
        <input class="button" type="submit" name="Search" value="<?php echo $VM_LANG->_('PHPSHOP_SEARCH_TITLE') ?>" />
		<input type="hidden" name="Itemid" value="<?php echo intval(@$_REQUEST['Itemid']) ?>" />
		<input type="hidden" name="option" value="com_virtuemart" />
		<input type="hidden" name="page" value="shop.browse" />
	  </form>
        <br />
        <a href="<?php echo $sess->url($mm_action_url."index.php?option=com_virtuemart&page=shop.search") ?>">
            <?php echo $VM_LANG->_('PHPSHOP_ADVANCED_SEARCH') ?>
        </a><?php /** Changed Product Type - Begin */
	if ( $show_product_parameter_search == 'yes' ) { ?>
        <br />
        <a href="<?php echo $sess->url($mm_action_url."index.php?option=com_virtuemart&page=shop.parameter_search") ?>" title="<?php echo $VM_LANG->_('PHPSHOP_PARAMETER_SEARCH') ?>">
            <?php echo $VM_LANG->_('PHPSHOP_PARAMETER_SEARCH') ?>
        </a>
<?php } /** Changed Product Type - End */ ?>
        <hr />
    </td>
  </tr>
  <!-- End Search Box --> 
<?php 
}
  
$perm = new ps_perm;
// Show the Frontend ADMINISTRATION Link
if ($perm->check("admin,storeadmin") 
      && ((!stristr($my->usertype, "admin") ^ PSHOP_ALLOW_FRONTENDADMIN_FOR_NOBACKENDERS == '' ) 
          || stristr($my->usertype, "admin")
      )
      && $show_adminlink == 'yes'
    ) { ?>
    <tr> 
      <td colspan="2">
      	<a class="<?php echo $class_mainlevel ?>" href="<?php $sess->purl(SECUREURL . "index2.php?page=store.index&pshop_mode=admin") ?>">
      	<?php echo $VM_LANG->_('PHPSHOP_ADMIN_MOD'); ?>
      	</a>
      </td>
    </tr>
  <?php 
}

// Show the Account Maintenance Link
if ($perm->is_registered_customer($auth["user_id"]) && $show_accountlink == 'yes') {
  ?> 
    <tr> 
      <td colspan="2"><a class="<?php echo $class_mainlevel ?>" href="<?php $sess->purl(SECUREURL . "index.php?page=account.index");?>">
      <?php echo $VM_LANG->_('PHPSHOP_ACCOUNT_TITLE') ?></a></td>
    </tr><?php 
}

// SHOW LOGOUT FORM IF USER'S LOGGED IN
if ( $show_login_form == "yes" ) {
	
    if ($my->id) {
		if( vmIsJoomla('1.5') ) {
			// Logout URL
			$action =  $mm_action_url . 'index.php?option=com_user&amp;task=logout';

			// Logout return URL
			$uri = JFactory::getURI();
			$url = $uri->toString();
			$return = base64_encode( $url );
		} else {
			// Logout URL
			$action = $mm_action_url . 'index.php?option=logout';

			// Logout return URL
			$return = $mm_action_url . 'index.php';
		}
?>	  
	<tr>
	  <td colspan="2" valign="top">
		<div align="left" style="margin: 0px; padding: 0px;">
		  <form action="<?php echo $action ?>" method="post" name="login" id="login">
			<input type="submit" name="Submit" class="button" value="<?php echo $VM_LANG->_('BUTTON_LOGOUT') ?>" /><br /><hr />
			<input type="hidden" name="op2" value="logout" />
			<input type="hidden" name="return" value="<?php echo $return ?>" />
			<input type="hidden" name="lang" value="english" />
			<input type="hidden" name="message" value="0" />
		  </form>
		</div>
	  </td>
	</tr>
<?php 
	}
	else
	{
		if( vmIsJoomla('1.5') ) {
			// Login URL
			$action =  $mm_action_url . 'index.php?option=com_user&amp;task=login';
			
			// Login return URL
			$uri = JFactory::getURI();
			$url = $uri->toString();
			$return = base64_encode( $url );
			
			// Lost password
			$reset = JRoute::_( 'index.php?option=com_user&view=reset' );

			// User name reminder (Joomla 1.5 only)
			$remind_url = JRoute::_( 'index.php?option=com_user&view=remind' );
		} else {
			// Login URL
			$action = $mm_action_url . 'index.php?option=login';

			// Login return URL
			$return = $sess->url( $mm_action_url . 'index.php?'. $_SERVER['QUERY_STRING'] );
			
			// Lost password url
			$reset = sefRelToAbs( 'index.php?option=com_registration&amp;task=lostPassword&amp;Itemid='.(int)vmGet($_REQUEST, 'Itemid', 0) );

			// Set user name reminder to nothing
			$remind_url = '';
		}
		?> 	  
		<tr>
		  <td colspan="2" align="left" valign="top" style="margin: 0px; padding: 0px;">
			<form action="<?php echo $action ?>" method="post" name="login" id="login">
			<label for="username_field"><?php echo $VM_LANG->_('USERNAME') ?></label><br/>
			<input class="inputbox" type="text" id="username_field" size="12" name="username" />
		  <br/>
			<label for="password_field"><?php echo $VM_LANG->_('PASSWORD') ?></label><br/>
			<input type="password" class="inputbox" id="password_field" size="12" name="passwd" />
			<?php if( @VM_SHOW_REMEMBER_ME_BOX == '1' ) : ?>
			<br />
			<label for="remember_login"><?php echo $VM_LANG->_('REMEMBER_ME') ?></label>
			<input type="checkbox" name="remember" id="remember_login" value="yes" checked="checked" />
			<?php else : ?>
			<br />
			<input type="hidden" name="remember" value="yes" />
			<?php endif; ?>
			<input type="hidden" value="login" name="op2" />
			<input type="hidden" value="<?php echo $return ?>" name="return" />
		  	<br/>
			<input type="submit" value="<?php echo $VM_LANG->_('BUTTON_LOGIN') ?>" class="button" name="Login" />
			<?php
			if( vmIsJoomla(1.5) ) {
				$validate = JUtility::getToken();
			}
			elseif( function_exists('josspoofvalue')) {
				$validate = josSpoofValue(1);
			} else {
			  	// used for spoof hardening
				$validate = vmSpoofValue(1);
			}
			?>
			<input type="hidden" name="<?php echo $validate; ?>" value="1" />
			</form>
		  </td>
		</tr>
		<tr>
		  <td colspan="2">
			<a href="<?php echo $reset ?>">
			<?php echo $VM_LANG->_('LOST_PASSWORD'); ?>
			</a>
		  </td>
		</tr>
		<?php if( $remind_url ) : ?>
		<tr>
		  <td colspan="2">
			<a href="<?php echo $remind_url ?>"><?php echo $VM_LANG->_('FORGOT_YOUR_USERNAME') ?></a>
		  </td>
		</tr>
		<?php endif; ?>
		<?php if( $mosConfig_allowUserRegistration == '1' ) : ?>
			<tr>
			  <td colspan="2">
				<?php echo $VM_LANG->_('NO_ACCOUNT'); ?>
				<a href="<?php $sess->purl( SECUREURL.'index.php?option=com_virtuemart&amp;page=shop.registration' ); ?>">
				<?php echo $VM_LANG->_('CREATE_ACCOUNT'); ?>
				</a>
			  </td>
			</tr>
			<?php endif; ?>
			<tr>
			  <td colspan="2">
				<hr />
			  </td>
			</tr>
<?php
	}
  }
  // ALTERNATIVE LOGOUT LINK (when Registratrion Type is NO_REGISTRATION or OPTIONAL_REGISTRATION (and no user account was created))
  if( empty( $my->id) && !empty( $auth['user_id'])) {
  		// This is the case when a customer is logged in on the store, but not into Joomla!/Mambo
	  	?>
	  <tr> 
	    <td colspan="2">
	        <a class="<?php echo $class_mainlevel ?>" href="<?php $sess->purl(SECUREURL . "index.php?page=$page&func=userLogout");?>">
	        <?php echo $VM_LANG->_('BUTTON_LOGOUT') ?>
	        </a>
	    </td>
	  </tr>
	  <?php
  }
  

// Show DOWNLOAD Link
if (ENABLE_DOWNLOADS == '1') { ?>
  <tr> 
    <td colspan="2">
        <a class="<?php echo $class_mainlevel ?>" href="<?php $sess->purl(SECUREURL . "index.php?page=shop.downloads");?>">
        <?php echo $VM_LANG->_('PHPSHOP_DOWNLOADS_TITLE') ?>
        </a>
    </td>
  </tr><?php
}

// Show a link to the cart and show the mini cart
// Check to see if minicart module is published, if it is prevent the minicart displaying in the VM module
$q="SELECT published FROM #__modules WHERE module='mod_virtuemart_cart'";
$db->query( $q );

if (USE_AS_CATALOGUE != '1' && $show_minicart == 'yes'  && !$db->f("published")  ) {
	$_SESSION['vmMiniCart'] = true;
?>
    <tr>
        <td colspan="2">
        	<?php
	        $class_att = 'class="'. $class_mainlevel .'"';
	        $href = $sess->url($mm_action_url."index.php?page=shop.cart");
	        $href2 = $sess->url($mm_action_url."index2.php?page=shop.cart");
	        $text = $VM_LANG->_('PHPSHOP_CART_SHOW');
	        if( $useGreyBox_cartlink ) {
	        	echo vmCommonHTML::getGreyboxPopUpLink( $href2, $text, '', $text, $class_att, 500, 600, $href );
	        }
	        else {
	        	echo vmCommonHTML::hyperlink( $href, $text, '', $text, $class_att );
			}
			?>
		</td>
    </tr>
    <tr>
        <td colspan="2" class="vmCartModule">
        	<?php //				^ Do not change this class name!! It is used to update this cell after a cart action 
        	// This is the 'mini cart' file
        	include (PAGEPATH.'shop.basket_short.php');
        	?>
        </td>
    </tr>
        <?php 
} else {
	$_SESSION['vmMiniCart'] = false;
	 
}?>
   
</table>
<?php
// Just for SIMPLEBOARD compatibility !
if (@$_REQUEST['option'] != "com_virtuemart") $db = array(); 
?>
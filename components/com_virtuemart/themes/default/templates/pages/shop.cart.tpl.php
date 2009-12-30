<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id: shop.cart.tpl.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage themes
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

echo '<h2>'. JText::_('VM_CART_TITLE') .'</h2>
<!-- Cart Begins here -->
';
include(PAGEPATH. 'basket.php');
echo $basket_html;
echo '<!-- End Cart --><br /><br />
';
// show Continue Shopping link when the cart is empty 
if ($cart["idx"] == 0) {
    ?>
    <?php
      if( $continue_link != '') {
       ?>
        <a href="<?php echo $continue_link ?>" class="continue_link">
       <?php echo JText::_('VM_CONTINUE_SHOPPING'); ?>
        </a>
       <?php
    }
    else {
      $continue_link=$sess->url($_SERVER['PHP_SELF'].'?option=com_virtuemart' ); 
    ?>
    <a href="<?php echo $continue_link ?>" class="continue_link">
    <?php echo JText::_('VM_CONTINUE_SHOPPING'); ?>
    </a>
    <?php
    }
}
// end Continue Shopping link	

if ($cart["idx"]) {
    ?>
    <div align="center">
    <?php
    if( $continue_link != '') {
		?>
		 <a href="<?php echo $continue_link ?>" class="continue_link">
		 	<?php echo JText::_('VM_CONTINUE_SHOPPING'); ?>
		 </a>
		<?php
    }
        
   if (!defined('_MIN_POV_REACHED')) { ?>

       <span style="font-weight:bold;"><?php echo JText::_('VM_CHECKOUT_ERR_MIN_POV2') . " ".$CURRENCY_DISPLAY->getFullValue($_SESSION['minimum_pov']) ?></span>
       <?php
   }
   else {
   		$href = $sess->url( JROUTE::_('index.php?option=com_virtuemart&page=checkout.index&ssl_redirect=1'), true);
   		$href2 = $sess->url( JROUTE::_('index2.php?option=com_virtuemart&page=checkout.index&ssl_redirect=1'), true);
   		$class_att = 'class="checkout_link"';
   		$text = JText::_('VM_CHECKOUT_TITLE');
 		
   		if( $this->get_cfg('useGreyBoxOnCheckout', 1)) {
   			echo vmCommonHTML::getGreyBoxPopupLink( $href2, $text, '', $text, $class_att, 500, 600, $href );
   		}
   		else {
   			echo vmCommonHTML::hyperlink( $href, $text, '', $text, $class_att );
   		}
 	}
	?>
	</div>
	
	<?php
	// End if statement
}
?>
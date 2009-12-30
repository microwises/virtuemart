<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
 *
 * @version $Id: 
 * @package VirtueMart
 * @subpackage classes
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

/**
 * Favourite products support class
 * @package VirtueMart
 */
class vmfavourites extends vmAbstractObject {
	
	function addFavourite(&$d) {
		global $my,$vmLogger,$func,$sess;
		//Test if we are logged in, if not redirect to shop.favourites to log in.
		if(!$my->id) {
			$_SESSION['favourites'] = $d;
			$vmLogger->warning( JText::_('PHPSHOP_CART_ERROR_NO_NEGATIVE',false) );
			
			vmRedirect( $sess->url( 'index.php?page=shop.favourites', true, false ) );
		}
		if(isset($_SESSION['favourites'])) {
			unset($_SESSION['favourites']);
			print'session!';
			vmRedirect( $sess->url( 'index.php?page='.$d['page'].'&amp;flypage='.$d['flypage'].'&amp;product_id='.$d['product_id'], true, false ) );
		}
		print'no session';
		return true;
	} 
	
	function deleteFavourite() {
		global$my;
	}
	
	
	
	
}
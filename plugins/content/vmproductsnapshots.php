<?php
/**
 * VirtueMart Show-Product-Snapshop Mambot
 *
 * @version $Id$
 * @package VirtueMart
 * @subpackage mambots
 *
 * @copyright (C) 2004-2008 Soeren Eberhardt
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * VirtueMart is Free Software.
 * VirtueMart comes with absolute no warranty.
 *
 * http://virtuemart.net
 */

if( ! defined( '_VALID_MOS' ) && ! defined( '_JEXEC' ) )
	die( 'Direct Access to ' . basename( __FILE__ ) . ' is not allowed.' ) ;

/**
 * VirtueMart Show-Product-Snapshop Mambot
 *
 * <b>Usage:</b>
 * <code>{product_snapshot:id=XX,showname=y,showprice=n,showdesc=n,showaddtocart=y,displayeach=h,displaylist=v,width=90%,border=0,style=color:black;,align=left}</code>
 * string sku (product_sku) for more than one, separate with vertical bar
 * string showname (show the product name? y or n)
 * string showprice (show the product price? y or n)
 * string showdesc (show the product short description? y or n)
 * string quantity (the quantity to add to cart. Separate with vertical bar when there's more than one product eg 1|2|1)
 * string showaddtocart (show an "Add-to-cart" link? y or n)
 * string displayeach (the horizontal or vertical orientation of the product attributes. h or v)
 * string displaylist (the horizontal or vertical orientation of the products.
                       It only applies when there is more than one sku. h or v)
 * string width (The width of the Table element)
 * string border (The value of the Border attribute of the Table element)
 * string style (the value for the style attribute of the Table element)
 * string align (defines the align of the table with the product snapshot)
 */
//global $ps_product ;

//require_once (dirname( __FILE__ ) . "/../../components/com_virtuemart/virtuemart_parser.php") ;
include_class( "product" ) ;

//if( vmIsJoomla( '1.5', '>=' ) ) {
	$mainframe->registerEvent( 'onBeforeDisplayContent', 'pluginProductSnap' ) ;
//} else {
//	$_MAMBOTS->registerFunction( 'onPrepareContent', 'botProductSnap' ) ;
//}

//function pluginProductSnap( &$row, &$params, $page = 0 ) {
//	return handleProductSnapShot( $row, $params, $page ) ;
//}
function botProductSnap( $published, &$row, &$params, $page = 0 ) {
	return handleProductSnapShot( $row, $params, $page, $published ) ;
}
/**
 * Main Function to display Product Snapshots
 *
 * @param mosContent $row
 * @param JParams $params
 * @param int $page
 * @param boolean $published
 * @return boolean
 */
function handleProductSnapShot( &$row, &$params, $page = 0, $published = true ) {
	global $mosConfig_absolute_path, $mosConfig_live_site, $database ;
	
	// load default parameters
	if( vmIsJoomla( '1.5', '>=' ) ) {
		$db = JFactory::getDBO() ;
		$plugin = & JPluginHelper::getPlugin( 'content', 'vmproductsnapshots' ) ;
		$parameters = $plugin->params ;
	} else {
		$query = "SELECT id,params FROM #__mambots WHERE element = 'vmproductsnapshots' AND folder = 'content'" ;
		$database->setQuery( $query ) ;
		$mambot = $database->loadResult() ;
		$parameters = $mambot->params ;
	}
	$bot_params = & new vmParameters( $parameters ) ;
	$param_defaults = array( 'id' => '0' , 
												'enabled' => '1',
												'showname' => 'y' , 
												'showimage' => 'y' , 
												'showdesc' => 'n' , 
												'showprice' => 'y' , 
												'quantity' => '1' , 
												'showaddtocart' => 'y' , 
												'displaylist' => 'v' , 
												'displayeach' => 'h' , 
												'width' => '100' , 
												'border' => '0' , 
												'style' => '' , 
												'align' => '' ) ;
	// get settings from admin mambot parameters
	foreach( $param_defaults as $key => $value ) {
		$param_defaults[$key] = $bot_params->get( $key, $value ) ;
	}

	$enabled = $param_defaults['enabled'];
	if( !$published || !$enabled ) {
		$row->text = preg_replace( "/{product_snapshot:.+?}/", '', $row->text );
		return true ;
	}

	$vm_productsnap_entrytext = $row->text ;
	$vm_productsnap_matches = array( ) ;
	if( preg_match_all( "/{product_snapshot:.+?}/", $vm_productsnap_entrytext, $vm_productsnap_matches, PREG_PATTERN_ORDER ) > 0 ) {
		foreach( $vm_productsnap_matches[0] as $vm_productsnap_match ) {
			$vm_productsnap_match = str_replace( "{product_snapshot:", "", $vm_productsnap_match ) ;
			$vm_productsnap_match = str_replace( "}", "", $vm_productsnap_match ) ;
			
			// Get Bot Parameters
			$vm_productsnap_params = get_prodsnap_params( $vm_productsnap_match, $param_defaults ) ;
			
			// Get the html
			$showsnapshot = return_snapshot( $vm_productsnap_params ) ;
			
			$vm_productsnap_entrytext = preg_replace( "/{product_snapshot:.+?}/", $showsnapshot, $vm_productsnap_entrytext, 1 ) ;
		}
		$row->text = $vm_productsnap_entrytext ;
	
	}
	return ;
}

/**
 *  compare and return parameters for product snap shot.
 * @author mike howard
 * @param string $vm_productsnap_match
 * @param array $param_defaults
 * @return array
 */
function get_prodsnap_params( $vm_productsnap_match, $param_defaults ) {
	$params = explode( ",", $vm_productsnap_match ) ;
	foreach( $params as $param ) {
		$param = explode( "=", $param ) ;
		if( isset( $param_defaults[$param[0]] ) ) {
			$param_defaults[$param[0]] = $param[1] ;
		}
	}
	$param_defaults['id'] = "'" . str_replace( "|", "','", $param_defaults['id'] ) . "'" ;
	$param_defaults['quantity'] = explode( "|", $param_defaults['quantity'] ) ;
	return $param_defaults ;
}

/**
 * return the html code to show a snapshot of a product based on the product id.
 *
 * @param array $params
 * @return string
 */
function return_snapshot( &$params ) {
	
	global $sess, $VM_LANG, $mosConfig_live_site, $ps_product ;

	$db = new ps_DB();
	$html = "" ;
	
	$q = "SELECT DISTINCT product_name,product_id,product_parent_id,product_thumb_image,product_s_desc
			FROM #__{vm}_product
			WHERE product_id IN ({$params['id']})" ;
	$db->query( $q ) ;

    $ordering=explode(",",$params['id']);
    $db->recordx = array();
    foreach($ordering as $key1 => $value1) {
        foreach ($db->record as $key2 => $value2) {
            $a = "'" . $value2->product_id . "'";
            if ($a == $value1) {
                $db->recordx[] = $value2;
            }
        }
    }
    foreach($db->record as $key => $value) {
        $db->record[$key] = $db->recordx[$key];
    }
	
	$product_count = $db->num_rows() ;
	if( $product_count > 0 ) {
		$html .= "<table class=\"productsnap\" width=\"{$params['width']}\" border=\"{$params['border']}\" style=\"{$params['style']}\" " ;
		$html .= ! empty( $params['align'] ) ? "align=\"{$params['align']}\">" : ">" ;
		$html .= "\n" ;
		
		// set up how the rows and columns are displayed
		if( 'v' == $params['displayeach'] ) {
			$row_sep_top = "<tr>\n" ;
			$row_sep_btm = "</tr>\n" ;
		} else {
			$row_sep_top = "" ;
			$row_sep_btm = "" ;
		}
		
		if( 'h' == $params['displaylist'] ) {
			$start = "<tr>\n" ;
			$end = "</tr>\n" ;
		} else {
			$start = "" ;
			$end = "" ;
		}
		
		if( 'h' == $params['displaylist'] && 'v' == $params['displayeach'] ) {
			$prod_top = "<td valign=\"top\"><table>\n" ;
			$prod_btm = "</table></td>\n" ;
		} else if( $params['displaylist'] == $params['displayeach'] ) {
			$prod_top = "" ;
			$prod_btm = "" ;
		} else {
			$prod_top = "<tr>\n" ;
			$prod_btm = "</tr>\n" ;
		}
		/*
		eg of display
		list h, each h
		-- prod_sep_top "" -- prod_sep_btm "" -- start = "<tr>" -- end = "</tr>" -- row_sep_top = "<td>" -- row_sep_btm = "</td>"
		<table><tr><td>name</td><td>image</td><td>name</td><td>image</td></tr></table>
		list h, each v
		-- prod_sep_top "<td><table>" -- prod_sep_btm "</table></td>" -- start = "<tr>" -- end = "</tr>" -- row_sep_top = "<tr><td>" -- row_sep_btm = "</td></tr>"
		<table><tr><td><table><tr><td>name</td></tr><tr><td>image</td></tr></table></td><td><table><tr><td>name</td></tr><tr><td>image</td></tr></table></td></tr></table>
		list v, each h
		-- prod_sep_top "<tr>" -- prod_sep_btm "</tr>" -- start = "" -- end = "" -- row_sep_top = "<td>" -- row_sep_btm = "</td>"
		<table><tr><td>name</td><td>image</td></tr><tr><td>name</td><td>image</td></tr></table>
		list v, each v
		-- prod_sep_top "" -- prod_sep_btm "" -- start = "" -- end = "" -- row_sep_top = "<tr><td>" -- row_sep_btm = "</td></tr>"
		<table><tr><td>name</td></tr><tr><td>image</td></tr><tr><td>name</td></tr><tr><td>image</td></tr></table>
		*/
		$i = 0 ;
		$html .= $start ;
		while( $db->next_record() ) {
			$html .= $prod_top ;
			if( 'y' == $params['showname'] ) {
				$html .= $row_sep_top ;
				$html .= "<td class=\"product_name\" align=\"center\">" . $db->f( "product_name" ) . "</td>\n" ;
				$html .= $row_sep_btm ;
			}
			if( 'y' == $params['showimage'] ) {
				$html .= $row_sep_top ;
				$url = "index.php?page=" . $ps_product->get_flypage( $db->f( "product_id" ) ) ;
				if( $db->f( "product_parent_id" ) ) {
					$url = "index.php?page=shop.product_details&amp;flypage=" . $ps_product->get_flypage( $db->f( "product_parent_id" ) ) ;
					$url .= "&amp;product_id=" . $db->f( "product_parent_id" ) ;
				} else {
					$url = "index.php?page=shop.product_details&amp;flypage=" . $ps_product->get_flypage( $db->f( "product_id" ) ) ;
					$url .= "&amp;product_id=" . $db->f( "product_id" ) ;
				}
				$html .= "<td class=\"image\" align=\"center\"><a href=\"" . $sess->url( URL . $url ) . "\">" ;
				$html .= "<img alt=\"" . $db->f( "product_name" ) . "\" hspace=\"7\" src=\"" . IMAGEURL . "/product/" . $db->f( "product_thumb_image" ) . "\" width=\"90\" border=\"0\" />" ;
				$html .= "</a></td>\n" ;
				$html .= $row_sep_btm ;
			}
			if( 'y' == $params['showdesc'] ) {
				$html .= $row_sep_top ;
				$html .= "<td class=\"desc\">" . $db->f( "product_s_desc" ) . "</td>\n" ;
				$html .= $row_sep_btm ;
			}
			if( 'y' == $params['showprice'] ) {
				$html .= $row_sep_top ;
				//$html .= "<td class=\"price\">".$PHPSHOP_LANG->_PHPSHOP_CART_PRICE .": ". number_format($price["product_price"],2) . " " . $price["product_currency"]."</td>\n";
				$html .= "<td class=\"price\">" . str_replace( "$", "\\$", $ps_product->show_price( $db->f( "product_id" ) ) ) . "</td>\n" ;
				$html .= $row_sep_btm ;
			}
			if( 'y' == $params['showaddtocart'] ) {
				if( @$params['quantity'][$i] > 1 ) {
					$qty = $params['quantity'][$i] ;
				} else {
					$qty = 1 ;
				}
				$html .= $row_sep_top ;
				$html .= "<td class=\"addtocart\">" ;
				$url = "index.php?page=shop.cart&func=cartAdd&quantity=$qty&product_id=" . $db->f( "product_id" ) ;
				$html .= "<a href=\"" . $sess->url( URL . $url ) . "\"> " . $VM_LANG->_('PHPSHOP_CART_ADD_TO') ;
				if( @$params['quantity'][$i] > 1 ) {
					$html .= " x$qty" ;
				}
				$html .= "</a><br />\n</td>" ;
				$html .= $row_sep_btm ;
			}
			$html .= $prod_btm ;
			$i ++ ;
		}
		$html .= $end ;
		$html .= "</table>" ;
		return ($html) ;
	} else {
		echo 'Product not found' ;
		return ("") ;
	}
}
?>
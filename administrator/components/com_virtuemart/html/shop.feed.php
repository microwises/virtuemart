<?php
/**
* This file is used to send a product feed to the client
* Get the latest Products directly to your Desktop!
*
* @version $Id: shop.feed.php 1760 2009-05-03 22:58:57Z Aravot $
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2007 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
require_once( CLASSPATH.'connectionTools.class.php');

if( VM_FEED_ENABLED == '') {
	vmConnector::sendHeaderAndContent( 403, 'This Feed is currently not available.', '');
	exit();
}

switch( strtoupper(vmGet ( $_GET, "feed", "RSS2.0" )) ) {
	case "2.0":
	case "RSS2":
	case "RSS2.0":
		$info['feed'] = 'RSS2.0';
		break;

	case "1.0":
	case "RSS1.0":
		$info['feed'] = 'RSS1.0';
		break;

	case "0.91":
	case "RSS0.91":
		$info['feed'] = 'RSS0.91';
		break;

	case "PIE0.1":
		$info['feed'] = 'PIE0.1';
		break;

	case "MBOX":
		$info['feed'] = 'MBOX';
		break;

	case "OPML":
		$info['feed'] = 'OPML';
		break;

	case "ATOM":
	case "ATOM0.3":
		$info['feed'] = 'ATOM0.3';
		break;

	case "HTML":
		$info['feed'] = 'HTML';
		break;

	case "JS":
		// fall through
	case "JAVASCRIPT":
		$info['feed'] = 'JAVASCRIPT';
		break;
		
	default:
		$info['feed'] = 'RSS2.0';
		
}

$info['now'] = date( 'Y-m-d H:i:s', time()+$mosConfig_offset*60*60 );
$info['now_rfc822'] = gmdate("D, d M Y H:i:s", time()+$mosConfig_offset*60*60 );
$date = gmdate("Y-m-d\TH:i:sO", time()+$mosConfig_offset*60*60 );
$info['now_iso8601'] = substr($date,0,22) . ':' . substr($date,-2);

$iso = JText::getCharset();
$lang = split( '_', $mosConfig_locale );

if( $category_id ) {
	include_class('product');
	global $ps_product_category;
	$info['title'] = str_replace( '{storename}', $vendor_store_name, VM_FEED_TITLE_CATEGORIES );
	$info['title'] = str_replace( '{catname}', $ps_product_category->get_name_by_catid( $category_id ), $info['title'] );
} else {
	$info['title'] = str_replace( '{storename}', $vendor_store_name, VM_FEED_TITLE );
}
$info['item_source'] = $vendor_store_name;
$info['generator'] = $vendor_store_name." :: ProductFeed";
$info['copyright'] = 'Copyright &#169; ' .date('Y')." ". $vendor_name;
$info['authorname'] = $vendor_name;
$info['email'] = $vendor_mail;
$info['managingEditor'] = $vendor_mail . " (" . $vendor_name . ")";

$info['date'] = date( 'r' );
$info['year'] = date( 'Y' );
$info['link'] = $mosConfig_live_site;

$info['encoding'] = $iso;
$info['language'] = $lang[0];

$info['cache'] = VM_FEED_CACHE;
$info['cache_time'] = VM_FEED_CACHETIME;

$info['category_id'] 	= $category_id;// Filter by category? At this place, category_id is INT
$info['product_number'] 	= min( vmGet($_GET,'limit',20), 200 );// Print a maxmimum of 200 products
$info['feed_description'] = 'VirtueMart Product Syndication';

$info['product_description_type'] = VM_FEED_DESCRIPTION_TYPE;
$info['limit_desc'] = VM_FEED_LIMITTEXT;
$info['text_length'] = VM_FEED_MAX_TEXT_LENGTH;

$info['show_description'] = VM_FEED_SHOW_DESCRIPTION;
$info['show_price'] = VM_FEED_SHOW_PRICES;
$info['show_image'] = VM_FEED_SHOW_IMAGES;

$info['image_file'] = $vendor_image_url;

$info['image_alt'] = $vendor_store_name;

/* Setting "Time To Live"
* This is the time after reader should refresh the info
* Unit of measure: Minutes
*/
$info['TTL'] = '1440';

while( @ob_end_clean() );
ob_start();
cached_feed( $info);

// do NOTHING afterwards!
die();


function cached_feed( $feed_info ) {
	global $sess, $mosConfig_cachepath;
	
	// load feed creator class
	require_once( $GLOBALS['mosConfig_absolute_path'] .'/includes/feedcreator.class.php' );
	
	$products = getProducts( $feed_info );
	if( empty( $products )) {
		return;
	}
	$mosConfig_cachepath = empty( $mosConfig_cachepath ) ? $GLOBALS['mosConfig_absolute_path'] .'/cache' : $mosConfig_cachepath;
	$filename = $mosConfig_cachepath . "/productfeed_".$feed_info['feed']."_catid{$feed_info['category_id']}.xml";
	
	// load feed creator class
	$rss 	= new UniversalFeedCreator();
	// load image creator class
	$image 	= new FeedImage();

	// loads cache file
	if ( $feed_info['cache'] ) {		
		$rss->useCached( $feed_info['feed'], $filename, $feed_info['cache_time'] );
	}
	$rss->title 			= $feed_info['title'];
	$rss->description 		= $feed_info['feed_description'];
	$rss->link 				= htmlspecialchars( $feed_info['link'] );
	$rss->cssStyleSheet 	= NULL;
	$rss->encoding 			= $feed_info['encoding'];
	$feed_image		= $feed_info['image_file'];

	if ( $feed_image ) {
		$image->url 		= $feed_image;
		$image->link 		= $rss->link;
		$image->title 		= $feed_info['image_alt'];
		$image->description	= $rss->description;
		// loads image info into rss array
		$rss->image 		= $image;
	}
	// parameter intilization
	$feed_date 			= date( 'r' );
	$feed_year 			= date( 'Y' );
		
	$limit		= min( $feed_info['product_number'], 200 );
	
	$limit_text 		= $feed_info['limit_desc'];
	$text_length 		= $feed_info['text_length'];
	
	foreach($products as $product) {
		// load individual item creator class
		$item = new FeedItem();
		// item info
		$product_link = $sess->url( $GLOBALS['mosConfig_live_site'].'/index.php?product_id='.$product['id'].'&page=shop.product_details&category_id='.$product['category_id'].'&flypage='.$product['category_flypage'], true );
		$item->title 		= htmlspecialchars($product['name'] );
		$item->link 		= vmHtmlEntityDecode($product_link);
		$item->source 		= $product_link;
		
		$item->description = getProductDescription( $product, $feed_info );
		
		$item->date			= date( 'r', $product['cdate'] );
		$item->category     = htmlspecialchars( $product['category_name'] );

		// loads item info into rss array
		$rss->addItem( $item );
	}
	while( @ob_end_clean() );
	// save feed file
	$rss->saveFeed( $feed_info['feed'], $filename );
	
}


/**
 * Helper Function for cleaning up the description
 * It rips off the html tags and entities, because some RSS readers don't like that
 */
function cleanupDescription($stext)  {

	$stext = preg_replace('/</',' <',$stext);
	$stext = preg_replace('/>/','> ',$stext);
	$stext = html_entity_decode(strip_tags($stext));
	$stext = preg_replace('/[\n\r\t]/',' ',$stext);
	$stext = preg_replace('/ +/',' ',$stext);

	return $stext;
}

function getProductDescription( $product, &$feed_info )  {
	
	$desc = "";
	if($feed_info['show_description'] != '0') {
		if($feed_info['limit_desc'] == 1 ) {
			$desc = substr($product['description'], 0, $feed_info['text_length'] );
		}
		else {
			$desc = $product['description'];
		}
		if( $feed_info['show_image'] == '1' ) {
			$desc .= '<img src="'.$product['imageurl'].'" alt="'.$product['name'].'" vspace="5" hspace="5" align="left" border="0" />';
		}

		if( $feed_info['show_price'] == '1' ) {
			require_once( CLASSPATH . "ps_product.php" );
			$ps_product =& new ps_product();
			$desc .= "<br />".JText::_('VM_CART_PRICE').": ".$ps_product->show_price( $product['id'] );
			$desc = preg_replace( "/<span class=\"product-Old-Price\"[^>]*?>(.*?)<\/span>/si", '<strike>\1</strike>', $desc );
		}
	}
	return $desc;
}
function getProducts( &$feed_info ) {
	$db = new ps_DB();
	$q_max = "SELECT MAX(cdate) AS last_added, MAX(mdate) AS last_modified ";
	$q_max .= "FROM #__{vm}_product WHERE product_publish = 'Y' AND product_parent_id='0' ";

	$db->query($q_max);
	$db->next_record();

	$max_cdate = $db->f('last_added');
	$max_mdate = $db->f('last_modified');

	if($max_cdate < $max_mdate) {
		$orderby = "mdate";
		$feed_info['pubdate'] = $max_mdate;
	}
	else {
		$orderby = "cdate";
		$feed_info['pubdate'] = $max_mdate;
	}

	$q_products = "SELECT DISTINCT(product_sku), p.product_id, product_name, product_thumb_image, ";
	if( $feed_info['product_description_type'] == 'product_s_desc' ) {
		$q_products .= "product_s_desc as description, ";
	}
	elseif( $feed_info['product_description_type'] == 'product_desc' ) {
		$q_products .= "product_desc as description, ";
	}
	$q_products .= "p.cdate, p.mdate, c.category_name, c.category_id, category_flypage ";
	$q_products .= "\nFROM #__{vm}_product p, #__{vm}_category c,#__{vm}_product_category_xref cx 
					WHERE product_publish = 'Y' AND product_parent_id='0' 
					AND c.category_id = cx.category_id
					AND cx.product_id = p.product_id";
	if( $feed_info['category_id'] > 0 ) {
		$q_products .= "\nAND c.category_id = {$feed_info['category_id']}";
		$q_products .= "\nAND c.category_publish = 'Y' ";
	}
	$q_products .= "\nORDER BY $orderby DESC ";
	$q_products .= "\nLIMIT 0, ".$feed_info['product_number'];

	$db->query($q_products);
	$i = 0;
	$products = array();
	
	while($db->next_record() ) {
		$products[$i]['id'] = $db->f('product_id');
		$products[$i]['name'] = $db->f('product_name');
		$products[$i]['cdate'] = $db->f('cdate');
		$products[$i]['mdate'] = $db->f('mdate');
		$products[$i]['imageurl'] = strtolower(substr($db->f('product_thumb_image'),0,4))=='http' ? $db->f('product_thumb_image') : IMAGEURL.'product/'.$db->f('product_thumb_image');
		$products[$i]['description'] = $db->f('description');
		$products[$i]['category_id'] = $db->f('category_id');
		$products[$i]['category_name'] = $db->f('category_name');
		$products[$i]['category_flypage'] = $db->f('category_flypage') ? $db->f('category_flypage') : FLYPAGE;
		$i++;
	}
	return $products;
}
?>
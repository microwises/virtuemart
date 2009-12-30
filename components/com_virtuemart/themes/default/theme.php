<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
* This is the theme's function file.
* It allows you to declare additional functions and classes
* that may be used in your templates 
*
* @version $Id: theme.php 1772 2009-05-11 23:27:27Z macallf $
* @package VirtueMart
* @subpackage themes
* @copyright Copyright (C) 2006-2008 soeren - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
require_once(JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'image.php');

global $mainframe;

// include the stylesheet for this template

//if( vmIsJoomla('1.0') && mosGetParam($_REQUEST,'option') != VM_COMPONENT_NAME) {
//	// This can only be a call from a module or mambot
//	// In Joomla 1.0 it is not possible to add a JS or CSS into the HEAD from a module or content mambot,
//	// using addcustomheadtag, that's why we just print the tags here
//	echo vmCommonHTML::scriptTag(VM_THEMEURL.'theme.js');
//	echo vmCommonHTML::linkTag(VM_THEMEURL.'theme.css');
//} else {
	$vm_mainframe->addStyleSheet( VM_THEMEURL.'theme.css' );
	$vm_mainframe->addScript( VM_THEMEURL.'theme.js' );
//}
class vmTheme extends vmTemplate  {
	
	function vmTheme() {
		global $mosConfig_live_site, $vm_mainframe;

		parent::vmTemplate();
		if( !defined( "_MOOTOOLS_LOADED" )) {
			JHTML::_("behavior.mootools");
			$document =& JFactory::getDocument();
			$document->addScriptDeclaration('var cart_title = "'.JText::_('VM_CART_TITLE').'";var ok_lbl="'.JText::_('CMN_CONTINUE').'";var cancel_lbl="'.JText::_('CMN_CANCEL').'";var notice_lbl="'.JText::_('PEAR_LOG_NOTICE').'";var live_site="'.$mosConfig_live_site.'";' );
			$document->addScript(VM_THEMEURL.'js/mootools/mooPrompt.js');
			$document->addStyleSheet(VM_THEMEURL.'js/mootools/mooPrompt.css');
			define ("_MOOTOOLS_LOADED","1");
		}
		//vmCommonHTML::loadMooTools();
	}
	
	function vmBuildFullImageLink( $product ) {
		
		
		$product_image = '';
		
		$img_attributes= 'alt="'.$product['product_name'].'"';
		
		/* Wrap the Image into an URL when applicable */
		if ( @$product["product_url"] ) {
			$product_image = "<a href=\"". $product["product_url"]."\" title=\"".$product['product_name']."\" target=\"_blank\">";
			//$product_image .= ps_product::image_tag($product['product_thumb_image'], $img_attributes, 0);
			$product_image .= ImageHelper::getShopImageHtml($product['product_thumb_image'], 'product', $img_attributes, false);
			$product_image .= "</a>";
		}
		/* Show the Thumbnail with a Link to the full IMAGE */
		else {
			if( empty($product['product_full_image'] ) ) {
				$product_image = "<img src=\"".VM_THEMEURL.'images/'.NO_IMAGE."\" alt=\"".$product['product_name']."\" border=\"0\" />";
			}
			else {
				// file_exists doesn't work on remote files,
				// so returns false on remote files
				// This should fix the "Long Page generation bug"
				if( file_exists( IMAGEPATH.'product/'.$product['product_full_image'] )) {
		
					/* Get image width and height */
					if( $image_info = @getimagesize(IMAGEPATH.'product/'.$product['product_full_image'] ) ) {
						$width = $image_info[0] + 20;
						$height = $image_info[1] + 20;
					}
				}
				else {
					$width = 640;
					$height= 480;
				}
				if( stristr( $product['product_full_image'], "http" ) ) {
					$imageurl = $product['product_full_image'];
				}
				else {
					$imageurl = IMAGEURL.'product/'.rawurlencode($product['product_full_image']);
				}
				/* Build the "See Bigger Image" Link */
				if( @$_REQUEST['output'] != "pdf" && $this->get_cfg('useLightBoxImages', 1 ) ) {
					$link = $imageurl;
					//$text = ps_product::image_tag($product['product_full_image'], $img_attributes, 1,null,200,200,true)."<br/>".JText::_('VM_FLYPAGE_ENLARGE_IMAGE');
					//$text = ps_product::image_tag($product['product_thumb_image'], $img_attributes, 0)."<br/>".JText::_('VM_FLYPAGE_ENLARGE_IMAGE');
					$text = ImageHelper::getShopImageHtml($product['product_thumb_image'], 'product', $img_attributes, false);
					
					$product_image = $this->getLightboxImageLink( $link, $text, $product['product_name'], 'product'.$product['product_id'] );
				}
				elseif( @$_REQUEST['output'] != "pdf" ) {
					$link = $imageurl;
					$text = ps_product::image_tag($product['product_thumb_image'], $img_attributes, 0)."<br/>".JText::_('VM_FLYPAGE_ENLARGE_IMAGE');
					// vmPopupLink can be found in: htmlTools.class.php
					$product_image = vmPopupLink( $link, $text, $width, $height );
				}
				else {
					$product_image = "<a href=\"$imageurl\" target=\"_blank\">"
									//. ps_product::image_tag($product['product_thumb_image'], $img_attributes, 0)
									. ImageHelper::getShopImageHtml($product['product_thumb_image'], 'product', $img_attributes, false)
									. "</a>";
				}
			}
		}
		return $product_image;
	}
	
	/**
	 * Builds a list of all additional images
	 *
	 * @param int $product_id
	 * @param array $images
	 * @return string
	 */
	function vmlistAdditionalImages( $product_id, $images, $title='', $limit=1000 ) {
		global $sess;
		$html = '';
		$i = 0;
		foreach( $images as $image ) { 
			//$thumbtag = ps_product::image_tag( $image->file_name, 'class="browseProductImage"', 1, 'product', $image->file_image_thumb_width, $image->file_image_thumb_height );
			$thumbtag = ImageHelper::getShopImageHtml($image->file_name, 'product', 'class="browseProductImage"', true, $image->file_image_thumb_width, $image->file_image_thumb_height);
			$fulladdress = $sess->url( 'index2.php?page=shop.view_images&amp;image_id='.$image->file_id.'&amp;product_id='.$product_id.'&amp;pop=1' );
			
			if( $this->get_cfg('useLightBoxImages', 1 )) {
				$html .= $this->getLightboxImageLink( $image->file_url, $thumbtag, $title ? $title : $image->file_title, 'product'.$product_id );
			}
			else {
				$html .= vmPopupLink( $fulladdress, $thumbtag, 640, 550 );
			}
			$html .= ' ';
			if( ++$i > $limit ) break;
		}
		return $html;
	}
	/**
	 * Builds the "more images" link
	 *
	 * @param array $images
	 */
	function vmMoreImagesLink( $images ) {
		global $mosConfig_live_site, $sess;
		/* Build the JavaScript Link */
		$url = $sess->url( "index2.php?page=shop.view_images&amp;flypage=".@$_REQUEST['flypage']."&amp;product_id=".@$_REQUEST['product_id']."&amp;category_id=".@$_REQUEST['category_id']."&amp;pop=1" );
		$text = JText::_('VM_MORE_IMAGES').'('.count($images).')';
		$image = vmCommonHTML::imageTag( VM_THEMEURL.'images/more_images.png', $text, '', '16', '16' );
		
		return vmPopupLink( $url, $image.'<br />'.$text, 640, 550, '_blank', '', 'screenX=100,screenY=100' );
	}

	
	// Your code here please...
	/**
	 * Function to load the javascript and stylsheet files for Slimbox,
	 * a Lightbox derivate with mootools and prototype.lite
	 * @author http://www.digitalia.be/software/slimbox
	 *
	 * @param boolean $print
	 */
	function loadSlimBox( ) {
		global $mosConfig_live_site, $vm_mainframe;
		if( !defined( '_SLIMBOX_LOADED' )) {

			JHTML::_("behavior.mootools");
			$document =& JFactory::getDocument();
			$document->addScriptDeclaration('var slimboxurl = \''.VM_THEMEURL.'js/slimbox/\';' );
			$document->addScript(VM_THEMEURL.'js/slimbox/js/slimbox.js');
			$document->addStyleSheet(VM_THEMEURL.'js/slimbox/css/slimbox.css');
			
			//$vm_mainframe->addScriptDeclaration( 'var slimboxurl = \''.$mosConfig_live_site.'/components/'. VM_COMPONENT_NAME .'/js/slimbox/\';');
			//$vm_mainframe->addScript( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/slimbox/js/slimbox.js' );
			//$vm_mainframe->addStyleSheet( $mosConfig_live_site .'/components/'. VM_COMPONENT_NAME .'/js/slimbox/css/slimbox.css' );

			define ( '_SLIMBOX_LOADED', '1' );
		}
	}

	/**
	 * Returns a properly formatted image link that opens a LightBox2/Slimbox
	 *
	 * @param string $image_link Can be the image src or a complete image tag
	 * @param string $text The Link Text, e.g. 'Click here!'
	 * @param string $title The Link title, will be used as Image Caption
	 * @param string $image_group The image group name when you want to use the gallery functionality
	 * @param string $mootools Set to 'true' if you're using slimbox or another MooTools based image viewing library
	 * @return string
	 */
	function getLightboxImageLink( $image_link, $text, $title='', $image_group='' ) {

		$this->loadSlimBox();

		if( $image_group ) {
			$image_group = '['.$image_group.']';
		}
		$link = vmCommonHTML::hyperLink( $image_link, $text, '', $title, 'rel="lightbox'.$image_group.'"' );

		return $link;
	}


}
?>
<?php
// no direct access
//defined('_JEXEC') or die('Restricted access');
//
//
///**
// * Utilities for VirtueMart categories
// */
//class CategoryUtils
//{    
//	function getCategoryImageTag($image, $args="", $resize=1, $thumb_width=0, $thumb_height=0 ) 
//	{
//		if ($image != "") {
//		    // URL
//			if( substr( $image, 0, 4) == "http" ) {
//				$url = $image;
//			}
//			// local image file
//			else {
//			    $url = '<img src="'.IMAGEURL.'category'.DS.$image.'" />';
//			}	  
//		} 
//			
//		return $url; 
//    }
//
//
//	/**
//	 * Returns the img tag for the given product image
//	 *
//	 * @param string $image The name of the image OR the full URL to the image
//	 * @param string $args Additional attributes for the img tag
//	 * @param int $resize 
//	 * (1 = resize the image by using height and width attributes, 
//	 * 0 = do not resize the image)
//	 * @param string $path_appendix The path to be appended to IMAGEURL / IMAGEPATH
//	 * @return The HTML code of the img tag
//	 */
//	function getCategoryImageTag2($image, $args="", $resize=1, $path_appendix='product', $thumb_width=0, $thumb_height=0 ) {
//		global  $mosConfig_absolute_path;
//		require( CLASSPATH . 'imageTools.class.php');
//		
//		$border="";
//		if( strpos( $args, "border=" )===false ) {
//			$border = 'border="0"';
//		}
//		$height = $width = '';
//		
//		if ($image != "") {
//			// URL
//			if( substr( $image, 0, 4) == "http" ) {
//				$url = $image;
//			}
//			// local image file
//			else {
//				if(PSHOP_IMG_RESIZE_ENABLE == '1' || $resize==1) {
//					$url = JURI::base()."/components/com_virtuemart/show_image_in_imgtag.php?filename=".urlencode($image)."&amp;newxsize=".PSHOP_IMG_WIDTH."&amp;newysize=".PSHOP_IMG_HEIGHT."&amp;fileout=";
//					if( !strpos( $args, "height=" )) {
//						$arr = @getimagesize( vmImageTools::getresizedfilename( $image, $path_appendix, '', $thumb_width, $thumb_height ) );
//						$width = $arr[0]; $height = $arr[1];
//					}
//				}
//				else {
//					$url = IMAGEURL.$path_appendix.'/'.$image;
//					if( file_exists($image)) {
//						$url = str_replace( $mosConfig_absolute_path, JURI::base(), $image );
//					} elseif( file_exists($mosConfig_absolute_path.'/'.$image)) {
//						$url = JURI::base().'/'.$image;
//					}
//					
//					if( !strpos( $args, "height=" ) ) {
//						$arr = getimagesize( str_replace( IMAGEURL, IMAGEPATH, $url ) );
//						$width = $arr[0]; $height = $arr[1];
//						
//					}
//					if( $resize ) {
//						if( $height < $width ) {
//							$width = round($width / ($height / PSHOP_IMG_HEIGHT));
//							$height = PSHOP_IMG_HEIGHT;
//						} else {
//							$height = round($height / ($width / PSHOP_IMG_WIDTH ));
//							$width = PSHOP_IMG_WIDTH;
//						}
//					}
//				}
//				$url = str_replace( basename( $url ), $GLOBALS['COM_VIRTUEMART_LANG']->convert(basename($url)), $url );
//			}
//		}
//		else {
//			$url = VM_THEMEURL.'images/'.NO_IMAGE;
//		}
//		
//		return vmCommonHTML::imageTag( $url, '', '', $height, $width, '', '', $args.' '.$border );
//
//	}	
//}
//?>
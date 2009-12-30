<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
* This file contains functions and classes for common image manipulation tasks
*
* @version $Id: imageTools.class.php 1755 2009-05-01 22:45:17Z rolandd $
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

class vmImageTools {
	/**
	 * Validates an uploaded image. Creates UNIX commands to be used
	 * by the process_images function, which has to be called after
	 * the validation.
	 * @author jep
	 * @author soeren
	 * @static 
	 * @param array $d
	 * @param string $field_name The name of the field in the table $table, the image is assigned to [e.g. product_thumb_image]
	 * @param string $table_name The name of a table in the database [e.g. #__{vm}_product]
	 * @return boolean When the upload validation was sucessfull, true is returned, otherwise false
	 */
	function validate_image(&$d,$field_name,$table_name) {
		global $vmLogger;
	
		// The commands to be executed by the process_images
	    // function are returned as strings in an array here.
	    if (empty($d['image_commands']) || !empty( $_REQUEST['image_commands'])) {
	    	unset( $_REQUEST['image_commands'] );
	        $d['image_commands'] = array();
	    }
		// Generate the path to images
		$path  = IMAGEPATH;
		$path .= $table_name . "/";
	
		// Check permissions to write to destination directory
		// Workaround for Window$
		if(strstr($path , ":" )) {
			$path_begin = substr( $path, strpos( $path , ":" )+1, strlen($path));
			$path = str_replace( "//", "/", $path_begin );
		}
		if (!is_dir( $path )) {
			mkdir( $path, 0777 );
			$vmLogger->debug( 'Had to create the directory '.$path);
		}
		
		if( !is_writable($path) && !empty( $_FILES[$field_name]["tmp_name"]) ) {
			$vmLogger->err( 'Cannot write to '.$table_name.' image directory: '.$path );
			return false;
		}
		// Check for upload errors
		require_once( CLASSPATH. 'ps_product_files.php');
		ps_product_files::checkUploadedFile( $field_name );
		
		$tmp_field_name = str_replace( "thumb", "full", $field_name );
		//	Class for resizing Thumbnails
		require_once( CLASSPATH . "class.img2thumb.php");
				
		if( @$d[$tmp_field_name.'_action'] == 'auto_resize' ) {
			// Resize the Full Image
			if( !empty ( $_FILES[$tmp_field_name]["tmp_name"] )) {
				$full_file = $_FILES[$tmp_field_name]["tmp_name"];
				$image_info = getimagesize($full_file);
			}
			elseif( !empty($d[$tmp_field_name."_url"] )) {
				$tmp_file_from_url = $full_file = ps_product_files::getRemoteFile($d[$tmp_field_name."_url"]);
				if( $full_file ) {
					$vmLogger->debug( 'Successfully fetched the image file from '.$d[$tmp_field_name."_url"].' for later resizing' );
					$image_info = getimagesize($full_file);
				}
			}
			if( !empty( $image_info )) {
				
				if( $image_info[2] == 1) {
					if( function_exists("imagegif") ) {
						$ext = ".gif";
						$noimgif="";
					}
					else {
						$ext = ".jpg";
						$noimgif = ".gif";
					}
				}
				elseif( $image_info[2] == 2) {
					$ext = ".jpg";
					$noimgif="";
				}
				elseif( $image_info[2] == 3) {
					$ext = ".png";
					$noimgif="";
				}
				$vmLogger->debug( 'The resized Thumbnail will have extension '.$noimgif.$ext );
				/* Generate Image Destination File Name */
				if( !empty( $d[$table_name.'_name'] )) {
					$filename = substr( $d[$table_name.'_name'], 0, 16 );
					$filename = vmSafeFileName( $filename );
				}
				else {
					$filename = md5( 'virtuemart' );
				}
				$to_file_thumb = uniqid( $filename.'_' );
				
				$fileout = IMAGEPATH."$table_name/resized/$to_file_thumb".'_'.PSHOP_IMG_WIDTH.'x'.PSHOP_IMG_HEIGHT.$noimgif.$ext;
				
				if( !file_exists( dirname( $fileout ))) {
					mkdir( dirname( $fileout ));
					$vmLogger->debug('Created Directory '.dirname( $fileout ));
				}
				$neu = new Img2Thumb( $full_file, PSHOP_IMG_WIDTH, PSHOP_IMG_HEIGHT, $fileout, 0, 255, 255, 255 );
				$thumbname = 'resized/'.basename( $fileout );
				$vmLogger->debug( 'Finished creating the thumbnail '.$thumbname );
				
				if( isset($tmp_file_from_url) ) unlink( realpath($tmp_file_from_url) );
				$tmp_field_name = str_replace( "full", "thumb", $tmp_field_name );
				$tmp_field_name = str_replace( "_url", "", $tmp_field_name );
				$_FILES[$tmp_field_name]['tmp_name'] = $fileout;
				$_FILES[$tmp_field_name]['name'] = $thumbname;
				$d[$tmp_field_name] = $thumbname;
				
				$curr_file = isset($_REQUEST[$tmp_field_name."_curr"]) ? $_REQUEST[$tmp_field_name."_curr"] : "";
		
				if (!empty($curr_file)) {
					
		            $delete = str_replace("\\", "/", realpath($path."/".$curr_file));
		            $d["image_commands"][] = array( 'command' => 'unlink',
	        								'param1' => $delete
	        						);
					
					$vmLogger->debug( 'Preparing: delete old thumbnail image: '.$delete );
					/* Remove the resized image if exists */
					if( PSHOP_IMG_RESIZE_ENABLE=="1" ) {
						$pathinfo = pathinfo( $delete );
						isset($pathinfo["dirname"]) or $pathinfo["dirname"] = "";
						isset($pathinfo["extension"]) or $pathinfo["extension"] = "";
						$filehash = basename( $delete, ".".$pathinfo["extension"] );
						$resizedfilename = $pathinfo["dirname"]."/resized/".$filehash."_".PSHOP_IMG_WIDTH."x".PSHOP_IMG_HEIGHT.".".$pathinfo["extension"];
						
                		$d["image_commands"][] = array( 'command' => 'unlink',
        									'param1' => $resizedfilename
        								);
						$vmLogger->debug( 'Preparing: delete resized thumbnail '.$resizedfilename );
						
					}
				}
			}
		}
	
		$temp_file = isset($_FILES[$field_name]['tmp_name']) ? $_FILES[$field_name]['tmp_name'] : "";
		$file_type = isset($_FILES[$field_name]['type']) ? $_FILES[$field_name]['type'] : "";
	
		$orig_file = isset($_FILES[$field_name]["name"]) ? $_FILES[$field_name]['name'] : "";
		$curr_file = isset($_REQUEST[$field_name."_curr"]) ? $_REQUEST[$field_name."_curr"] : "";
	
		/* Generate text to display in error messages */
		if (eregi("thumb",$field_name)) {
			$image_type = "thumbnail image";
		} elseif (eregi("full",$field_name))  {
			$image_type = "full image";
		} else {
			$image_type = ereg_replace("_"," ",$field_name);
		}
		
		/* If User types "none" in Image Upload Field */
		if ( @$d[$field_name."_action"] == "delete") {
			/* If there is a current image file */
			if (!empty($curr_file)) {
				
	            $delete = str_replace("\\", "/", realpath($path."/".$curr_file));
	            $d["image_commands"][] = array( 'command' => 'unlink',
	        								'param1' => $delete
	        						);
				
				$vmLogger->debug( 'Preparing: delete old '.$image_type.' '.$delete );
				/* Remove the resized image if exists */
				if( PSHOP_IMG_RESIZE_ENABLE=="1" && $image_type == "thumbnail image") {
					$pathinfo = pathinfo( $delete );
					isset($pathinfo["dirname"]) or $pathinfo["dirname"] = "";
					isset($pathinfo["extension"]) or $pathinfo["extension"] = "";
					$filehash = basename( $delete, ".".$pathinfo["extension"] );
					$resizedfilename = $pathinfo["dirname"]."/resized/".$filehash."_".PSHOP_IMG_WIDTH."x".PSHOP_IMG_HEIGHT.".".$pathinfo["extension"];
					
                	$d["image_commands"][] = array( 'command' => 'unlink',
        									'param1' => $resizedfilename
        								);
					$vmLogger->debug( 'Preparing: delete resized thumbnail '.$resizedfilename );
					
				}
			}
			$d[$field_name] = "";
			return true;
		}
		/* If upload fails */
		elseif($orig_file and $temp_file == "none") {
			$vmLogger->err( $image_type.' upload failed.');
			return false;
		}
	
		else {
			// If nothing was entered in the Upload box, there is no image to process
			if (!$orig_file )  {
				$d[$field_name] = $curr_file;
				return true;
			}
		}
		if( empty( $temp_file )) {
			$vmLogger->err( 'The File Upload was not successful: there\'s no uploaded temporary file!' );
			return false;
		}
	
		/* Generate Image Destination File Name */
		if( !empty( $d[$table_name.'_name'] )) {
			$filename = substr( $d[$table_name.'_name'], 0, 16 );
			$filename = vmSafeFileName( $filename );
		}
		else {
			$filename = md5( 'virtuemart' );
		}
		$to_file = uniqid( $filename.'_' );
	
		/* Check image file format */
		if( $orig_file != "none" ) {
			$to_file .= $ext = '.'.Img2Thumb::GetImgType( $temp_file );
			if( !$to_file ) {
				$vmLogger->err( $image_type.' file is invalid: '.$file_type.'.' );
				return false;
			}
		}
		/*
		** If it gets to this point then there is an uploaded file in the system
		** and it is a valid image file.
		*/
	
	
		/* If Updating */
		if (!empty($curr_file)) {
			/* Command to remove old image file */
			$delete = str_replace( "\\", "/", realpath($path)."/".$curr_file);
			
        	$d["image_commands"][] = array( 'command' => 'unlink',
        								'param1' => $delete
        						);
			
			/* Remove the resized image if exists */
			if( PSHOP_IMG_RESIZE_ENABLE=="1" && $image_type == "thumbnail image") {
				$pathinfo = pathinfo( $delete );
				$filehash = basename( $delete, ".".$pathinfo["extension"] );
				$resizedfilename = $pathinfo["dirname"]."/resized/".$filehash."_".PSHOP_IMG_WIDTH."x".PSHOP_IMG_HEIGHT.".".$pathinfo["extension"];
				
           		$d["image_commands"][] = array( 'command' => 'unlink',
        								'param1' => $resizedfilename
        							);
				$vmLogger->debug( 'Preparing: delete resized thumbnail '.$resizedfilename );
				
			}
		}
	
		/* Command to move uploaded file into destination directory */
	    // Command to move uploaded file into destination directory
	    $d["image_commands"][] = array( 'command' => 'move_uploaded_file',
	        								'param1' => $temp_file,
	        								'param2' => $path.$to_file
	        						);
	    $d["image_commands"][] = array( 'command' => 'unlink',
        								'param1' => $temp_file
        							);
		
		if( empty( $d[$field_name] )) {	
			/* Return new image file name */
			$d[$field_name] = $to_file;
		}
	
		return true;
	}
	
	/**
	 * The function that safely executes $d['image_commands'] and catches errors
	 *
	 * @param array $d
	 * @return boolean True when all image commands were executed successfully, false when not
	 */
	function process_images(&$d) {
		global $vmLogger;
		require_once(CLASSPATH.'ps_product_files.php');
	        
	    if (!empty($d["image_commands"])) {
	
	        foreach( $d['image_commands'] as $exec ) {
	        	switch( $exec['command'] ) {
	        		case 'unlink':
	        			if( file_exists( $exec['param1']) ) {
	        				$ret = unlink( $exec['param1'] );
	        			} else {
	        				$ret = true;
	        			}
	        			break;
	        		case 'move_uploaded_file':
	        			if( is_uploaded_file( $exec['param1']) ) {
	        				$ret = move_uploaded_file( $exec['param1'], $exec['param2'] );
	        			} else {
	        				$ret = copy( $exec['param1'], $exec['param2'] );
	        			}
						@chmod( $exec['param2'], 0666 );
	        			break;
	        	}
	
	            if ($ret == false) {
	                $vmLogger->err ( 'The following image update command failed: '. $exec['command'] );
	                return false;
	            }
	            else {
	                $vmLogger->debug( 'Successfully processed image command: '.$exec['command'] );
	            }
	
	        }
	        $d["image_commands"] = array();
	    }
	    return true;
	}
	/**
	 * Resizes an image to a given size
	 * 
	 * @since VirtueMart 1.1.0
	 * @author soeren
	 * 
	 * @static
	 *  
	 * @param string $sourceFile
	 * @param string $resizedFile
	 * @param int $width
	 * @param int $height
	 * @param boolean $enlargeSmallerImg
	 * @return boolean
	 */
	function resizeImage($sourceFile, $resizedFile, $height, $width, $enlargeSmallerImg=false ) {
		$mainframe = JFactory::getApplication('site');
		if( $width <= 0 || $height <= 0 ) {
			if( is_callable(array($vmLogger,'err'))) {
				$mainframe->enqueueMessage('An invalid image height or weight was specified!', 'notice');
				return false;
			}
		}
		// We must take care of images which are already smaller than the size they are to be resized to
		// In most case it is not wanted to enlarge them
		$imgArr = @getimagesize( $sourceFile );
		$isSmallerThanResizeto = $imgArr[0] < $width && $imgArr[1] < $height;
		if( $isSmallerThanResizeto && !$enlargeSmallerImg ) {
			if( $sourceFile != $resizedFile ) {
				@copy( $sourceFile, $resizedFile );
			}
			$mainframe->enqueueMessage('The image '.basename( $sourceFile ).' was not resized because would have been enlarged.', 'notice');
			return false;
		}
		
		//	Class for resizing Thumbnails
		require_once( CLASSPATH . "class.img2thumb.php");
		$Img2Thumb = new Img2Thumb( $sourceFile, $width, $height, $resizedFile, 0, 255, 255, 255 );
		if( is_file( $resizedFile )) {
			return true;
		}
		else {
			return false;
		}
	}
	/**
	 * Returns the filename of an image's resized copy in the /resized folder
	 * @since VirtueMart 1.1.0
	 * @author soeren
	 * @static 
	 * @param string $filename
	 * @param string $section
	 * @param string $ext
	 * @return string
	 */
	function getResizedFilename( $filename, $section='product', $ext='', $height=0, $width=0 ) {
		$fileinfo = pathinfo( $filename );
		if( $ext == '' ) {
			$ext = $fileinfo['extension'];
		}
		
		$width = ( $width > 0 ) ? (int)$width : PSHOP_IMG_WIDTH;
		$height = ( $height > 0 ) ? (int)$height : PSHOP_IMG_HEIGHT;
		
		$basename = str_replace( "_".$height."x".$width, '', basename( $filename, '.'.$ext ) );
		return IMAGEPATH.$section.'/resized/'.$basename."_".$height."x".$width.'.'.$ext;
		
	}
}
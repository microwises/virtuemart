<?php 
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage html
* @copyright Copyright (C) 2004-2007 soeren - All rights reserved.
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

$showpage = JRequest::getVar(  'showpage');
$flypage = JRequest::getVar(  'flypage');
$product_id = JRequest::getVar(  'product_id');
$category_id = JRequest::getVar(  'category_id');

/* Who cares for Safe Mode ? Not me! */
if (@file_exists( "/usr/bin/htmldoc" )) {
	
	$load_page = $mosConfig_live_site . "/index2.php?option=com_virtuemart&page=$showpage&flypage=$flypage&product_id=$product_id&category_id=$category_id&pop=1&hide_js=1&output=pdf";
	header( "Content-Type: application/pdf" );
	header( "Content-Disposition: inline; filename=\"pdf-mambo.pdf\"" );
	flush();
	//following line for Linux only - windows may need the path as well...
	passthru( "/usr/bin/htmldoc --no-localfiles --quiet -t pdf14 --jpeg --webpage --header t.D --footer ./. --size letter --left 0.5in '$load_page'" );
	exit;
} 
else {
	freePDF( $showpage, $flypage, $product_id, $category_id );
}
function repairImageLinks( $html ) {
	
	if( PSHOP_IMG_RESIZE_ENABLE == '1' ) {
		$images = array();
		if (preg_match_all("/<img[^>]*>/", $html, $images) > 0) {
		  $i = 0;
		  foreach ($images as $image) {
			if ( is_array( $image ) ) {
			  foreach( $image as $src) {
				  preg_match("'src=\"[^\"]*\"'si", $src, $matches);
				  $source = str_replace ("src=\"", "", $matches[0]);
				  $source = str_replace ("\"", "", $source);
				  $fileNamePos = strpos($source, "filename=");
				  if ( $fileNamePos > 0 ) {
					$firstAmpersand = strpos( $source, "&" );
					$fileName = substr( $source, $fileNamePos+9, $firstAmpersand - $fileNamePos-9 );
					$extension = strrchr( $fileName, "." );
					$fileNameNoExt = str_replace( $extension, "", $fileName );
					$newSource = IMAGEURL . "product/resized/".$fileNameNoExt."_".PSHOP_IMG_WIDTH."x".PSHOP_IMG_HEIGHT.$extension;
				  }
				  else
					$newSource= $source;
					
				  $html = str_replace( $source, $newSource, $html );
			  }
			}
		  }
		}
	}
	return $html;

}
function freePDF( $showpage, $flypage, $product_id, $category_id ) {
	global $db, $sess, $auth, $my, $perm, $mosConfig_live_site, $mosConfig_sitename, $mosConfig_offset, $mosConfig_hideCreateDate, $mosConfig_hideAuthor, 
	$mosConfig_hideModifyDate,$mm_action_url, $database, $mainframe, $mosConfig_absolute_path, $vendor_full_image, $vendor_name, $limitstart, $limit,
	$vm_mainframe, $keyword, $cur_template;
	
	while( @ob_end_clean() );
	error_reporting( 0 );
	ini_set( "allow_url_fopen", "1" );
	
	switch( $showpage ) {  
		case "shop.product_details":
		  $_REQUEST['flypage'] = "shop.flypage_lite_pdf";
		  $_REQUEST['product_id'] = $product_id;

		  ob_start();
		  include( PAGEPATH . $showpage . '.php' );
		  $html .= ob_get_contents();
		  ob_end_clean();

		  $html = repairImageLinks( $html );
		  break;
		
		case "shop.browse":
		  // vmInputFilter is needed for the browse page
		  if( !isset( $vmInputFilter ) || !isset( $GLOBALS['vmInputFilter'] ) ) {
		  	$GLOBALS['vmInputFilter'] = $vmInputFilter = vmInputFilter::getInstance();
		  }

		  $_REQUEST['category_id'] = $category_id;

		  ob_start();
		  include( PAGEPATH . $showpage . '.php' );
		  $html .= ob_get_contents();
		  ob_end_clean();

		  $html = repairImageLinks( $html );
		  break;
	}
	
	$logo = IMAGEPATH . "vendor/$vendor_full_image";
	$logourl = IMAGEURL . "vendor/$vendor_full_image";
	
	if (version_compare( phpversion(), '5.0' ) < 0 || extension_loaded('domxml') || !file_exists(CLASSPATH."pdf/dompdf/dompdf_config.inc.php")) {
		
		define('FPDF_FONTPATH', CLASSPATH.'pdf/font/');
		define( 'RELATIVE_PATH', CLASSPATH.'pdf/' );
		require( CLASSPATH.'pdf/html2fpdf.php');
		require( CLASSPATH.'pdf/html2fpdf_site.php');
		
		$pdf = new PDF();
		
		$pdf->AddPage();
		$pdf->SetFont('Arial','',11);
		$pdf->InitLogo($logo);
		$pdf->PutTitle($mosConfig_sitename);
		$pdf->PutAuthor( $vendor_name );
		$pdf->WriteHTML($html);
		//Output the file
		$pdf->Output();		
	} elseif( file_exists(CLASSPATH."pdf/dompdf/dompdf_config.inc.php")) {
		// In this part you can use the dompdf library (http://www.digitaljunkies.ca/dompdf/)
		// Just extract the dompdf archive to /classes/pdf/dompdf
		$image_details = getimagesize($logo);
		$footer = '<script type="text/php">

if ( isset($pdf) ) {

  // Open the object: all drawing commands will
  // go to the object instead of the current page
  $footer = $pdf->open_object();

  $w = $pdf->get_width();
  $h = $pdf->get_height();

  // Draw a line along the bottom
  $y = $h - 2 * 12 - 24;
  $pdf->line(16, $y, $w - 16, $y, "grey", 1);

  // Add a logo
  $img_w = 2 * 72; // 2 inches, in points
  $img_h = 1 * 72; // 1 inch, in points -- change these as required
  $pdf->image("'.$logourl.'", "'.$image_details[2].'", ($w - $img_w) / 2.0, $y - $img_h, $img_w, $img_h);

  // Add the object to every page. You can
  // also specify "odd" or "even"
  $pdf->add_object($footer, "all");
  // Close the object (stop capture)
  $pdf->close_object();
}
</script>';
		
		$website = 	'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
		<head>'. $mainframe->getHead().'
			<link rel="stylesheet" href="templates/'. $cur_template .'/css/template_css.css" type="text/css" />
			<link rel="stylesheet" href="'. VM_THEMEURL .'theme.css" type="text/css" />
			<link rel="shortcut icon" href="'. $mosConfig_live_site .'/images/favicon.ico" />
			<meta http-equiv="Content-Type" content="text/html; '. _ISO.'" />
			<meta name="robots" content="noindex, nofollow" />
		</head>
		<body class="contentpane">
			' . $html .'
			' . $footer .'
		</body>
	</html>';
		//die( htmlspecialchars($website));
		require_once( CLASSPATH."pdf/dompdf/dompdf_config.inc.php");
		$dompdf = new DOMPDF();
		$dompdf->load_html($website);
		$dompdf->render();
		$dompdf->stream("pdf_file.pdf", array('Attachment' => 0));
	}

	
}

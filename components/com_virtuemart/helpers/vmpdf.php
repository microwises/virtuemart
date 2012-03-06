<?php
defined('_JEXEC') or die('');
/**
 * abstract controller class containing get,store,delete,publish and pagination
 *
 *
 * This class provides the functions for the calculatoins
 *
 * @package	VirtueMart
 * @subpackage Helpers
 * @author Max Milbers
 * @copyright Copyright (c) 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

class vmPdf {

	function createVmPdf($view){

		if(!file_exists(JPATH_VM_LIBRARIES.DS.'tcpdf'.DS.'tcpdf.php')){
			vmError('vmPdf: For the pdf, you must install the tcpdf library at '.JPATH_VM_LIBRARIES.DS.'tcpdf');
			return 0;
		}
		// create new PDF document
		$pdf = new myTcPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator('Invoice by Virtuemart 2, used library tcpdf');
		$pdf->SetAuthor($view->vendor->vendor_name);

		$pdf->SetTitle(JText::_('COM_VIRTUEMART_INVOICE_TITLE'));
		$pdf->SetSubject(JText::sprintf('COM_VIRTUEMART_INVOICE_SUBJ',$view->vendor->vendor_store_name));
		$pdf->SetKeywords('Invoice by Virtuemart 2');

		//virtuemart.cloudaccess.net/index.php?option=com_virtuemart&view=invoice&layout=details&virtuemart_order_id=18&order_number=6e074d9b&order_pass=p_9cb9e2&task=checkStoreInvoice
		if(empty($view->vendor->images[0])){
			vmError('Vendor image given path empty ');
		} else if(empty($view->vendor->images[0]->file_url_folder) or empty($view->vendor->images[0]->file_name) or empty($view->vendor->images[0]->file_extension) ){
			vmError('Vendor image given image is not complete '.$view->vendor->images[0]->file_url_folder.$view->vendor->images[0]->file_name.'.'.$view->vendor->images[0]->file_extension);
			vmdebug('Vendor image given image is not complete, the given media',$view->vendor->images[0]);
		} else if(!empty($view->vendor->images[0]->file_extension) and strtolower($view->vendor->images[0]->file_extension)=='png'){
			vmError('Warning extension of the image is a png, tpcdf has problems with that in the header, choose a jpg or gif');
		} else {
			$imagePath = DS. str_replace('/',DS, $view->vendor->images[0]->file_url_folder.$view->vendor->images[0]->file_name.'.'.$view->vendor->images[0]->file_extension);
			if(!file_exists(JPATH_ROOT.$imagePath)){
				vmError('Vendor image missing '.$imagePath);
			} else {
				$pdf->SetHeaderData($imagePath, 60, $view->vendor->vendor_store_name, $view->vendorAddress);
			}
		}

		// set header and footer fonts
		$pdf->setHeaderFont(Array('helvetica', '', 8));
		$pdf->setFooterFont(Array('helvetica', '', 10));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		//TODO include the right file (in libraries/tcpdf/config/lang set some language-dependent strings
		$l='';
		$pdf->setLanguageArray($l);

		// set default font subsetting mode
		$pdf->setFontSubsetting(true);

		// Set font
		// dejavusans is a UTF-8 Unicode font, if you only need to
		// print standard ASCII chars, you can use core fonts like
		// helvetica or times to reduce file size.
		$pdf->SetFont('helvetica', '', 8, '', true);

		// Add a page
		// This method has several options, check the source code documentation for more information.
		$pdf->AddPage();

		// Set some content to print
		// $html =

		// Print text using writeHTMLCell()
		$pdf->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);


		// Close and output PDF document
		// This method has several options, check the source code documentation for more information.
		$pdf->Output($path, 'F');

		// 			vmdebug('Pdf object ',$pdf);
		// 		vmdebug('checkStoreInvoice start');
		return $path;

	}
}

if(!file_exists(JPATH_VM_LIBRARIES.DS.'tcpdf'.DS.'tcpdf.php')){
	vmError('vmPdf: For the pdf, you must install the tcpdf library at '.JPATH_VM_LIBRARIES.DS.'tcpdf');
} else {
	if(!class_exists('TCPDF'))	require(JPATH_VM_LIBRARIES.DS.'tcpdf'.DS.'tcpdf.php');
	// Extend the TCPDF class to create custom Header and Footer
	class myTcPDF extends TCPDF {

		public function __construct() {
			parent::__construct();
		}

		//Page header
		/*	public function Header() {
		// Logo
		$image_file = K_PATH_IMAGES.'logo_example.jpg';
		$this->Image($image_file, 10, 10, 15, '', 'JPG', '', 'T', false, 300, '', false, false, 0, false, false, false);
		// Set font
		$this->SetFont('helvetica', 'B', 20);
		// Title
		$this->Cell(0, 15, '<< TCPDF Example 003 >>', 0, false, 'C', 0, '', 0, false, 'M', 'M');
		}*/

		// Page footer
		public function Footer() {
			// Position at 15 mm from bottom
			$this->SetY(-15);
			// Set font
			$this->SetFont('helvetica', 'I', 8);

			$vendorModel = VmModel::getModel('vendor');
			$vendor = & $vendorModel->getVendor();
			// 			$this->assignRef('vendor', $vendor);
			$vendorModel->addImages($vendor,1);
			//vmdebug('$vendor',$vendor);
			$html = $vendor->vendor_legal_info."<br /> Page ".$this->getAliasNumPage().'/'.$this->getAliasNbPages();
			// Page number
			$this->writeHTMLCell($w=0, $h=0, $x='', $y='', $html, $border=0, $ln=1, $fill=0, $reseth=true, $align='', $autopadding=true);

			// 		$this->writeHTML(0, 10, $vendor->vendor_legal_info."<br /> Page ".$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		}
	}
}


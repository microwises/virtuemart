<?php
/**
 *
 * Controller for the front end Orderviews
 *
 * @package	VirtueMart
 * @subpackage User
 * @author Oscar van Eijk
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: orders.php 5432 2012-02-14 02:20:35Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the controller framework
jimport('joomla.application.component.controller');

/**
 * VirtueMart Component Controller
 *
 * @package		VirtueMart
 */
class VirtueMartControllerInvoice extends JController
{

	function checkStoreInvoice($orderDetails = 0){

		vmdebug('checkStoreInvoice start');
		//Test
		return false;

		$force = true;

		//	@ini_set( 'max_execution_time', 5 );

		$path = VmConfig::get('forSale_path',0);
		if($path===0 ){
			vmError('No path set to store invoices');
			return false;
		} else {
			$path .= 'invoices'.DS;
			if(!file_exists($path)){
				vmError('Path wrong to store invoices, folder invoices does not exist '.$path);
				return false;
			} else if(!is_writable( $path )){
				vmError('Cannot store pdf, directory not writeable '.$path);
				return false;
			}
		}

		$orderModel = VmModel::getModel('orders');

		if($orderDetails==0){
			$_currentUser = JFactory::getUser();
			$cuid = $_currentUser->get('id');

			if(empty($cuid)){
				// If the user is not logged in, we will check the order number and order pass
				if ($orderPass = JRequest::getString('order_pass',false)){
					$orderNumber = JRequest::getString('order_number',false);
					$orderId = $orderModel->getOrderIdByOrderPass($orderNumber,$orderPass);
					if(empty($orderId)){
						echo JText::_('COM_VIRTUEMART_RESTRICTED_ACCESS');
						// 						$app= JFactory::getApplication();
						// 						$app -> redirect('index.php');
						return 0;
					}
					$orderDetails = $orderModel->getOrder($orderId);
				}
			}
			else {
				// If the user is logged in, we will check if the order belongs to him
				$virtuemart_order_id = JRequest::getInt('virtuemart_order_id',0) ;
				if (!$virtuemart_order_id) {
					$virtuemart_order_id = $orderModel->getOrderIdByOrderNumber(JRequest::getString('order_number'));
				}
				$orderDetails = $orderModel->getOrder($virtuemart_order_id);

				if(!class_exists('Permissions')) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'permissions.php');
				if(!Permissions::getInstance()->check("admin")) {
					if(!empty($orderDetails['details']['BT']->virtuemart_user_id)){
						if ($orderDetails['details']['BT']->virtuemart_user_id != $cuid) {
							echo JText::_('COM_VIRTUEMART_RESTRICTED_ACCESS');
							return 0;
						}
					}
				}
			}
		}
		// 			vmdebug('$orderDetails in my pdf controller ',$orderDetails['details']['BT']);
		$invoiceNumber = $orderModel->createInvoiceNumber($orderDetails['details']['BT']);

		if(!$invoiceNumber or empty($invoiceNumber)){
			vmError('Cant create pdf, createInvoiceNumber failed');;
			return 0;
		}
		$path .= 'vminvoice_'.$invoiceNumber.'.pdf';


		if(file_exists($path) and !$force){
			return $path;
		}

		// 			$app = JFactory::getApplication('site');

		//We come from the be, so we need to load the FE langauge
		$jlang =& JFactory::getLanguage();
		$jlang->load('com_virtuemart', JPATH_SITE, 'en-GB', true);
		$jlang->load('com_virtuemart', JPATH_SITE, $jlang->getDefault(), true);
		$jlang->load('com_virtuemart', JPATH_SITE, null, true);

		$this->addViewPath( JPATH_VM_SITE.DS.'views' );
		$format = 'html';
		$viewName= 'invoice';
		$view = $this->getView($viewName, $format);

		$view->addTemplatePath( JPATH_VM_SITE.DS.'views'.DS.'invoice'.DS.'tmpl' );

		$view->invoiceNumber = $invoiceNumber;
		$view->order = $orderDetails;

		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		$vendorId = 1;
		$vendorModel = VmModel::getModel('vendor');
		$vendorModel->setId($vendorId);
		$vendor = $vendorModel->getVendor();
		// 		$this->assignRef('vendor', $vendor);
		$vendorModel->addImages($vendor,$vendorId);
		// 			vmdebug('$vendor',$vendor);

		//TODO use the new function vendorData = $usermodel->getVendor($vendorId); instead
		$userId = $vendorModel->getUserIdByVendorId(1);
		$usermodel = VmModel::getModel('user');
		$virtuemart_userinfo_id = $usermodel->getBTuserinfo_id($userId);
		$userFields = $usermodel->getUserInfoInUserFields('invoice', 'BT', $virtuemart_userinfo_id);
		$address=$userFields[1]['fields']['address_1']['value'];
		if ($userFields[1]['fields']['address_2']['value']) {
		    $address.="\n".$userFields[1]['fields']['address_2']['value'];
		}
		$address.="\n".$userFields[1]['fields']['zip']['value']." ".$userFields[1]['fields']['city']['value'];
		$address.="\n".$userFields[1]['fields']['virtuemart_country_id']['value'];

		// create new PDF document
		$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator('Invoice by Virtuemart 2, used library tcpdf');
		$pdf->SetAuthor($vendor->vendor_name);


		$pdf->SetTitle(JText::_('COM_VIRTUEMART_INVOICE_TITLE'));
		$pdf->SetSubject(JText::sprintf('COM_VIRTUEMART_INVOICE_SUBJ',$vendor->vendor_store_name));
		$pdf->SetKeywords('Invoice by Virtuemart 2');

		//virtuemart.cloudaccess.net/index.php?option=com_virtuemart&view=invoice&layout=details&virtuemart_order_id=18&order_number=6e074d9b&order_pass=p_9cb9e2&task=checkStoreInvoice
		if(empty($vendor->images[0])){
			vmError('Vendor image given path empty ');
		} else if(empty($vendor->images[0]->file_url_folder) or empty($vendor->images[0]->file_name) or empty($vendor->images[0]->file_extension) ){
			vmError('Vendor image given image is not complete '.$vendor->images[0]->file_url_folder.$vendor->images[0]->file_name.'.'.$vendor->images[0]->file_extension);
			vmdebug('Vendor image given image is not complete, the given media',$vendor->images[0]);
		} else if(!empty($vendor->images[0]->file_extension) and strtolower($vendor->images[0]->file_extension)=='png'){
			vmError('Warning extension of the image is a png, tpcdf has problems with that in the header, choose a jpg or gif');
		} else {
			$imagePath = DS. str_replace('/',DS, $vendor->images[0]->file_url_folder.$vendor->images[0]->file_name.'.'.$vendor->images[0]->file_extension);
			if(!file_exists(JPATH_ROOT.$imagePath)){
				vmError('Vendor image missing '.$imagePath);
			} else {
				$pdf->SetHeaderData($imagePath, 60, $vendor->vendor_store_name, $address);
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

		//set some language-dependent strings
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
		vmdebug('checkStoreInvoice start');
		return $path;
	}
}



require_once(JPATH_VM_LIBRARIES.DS.'tcpdf'.DS.'tcpdf.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

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
// No closing tag

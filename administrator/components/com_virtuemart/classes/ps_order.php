<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
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
 * The class handles orders from an adminstrative perspective.  Order
 * processing is handled in the ps_checkout class.
 */
class ps_order {


	/**
	 * Gets the vendorid saved by the orderitem.
	 * @author Max Milbers
	 * @param db
	 * @param int $order_id
	 */
	function get_vendor_id_by_order_id(&$order_id){
		global $db;
		$q = 'SELECT vendor_id FROM #__{vm}_order_item WHERE order_id='.$order_id;
		$db->query($q);
		$db->next_record();
		$vendor_id = $db->f('vendor_id');
		return $vendor_id;
	}

	/**
     * Changes the status of an order
     * @author pablo
     * @author soeren
     * @author Uli
     * 
     *
     * @param array $d
     * @return boolean
    */
	function order_status_update(&$d) {
		global $mosConfig_offset, $vm_mainframe;
		
		$db = new ps_DB;
		$timestamp = time() + ($mosConfig_offset*60*60);
		$mysqlDatetime = date("Y-m-d G:i:s",$timestamp);

		if( empty($_REQUEST['include_comment'])) {
			$include_comment="N";
		}

		// get the current order status
		$curr_order_status = @$d["current_order_status"];
		$notify_customer = empty($d['notify_customer']) ? "N" : $d['notify_customer'];
		if( $notify_customer=="Y" ) {
			$notify_customer=1; 
		}
		else {
			$notify_customer=0;
		}

		$d['order_comment'] = empty($d['order_comment']) ? "" : $d['order_comment'];
		if( empty($d['order_item_id']) ) {
			// When the order is set to "confirmed", we can capture
			// the Payment
			if( ($curr_order_status=="P" || $curr_order_status=="C") && $d["order_status"]=="S") {
				$q = "SELECT order_number,element,order_payment_trans_id FROM #__{vm}_order_payment,#__{vm}_orders WHERE ";
				$q .= "#__{vm}_order_payment.order_id='".$db->getEscaped($d['order_id'])."' ";
				$q .= "AND #__{vm}_orders.order_id='".$db->getEscaped($d['order_id'])."' ";
				$db->query( $q );
				$db->next_record();
				require_once( CLASSPATH.'paymentMethod.class.php');
				vmPaymentMethod::importPaymentPluginById($db->f('payment_method_id'));
				$d["order_number"] = $db->f("order_number");
				$result = $vm_mainframe->triggerEvent('capture_payment', array( $d ));
				if( $result !== false ) {
					if( $result[0] === false ) {
						return false;
					}
				}
			}

	
			/*
			 * If a pending order gets cancelled, void the authorization.
			 *
			 * It might work on captured cards too, if we want to
			 * void shipped orders.
			 *
			 */
			if( $curr_order_status=="P" && $d["order_status"]=="X") {
				$q = "SELECT order_number,element,order_payment_trans_id FROM #__{vm}_order_payment,#__{vm}_orders WHERE ";
				$q .= "#__{vm}_order_payment.order_id='".$db->getEscaped($d['order_id'])."' ";
				$q .= "AND #__{vm}_orders.order_id='".$db->getEscaped($d['order_id'])."' ";
				$db->query( $q );
				$db->next_record();
				require_once( CLASSPATH.'paymentMethod.class.php');
				vmPaymentMethod::importPaymentPluginById($db->f('payment_method_id'));
				$d["order_number"] = $db->f("order_number");
				$result = $vm_mainframe->triggerEvent('void_authorization', array( $d ));
				if( $result !== false ) {
					if( $result[0] === false ) {
						return false;
					}
				}
			}
	
			$fields =array( 'order_status'=> $d["order_status"], 
										'mdate'=> $timestamp );
			$db->buildQuery('UPDATE', '#__{vm}_orders', $fields, "WHERE order_id='" . $db->getEscaped($d["order_id"]) . "'");
			$db->query();
	
			// Update the Order History.
			$fields = array( 'order_id' => $d["order_id"],
										'order_status_code' => $d["order_status"],
										'date_added' => $mysqlDatetime,
										'customer_notified' => $notify_customer,
										'comments' => $d['order_comment']
							);
			$db->buildQuery('INSERT', '#__{vm}_order_history', $fields );
			$db->query();
	
			// Do we need to re-update the Stock Level?
			if( (strtoupper($d["order_status"]) == "X" || strtoupper($d["order_status"])=="R") 
				// && CHECK_STOCK == '1'
				&& $curr_order_status != $d["order_status"]
				) {
				// Get the order items and update the stock level
				// to the number before the order was placed
				$q = "SELECT product_id, product_quantity FROM #__{vm}_order_item WHERE order_id='".$db->getEscaped($d["order_id"])."'";
				$db->query( $q );
				$dbu = new ps_DB;
				// Now update each ordered product
				while( $db->next_record() ) {
					$q = "UPDATE #__{vm}_product 
							SET product_in_stock=product_in_stock+".$db->f("product_quantity").",
								product_sales=product_sales-".$db->f("product_quantity")." 
							WHERE product_id='".$db->f("product_id")."'";
					$dbu->query( $q );
				}
			}
			// Update the Order Items' status
			$q = "SELECT order_item_id FROM #__{vm}_order_item WHERE order_id=".$db->getEscaped($d['order_id']);
			$db->query($q);
			$dbu = new ps_DB;
			while ($db->next_record()) {
				$item_id = $db->f("order_item_id");
				$fields =array( 'order_status'=> $d["order_status"], 
											'mdate'=> $timestamp );
				$dbu->buildQuery('UPDATE', '#__{vm}_order_item', $fields, "WHERE order_item_id='" .(int)$item_id . "'");
				$dbu->query();
			}
			
			if (ENABLE_DOWNLOADS == '1') {
				##################
				## DOWNLOAD MOD
				$this->mail_download_id( $d );
			}
	
			if( !empty($notify_customer) ) {
				$this->notify_customer( $d );
			}
		} elseif( !empty($d['order_item_id'])) {
				$fields =array( 'order_status'=> $d["order_status"], 
											'mdate'=> $timestamp );
				$db->buildQuery('UPDATE', '#__{vm}_order_item', $fields, 'WHERE order_item_id='.intval( $d['order_item_id'] ));
				return $db->query() !== false;
		}
		return true;
	}

	/**
	 * mails the Download-ID to the customer
	 * or deletes the Download-ID from the product_downloads table
	 *
	 * @param array $d
	 * @return boolean
	 */
	function mail_download_id( &$d ){

		global $sess,	 $vmLogger;

		$url = URL."index.php?option=com_virtuemart&page=shop.downloads&Itemid=".$sess->getShopItemid();
		
		$db = new ps_DB();
		$db->query( 'SELECT order_status FROM #__{vm}_orders WHERE order_id='.(int)$d['order_id'] );
		$db->next_record();
		
		if ($db->f("order_status")==ENABLE_DOWNLOAD_STATUS) {
			$dbw = new ps_DB;
			
			$q = "SELECT order_id,user_id,download_id,file_name FROM #__{vm}_product_download WHERE";
			$q .= " order_id = '" . (int)$d["order_id"] . "'";
			$dbw->query($q);
			$dbw->next_record();
			$userid = $dbw->f("user_id");
			$download_id = $dbw->f("download_id");
			$datei=$dbw->f("file_name");
			$dbw->reset();

			if ($download_id) {

				// really 1?
				$vendor_id = 1;
				$dbv = ps_vendor::get_vendor_fields($vendor_id,array("email","vendor_name"),"");

				$db = new ps_DB;
				//Changed by Max Milbers merging #__{vm}_user_info.user_email to #__users.email
				$q="SELECT first_name,last_name, email FROM #__{vm}_user_info ";
				$q .= "LEFT JOIN #__users ju ON (ju.id = u.user_id) WHERE user_id = '$userid' AND address_type='BT'";

				$db->query($q);
				$db->next_record();
				
				$message = JText::_('HI',false) .' '. $db->f("first_name") .($db->f("middle_name")?' '.$db->f("middle_name") : '' ). ' ' . $db->f("last_name") . ",\n\n";
				$message .= JText::_('VM_DOWNLOADS_SEND_MSG_1',false).".\n";
				$message .= JText::_('VM_DOWNLOADS_SEND_MSG_2',false)."\n\n";

				while($dbw->next_record()) {
					$message .= $dbw->f("file_name").": ".$dbw->f("download_id")
					. "\n$url&download_id=".$dbw->f("download_id")."\n\n";
				}

				$message .= JText::_('VM_DOWNLOADS_SEND_MSG_3',false) . DOWNLOAD_MAX."\n";
				$expire = ((DOWNLOAD_EXPIRE / 60) / 60) / 24;
				$message .= str_replace("{expire}", $expire, JText::_('VM_DOWNLOADS_SEND_MSG_4',false));
				$message .= "\n\n____________________________________________________________\n";
				$message .= JText::_('VM_DOWNLOADS_SEND_MSG_5',false)."\n";
				$message .= $dbv->f("vendor_name") . " \n" . URL."\n\n".$dbv->f("email") . "\n";
				$message .= "____________________________________________________________\n";
				$message .= JText::_('VM_DOWNLOADS_SEND_MSG_6',false) . $dbv->f("vendor_name");


				$mail_Body = $message;
				$mail_Subject = JText::_('VM_DOWNLOADS_SEND_SUBJ',false);

				$result = vmMail( $dbv->f("email"), $dbv->f("vendor_name"), 
						$db->f("email"), $mail_Subject, $mail_Body, '' );

				if ($result) {
					$vmLogger->info( JText::_('VM_DOWNLOADS_SEND_MSG',false). " ". $db->f("first_name") . " " . $db->f("last_name") . " ".$db->f("email") );
				}
				else {
					$vmLogger->warning( JText::_('VM_DOWNLOADS_ERR_SEND',false)." ". $db->f("first_name") . " " . $db->f("last_name") . ", ".$db->f("email") );
				}
			} 
		}
		elseif ($d["order_status"]==DISABLE_DOWNLOAD_STATUS) {
			$q = "DELETE FROM #__{vm}_product_download WHERE order_id=" . (int)$d["order_id"];
			$db->query($q);
			$db->next_record();
		}

		return true;
	}

	/**
	 * notifies the customer that the Order Status has been changed
	 *
	 * @param array $d
	 */
	function notify_customer( &$d ){

		global  $sess, $vmLogger;

		$url = SECUREURL."index.php?option=com_virtuemart&page=account.order_details&order_id=".urlencode($d["order_id"]).'&Itemid='.$sess->getShopItemid();

		$db = new ps_DB;
		$order_id = $db->getEscaped($d["order_id"]);
		
		$vendor_id = ps_order::get_vendor_id_by_order_id($order_id);
		
		$dbv = ps_vendor::get_vendor_fields($vendor_id,array("email","vendor_name"));

		$q = "SELECT first_name,last_name,email,order_status_name FROM #__{vm}_order_user_info,#__{vm}_orders,#__{vm}_order_status ";
		$q .= "WHERE #__{vm}_orders.order_id = '".$order_id."' ";
		$q .= "AND #__{vm}_orders.user_id = #__{vm}_order_user_info.user_id ";
		$q .= "AND #__{vm}_orders.order_id = #__{vm}_order_user_info.order_id ";
		$q .= "AND order_status = order_status_code ";
		$db->query($q);
		$db->next_record();

		// MAIL BODY
		$message = JText::_('HI',false) .' '. $db->f("first_name") . ($db->f("middle_name")?' '.$db->f("middle_name") : '' ). ' ' . $db->f("last_name") . ",\n\n";
		$message .= JText::_('VM_ORDER_STATUS_CHANGE_SEND_MSG_1',false)."\n\n";

		if( !empty($d['include_comment']) && !empty($d['order_comment']) ) {
			$message .= JText::_('VM_ORDER_HISTORY_COMMENT_EMAIL',false).":\n";
			$message .= $d['order_comment'];
			$message .= "\n____________________________________________________________\n\n";
		}

		$message .= JText::_('VM_ORDER_STATUS_CHANGE_SEND_MSG_2',false)."\n";
		$message .= "____________________________________________________________\n\n";
		$message .= $db->f("order_status_name");

		if( VM_REGISTRATION_TYPE != 'NO_REGISTRATION' ) {
			$message .= "\n____________________________________________________________\n\n";
			$message .= JText::_('VM_ORDER_STATUS_CHANGE_SEND_MSG_3',false)."\n";
			$message .= $url;
		}
		$message .= "\n\n____________________________________________________________\n";
		$message .= $dbv->f("vendor_name") . " \n";
		$message .= URL."\n";
		$message .= $dbv->f("email");

		$message = str_replace( "{order_id}", $d["order_id"], $message );

		$mail_Body = html_entity_decode($message);
		$mail_Subject = str_replace( "{order_id}", $d["order_id"], JText::_('VM_ORDER_STATUS_CHANGE_SEND_SUBJ',false));
		
		
		$result = vmMail( $dbv->f("email"),  $dbv->f("vendor_name"), 
					$db->f("email"), $mail_Subject, $mail_Body, '' );
		
		/* Send the email */
		if ($result) {
			$vmLogger->info( JText::_('VM_DOWNLOADS_SEND_MSG',false). " ". $db->f("first_name") . " " . $db->f("last_name") . ", ".$db->f("email") );
		}
		else {
			$vmLogger->warning( JText::_('VM_DOWNLOADS_ERR_SEND',false).' '. $db->f("first_name") . " " . $db->f("last_name") . ", ".$db->f("email")." (". $result->ErrorInfo.")" );
			$GLOBALS['vmLogger']->debug('From: '.$dbv->f("email"));
			$GLOBALS['vmLogger']->debug('To: '.$db->f("email"));
		}
	}
	/**
	 * This function inserts the DOWNLOAD IDs for all files associated with this product
	 * so the customer can later download the purchased files
	 * @static 
	 * @since 1.1.0
	 * @param int $product_id
	 * @param int $order_id
	 * @param int $user_id
	 */
	function insert_downloads_for_product( &$d ) {
		$db = new ps_DB();
		$dbd = new ps_DB();
		if( empty( $d['product_id'] ) || empty( $d['order_id'] )) {
			return false;
		}
		
		$dl = "SELECT attribute_name,attribute_value ";
		$dl .= "FROM #__{vm}_product_attribute WHERE product_id='".$d['product_id']."'";
		$dl .= " AND attribute_name='download'";
		$db->query($dl);
		$dlnum = 0;
		while($db->next_record()) {

			$str = (int)$d['order_id'];
			$str .= $d['product_id'];
		    $str .= uniqid('download_');
			$str .= $dlnum++;
			$str .= time();

			$download_id = md5($str);

			$fields = array('product_id' => $d['product_id'], 
							'user_id' => (int)$d['user_id'], 
							'order_id' => (int)$d['order_id'], 
							'end_date' => '0', 
							'download_max' => DOWNLOAD_MAX, 
							'download_id' => $download_id, 
							'file_name' => $db->f("attribute_value")
							);
			$dbd->buildQuery('INSERT', '#__{vm}_product_download', $fields );
			$dbd->query();
		}
	}

	/**
	 * Handles a download Request
	 *
	 * @param array $d
	 * @return boolean
	 */
	function download_request(&$d) {
		global  $download_id, $vmLogger;

		$db = new ps_DB;
		$download_id = $db->getEscaped( vmGet( $d, "download_id" ) );

		$q = "SELECT * FROM #__{vm}_product_download WHERE";
		$q .= " download_id = '$download_id'";

		$db->query($q);
		$db->next_record();

		$download_id = $db->f("download_id");
		$file_name = $db->f("file_name");
		if( strncmp($file_name, 'http', 4 ) !== 0) {
			$datei = DOWNLOADROOT . $file_name;
		} else {
			$datei = $file_name;
		}
		$download_max = $db->f("download_max");
		$end_date = $db->f("end_date");
		$zeit=time();

		if (!$download_id) {
			$vmLogger->err( JText::_('VM_DOWNLOADS_ERR_INV',false) );
			return false;
			//vmRedirect("index.php?option=com_virtuemart&page=shop.downloads", $d["error"]);
		}

		elseif ($download_max=="0") {
			$q ="DELETE FROM #__{vm}_product_download";
			$q .=" WHERE download_id = '" . $download_id . "'";
			$db->query($q);
			$db->next_record();
			$vmLogger->err( JText::_('VM_DOWNLOADS_ERR_MAX',false) );
			return false;
			//vmRedirect("index.php?option=com_virtuemart&page=shop.downloads", $d["error"]);
		}

		elseif ($end_date!="0" && $zeit > $end_date) {
			$q ="DELETE FROM #__{vm}_product_download";
			$q .=" WHERE download_id = '" . $download_id . "'";
			$db->query($q);
			$db->next_record();
			$vmLogger->err( JText::_('VM_DOWNLOADS_ERR_EXP',false) );
			return false;
			//vmRedirect("index.php?option=com_virtuemart&page=shop.downloads", $d["error"]);
		}
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'connection.php');
		
		$download_count = true;
		
		if ( @file_exists( $datei ) ){
			// Check if this is a request for a special range of the file (=Resume Download)
			$range_request = VmConnector::http_rangeRequest( filesize($datei), false );
			if( $range_request[0] == 0 ) {
				// this is not a request to resume a download,
				$download_count = true;
			} else {
				$download_count = false;
			}
		} else {
			$download_count = false;
		}

		// Parameter to check if the file should be removed after download, which is only true,
		// if we have a remote file, which was transferred to this server into a temporary file
		$unlink = false;
		
		if( strncmp($datei, 'http', 4 ) === 0) {
			require_once( CLASSPATH.'ps_product_files.php');
			$datei_local = ps_product_files::getRemoteFile($datei);
			if( $datei_local !== false ) {
				$datei = $datei_local;
				$unlink = true;
			} else {
				$vmLogger->err( JText::_('VM_DOWNLOAD_FILE_NOTFOUND',false) );
				return false;
			}
		}
		else {
			// Check, if file path is correct
			// and file is
			if ( !@file_exists( $datei ) ){
				$vmLogger->err( JText::_('VM_DOWNLOAD_FILE_NOTFOUND',false) );
				return false;
				//vmRedirect("index.php?option=com_virtuemart&page=shop.downloads", $d["error"]);
			}
			if ( !@is_readable( $datei ) ) {
				$vmLogger->err( JText::_('VM_DOWNLOAD_FILE_NOTREADABLE',false) );
				return false;
				//vmRedirect("index.php?option=com_virtuemart&page=shop.downloads", $d["error"]);
			}
		}
		if( $download_count ) {
			// decrement the download_max to limit the number of downloads
			$q ="UPDATE `#__{vm}_product_download` SET";
			$q .=" `download_max`=`download_max` - 1";
			$q .=" WHERE download_id = '" .$download_id. "'";
			$db->query($q);
			$db->next_record();
		}
		if ($end_date=="0") {
			// Set the Download Expiry Date, so the download can expire after DOWNLOAD_EXPIRE seconds
			$end_date=time('u') + DOWNLOAD_EXPIRE;
			$q ="UPDATE #__{vm}_product_download SET";
			$q .=" end_date=$end_date";
			$q .=" WHERE download_id = '" . $download_id . "'";
			$db->query($q);
			$db->next_record();
		}
		
		if (ereg('Opera(/| )([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = "Opera";
		}
		elseif (ereg('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT'])) {
			$UserBrowser = "IE";
		} else {
			$UserBrowser = '';
		}
		$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';

		// dump anything in the buffer
		while( @ob_end_clean() );

		VmConnector::sendFile( $datei, $mime_type, basename($file_name) );
		
		if( $unlink ) {
			// remove the temporarily downloaded remote file
			@unlink( $datei );
		}
		$GLOBALS['vm_mainframe']->close(true);
			
	}

	/**
	 * Makes a $db query for Orderlisting
	 * Just for seperation, reusing and so on
	 * 
	 */
	function list_order_resultSet($order_status='A', $secure=0 ){
		global $keyword;
		$db = new ps_DB;		
		$listfields = 'cdate,order_total,order_status,order_id,order_currency';
		$countfields = 'count(*) as num_rows';
		$count = "SELECT $countfields FROM #__{vm}_orders ";
		$list = "SELECT $listfields FROM #__{vm}_orders ";
		
		$where = array();
		if (!$GLOBALS['perm']->check("admin")) {
			$where[] = "vendor_id='$vendor_id' ";
		}
		
		if ($order_status != "A") {
			$where[] = " order_status='$order_status' ";
		}
		if ($secure) {
			$where[] = " user_id='" . $_SESSION['auth']["user_id"] . "' ";
		}
		if( !empty( $keyword )) {
			$where[] =  "(order_id LIKE '%".$keyword."%' "
								. "OR order_number LIKE '%".$keyword."%' "
								. "OR order_total LIKE '%".$keyword."%') ";
		}
		
		$q ="";
		if(!empty ($where[0])){
			$q .= 'WHERE';
			$q .= implode(' AND ', $where ). " ORDER BY cdate DESC";
		}
		
		$count .= $q;

		$db->query($count);
		$db->next_record();	
		return $db;
	}
	/**
	 * Shows the list of the orders of a user in the account mainenance section
	 *
	 * @param string $order_status Filter by order status (A=all, C=confirmed, P=pending,...)
	 * @param int $secure Restrict the order list to a specific user id (=1) or not (=0)?
	 */
	function list_order($order_status='A', $secure=0 ) {
		global  $CURRENCY_DISPLAY, $sess, $limit, $limitstart, $keyword, $mm_action_url,$hVendor;

		$vendor_id = $hVendor->getLoggedVendor();

		$auth = $_SESSION['auth'];
		require_once( CLASSPATH .'ps_order_status.php');
		require_once( CLASSPATH .'htmlTools.class.php');
		require_once( CLASSPATH .'pageNavigation.class.php');
		$dbs = new ps_DB;		
		$db = list_order_resultSet($order_status, $secure);
		
		$num_rows = $db->f('num_rows');
		if( $num_rows == 0 ) {
			echo "<span style=\"font-style:italic;\">".JText::_('VM_ACC_NO_ORDERS')."</span>\n";
			return;
		}
		$pageNav = new vmPageNav( $num_rows, $limitstart, $limit );

		$list .= $q .= " LIMIT ".$pageNav->limitstart.", $limit ";
		$db->query( $list );
		$listObj = new listFactory( $pageNav );

		if( $num_rows > 0 ) {
			// print out the search field and a list heading
			$listObj->writeSearchHeader( '', '', 'account', 'index');
		}
		// start the list table
		$listObj->startTable();

		$listObj->writeTableHeader( 3 );

		while ($db->next_record()) {

			$order_status = ps_order_status::getOrderStatusName($db->f("order_status"));

			$listObj->newRow();

			$tmp_cell = "<a href=\"". $sess->url( $mm_action_url."index.php?page=account.order_details&order_id=".$db->f("order_id") )."\">\n";
			$tmp_cell .= "<img src=\"".IMAGEURL."ps_image/goto.png\" height=\"32\" width=\"32\" align=\"middle\" border=\"0\" alt=\"".JText::_('VM_ORDER_LINK')."\" />&nbsp;".JText::_('VM_VIEW')."</a><br />";
			$listObj->addCell( $tmp_cell );

			$tmp_cell = "<strong>".JText::_('VM_ORDER_PRINT_PO_DATE').":</strong> " . vmFormatDate($db->f("cdate"), "%d. %B %Y");
			$tmp_cell .= "<br /><strong>".JText::_('VM_ORDER_PRINT_TOTAL').":</strong> " . $CURRENCY_DISPLAY->getFullValue($db->f("order_total"), '', $db->f('order_currency'));
			$listObj->addCell( $tmp_cell );

			$tmp_cell = "<strong>".JText::_('VM_ORDER_PRINT_PO_STATUS').":</strong> ".$order_status;
			$tmp_cell .= "<br /><strong>".JText::_('VM_ORDER_PRINT_PO_NUMBER').":</strong> " . sprintf("%08d", $db->f("order_id"));
			$listObj->addCell( $tmp_cell );
		}
		$listObj->writeTable();
		$listObj->endTable();
		if( $num_rows > 0 ) {
			$listObj->writeFooter( $keyword, '&Itemid='.$sess->getShopItemid() );
		}

	}

	/**
	 * Validate form values prior to delete
	 *
	 * @param int $order_id
	 * @return boolean
	 */
	function validate_delete($order_id) {
		
		
		$db = new ps_DB;

		if(empty( $order_id )) {
			$GLOBALS['vmLogger']->err(JText::_('VM_ORDER_DELETE_ERR_ID'));
			return False;
		}
		
		// Get the order items and update the stock level
		// to the number before the order was placed
		$q = "SELECT product_id, product_quantity FROM #__{vm}_order_item WHERE order_id='".$db->getEscaped($order_id)."'";
		$db->query( $q );
		$dbu = new ps_DB;
		// Now update each ordered product
		while( $db->next_record() ) {
			$q = "UPDATE #__{vm}_product SET product_in_stock=product_in_stock+".$db->f("product_quantity")
			.",product_sales=product_sales-".$db->f("product_quantity")." WHERE product_id='".$db->f("product_id")."'";
			$dbu->query( $q );
		}

		return True;
	}

	/**
	* Controller for Deleting Records.
	*/
	function delete(&$d) {

		$record_id = $d["order_id"];

		if( is_array( $record_id)) {
			foreach( $record_id as $record) {
				if( !$this->delete_record( $record, $d ))
				return false;
			}
			return true;
		}
		else {
			return $this->delete_record( $record_id, $d );
		}
	}
	/**
	* Deletes one Record.
	*/
	function delete_record( $record_id, &$d ) {
		global $db;
		$record_id = intval( $record_id );
		if ($this->validate_delete($record_id)) {
			$q = "DELETE from #__{vm}_orders where order_id='$record_id'";
			$db->query($q);

			$q = "DELETE from #__{vm}_order_item where order_id='$record_id'";
			$db->query($q);

			$q = "DELETE from #__{vm}_order_payment where order_id='$record_id'";
			$db->query($q);

			$q = "DELETE from #__{vm}_product_download where order_id='$record_id'";
			$db->query($q);
			
			$q = "DELETE from #__{vm}_order_history where order_id='$record_id'";
			$db->query($q);
			
			$q = "DELETE from #__{vm}_order_user_info where order_id='$record_id'";
			$db->query($q);
			
			$q = "DELETE FROM #__{vm}_shipping_label where order_id=$record_id";
			$db->query($q);

			return True;
		}
		else {
			return False;
		}
	}
	/**
	 * Creates the order navigation on the order print page
	 *
	 * @param int $order_id
	 * @return boolean
	 */
	function order_print_navigation( $order_id=1 ) {
		global $sess, $modulename;

		$navi_db =& new ps_DB;

		$navigation = "<div align=\"center\">\n<strong>\n";
		$q = "SELECT order_id FROM #__{vm}_orders WHERE ";
		$q .= "order_id < '$order_id' ORDER BY order_id DESC";
		$navi_db->query($q);
		$navi_db->next_record();
		if ($navi_db->f("order_id")) {
			$url = $_SERVER['PHP_SELF'] . "?page=$modulename.order_print&order_id=";
			$url .= $navi_db->f("order_id");
			$navigation .= "<a class=\"pagenav\" href=\"" . $sess->url($url) . "\">&lt; " .JText::_('ITEM_PREVIOUS')."</a> | ";
		} else
		$navigation .= "<span class=\"pagenav\">&lt; " .JText::_('ITEM_PREVIOUS')." | </span>";

		$q = "SELECT order_id FROM #__{vm}_orders WHERE ";
		$q .= "order_id > '$order_id' ORDER BY order_id";
		$navi_db->query($q);
		$navi_db->next_record();
		if ($navi_db->f("order_id")) {
			$url = $_SERVER['PHP_SELF'] . "?page=$modulename.order_print&order_id=";
			$url .= $navi_db->f("order_id");
			$navigation .= "<a class=\"pagenav\" href=\"" . $sess->url($url) ."\">". JText::_('ITEM_NEXT')."  &gt;</a>";
		} else {
			$navigation .= "<span class=\"pagenav\">".JText::_('ITEM_NEXT')." &gt;</span>";
		}

		$navigation .= "\n<strong>\n</div>\n";

		return $navigation;
	}

}
$ps_order = new ps_order;

?>

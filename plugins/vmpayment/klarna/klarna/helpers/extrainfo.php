<?php
defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

/**
 *
 * a special type of Klarna
 * @author Valérie Isaksen
 * @version $Id:
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-2008 soeren - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
/**
 * Displays the Klarna Invoice number and updates the order number from the
 * store to the Klarna invoice.
 *
 * Paste this into the modules extra info textarea:
 * <?php include(JPATH_SITE . '/components/com_klarna/extrainfo.php'); ?>
 */
$orderId = null;
$invNo = null;
if($page == 'checkout.thankyou') {
    $db->next_record();
    echo (vmCommonHTML::getInfoField($db->f('order_payment_log')));
    $orderId = $db->f('order_id');
    $invNo = $db->f('order_payment_trans_id');
    //require_once('klarna_handler.php');
    KlarnaHandler::updateOrderNo($invNo, $orderId);
} else {
    $db2 = new ps_DB;
    $db2->query('SELECT order_payment_log, order_payment_trans_id, order_id,
                payment_method_id FROM #__{vm}_order_payment WHERE order_id='.
                $_GET['order_id']);
    $db2->next_record();
    $orderId = $db2->f('order_id');
    $invNo = $db2->f('order_payment_trans_id');
    $pm = $db2->f('payment_method_id');
    $db2->query('SELECT payment_class FROM #__{vm}_payment_method WHERE
                 payment_method_id='. $pm);
    $db2->next_record();
    $pClass = $db2->f('payment_class');
    unset($db2);

    $logo = '<img src="'.JURI::base();
    $logo .= '/components/com_klarna/images/images/logo/';
    $country = KlarnaHandler::convertCountry($_SESSION['auth']['country']);
    $lang = KlarnaHandler::getLanguageForCountry($country);

  $kLang = new KlarnaLanguagePack(JPATH_VMKLARNAPLUGIN . '/klarna/language/klarna_language.xml');

    switch ( strtolower($pClass) ) {
        case 'ps_klarna':
            $logo .= $country . '/klarna_invoice.png';
            $method = "";
            break;
        case 'ps_klarna_partpayment':
            $logo .= $country . '/klarna_account.png';
            $method = "";
            break;
        case 'ps_klarna_speccamp':
            $logo .= 'klarna_logo.png';
            $method = $kLang->fetch('MODULE_SPEC_TEXT_TITLE', $lang);
            break;
        default:
            $logo = '';
            $method = '';
            break;
    }
    $logo .= '"/>';
    ?>
<style type="text/css">
#klarna_invno {
    float:left; font-weight: bold; font-size: 13px;
}
#klarna_invno_text {
    width: 50%; float: left;
}
.clear {
    clear: both;
}
.klarna_info {
    float: left; left: -2px; position: relative; text-align: left; width: 99.8%;
}
.klarna_tulip {
    float: left; padding-right: 10px;
}
</style>
    <div class="klarna_info">
        <span class="sectiontableheader klarna_info"><img class="klarna_tulip"
                src="components/com_klarna/images/images/tulip.png" />
                Klarna Order Information</span>
        <span id="klarna_invno_wrapper">
            <span id="klarna_invno_text"><?php echo $kLang->fetch('invoice_number_text', $lang); ?></span>
            <span id="klarna_invno"><?php echo $invNo; ?></span>
        </span>
        <span style="float: right;">
            <a href="http://www.klarna.com/"><?php echo $logo;?></a><br>
            <?php echo $method;?>
        </span>
    </div>

    <div class="clear"></div>
    <?php
}

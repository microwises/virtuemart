<?php

/**
 *
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
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

    $jlang =JFactory::getLanguage();
    $jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, 'en-GB', true);
    $jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, $jlang->getDefault(), true);
    $jlang->load('plg_vmpayment_klarna', JPATH_ADMINISTRATOR, null, true);

	$total = 0;
	$handler = new KlarnaHandler() ;

	if (!class_exists( 'VmModel' )) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'vmmodel.php');
	$model = VmModel::getModel('paymentmethod');
	$payment = $model->getPayment();
	if (!class_exists( 'vmParameters' )) require(JPATH_VM_ADMINISTRATOR.DS.'helpers'.DS.'parameterparser.php');
	$parameters = new vmParameters($payment,  $payment->payment_element , 'plugin' ,'vmpayment');
	$data = $parameters->getParamByName('data');
	if (empty($data->klarna_pc_uri)) {
	    echo '<span class="no_pclasses">'.JText::_('VMPAYMENT_KLARNA_CONF_NO_PC_URI').'</span>';
	}
	$eid_array = KlarnaHandler::getEidSecretArray($data);
	foreach ($eid_array as $country => $eid_data) {
	    try {
		$klarna = new Klarna_virtuemart();
		$klarna->config($eid_data['eid'], $eid_data['secret'], null, null, null, $data->klarna_mode, $data->klarna_pc_type, $data->klarna_pc_uri, ($data->klarna_mode=='klarna_live'));
		$klarna->setCountry($country);
		$pclasses = $klarna->getPClasses();
		$total = $total + count($pclasses);
			if (!count($pclasses) == 0) {
	?>
				<table class="klarna_pclasses">
					<thead class="klarna_pclasses_header">
						<td class="pclass_id"><?php echo JText::_('VMPAYMENT_KLARNA_PCLASS_ID') ?></td>
						<td class="pclass_description"><?php echo JText::_('VMPAYMENT_KLARNA_PCLASS_DESCRIPTION') ?></td>
						<td class="pclass_number"><?php echo JText::_('VMPAYMENT_KLARNA_PCLASS_MONTHS') ?></td>
						<td class="pclass_number"><?php echo JText::_('VMPAYMENT_KLARNA_PCLASS_INTEREST') ?></td>
						<td class="pclass_number"><?php echo JText::_('VMPAYMENT_KLARNA_PCLASS_HANDLING_FEE') ?></td>
						<td class="pclass_number"><?php echo JText::_('VMPAYMENT_KLARNA_PCLASS_START_FEE') ?></td>
						<td class="pclass_number"><?php echo JText::_('VMPAYMENT_KLARNA_PCLASS_MIN_AMOUNT') ?></td>
						<td class="pclass_flag"><?php echo JText::_('VMPAYMENT_KLARNA_PCLASS_COUNTRY') ?></td>
					</thead>
					<tbody class="klarna_pclasses_body">
	<?php
				foreach ($klarna->getPClasses() as $pclass) {
	?>
						<tr>
							<td class="pclass_id"><?php echo $pclass->getId() ?></td>
							<td class="pclass_description"><?php echo $pclass->getDescription() ?></td>
							<td class="pclass_number"><<?php echo $pclass->getMonths() ?></td>
							<td class="pclass_number"><?php echo $pclass->getInterestRate() . "%" ?></td>
							<td class="pclass_number"><?php echo $pclass->getInvoiceFee() . " " . KlarnaHandler::getCurrencySymbolForCountry($data, $klarna->getCountryCode()) ?></td>
							<td class="pclass_number"><?php echo $pclass->getStartFee() . " " . KlarnaHandler::getCurrencySymbolForCountry($data, $klarna->getCountryCode()) ?></td>
							<td class="pclass_number"><?php echo $pclass->getMinAmount() . " " . KlarnaHandler::getCurrencySymbolForCountry($data, $klarna->getCountryCode()) ?></td>
							<td class="pclass_flag"><img src="<?php echo VMKLARNAPLUGINWEBASSETS ?>/images/share/flags/<?php echo $klarna->getLanguageCode() ?>.png" /></td>
						</tr>
	<?php
				} ?>

					</tbody>
				</table>
	<?php	}
		} catch (Exception $e) {
		echo $e;
	    }
	}


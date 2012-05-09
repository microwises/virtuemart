<?php  defined('_JEXEC') or die();
/**
*
* @version $Id: virtuemart.php 5967 2012-04-29 23:17:14Z electrocity $
* @package VirtueMart
* @subpackage Klarna
* @author Valérie Isaksen
* @copyright Copyright (C) 2009-11 by the authors of the VirtueMart Team listed at /administrator/com_virtuemart/copyright.php - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.net
*/
?>
  <fieldset>
        <table cellspacing="0" cellpadding="2" border="0" width="100%">
            <tbody>
                <tr>
                    <td colspan="2">
                        <input class="klarnaPayment" data-stype="<?php echo $viewData['stype'] ?>" id="<?php echo $viewData['klarna_pm']['id'] ?>" type="radio" name="virtuemart_paymentmethod_id" value="<?php echo  $viewData['virtuemart_paymentmethod_id'] ?>" />
						<input  value="<?php echo $viewData['klarna_pm']['id'] ?>" type="hidden" name="klarna_paymentmethod" />
							<label for="<?php echo $viewData['klarna_pm']['id']?>">
                                 <?php echo $viewData['klarna_pm']['module'] ?></label>
                       <br />
                    </td>
                </tr>
                <tr>
                    <td>
                       <?php echo  $viewData['klarna_pm']['fields']['0']['field'] ?>
                    <td>
                </tr>
            </tbody>
        </table>
     </fieldset>
<?php


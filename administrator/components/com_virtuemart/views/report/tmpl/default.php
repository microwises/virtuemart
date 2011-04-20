<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );

/**
*
* @version $Id$
* @package VirtueMart
* @subpackage Report
* @copyright Copyright (C) VirtueMart Team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

AdminMenuHelper::startAdminArea(); 
/* Load some variables */
$search_date = JRequest::getVar('search_date', null); // Changed search by date
$now = getdate();
$nowstring = $now["hours"].":".substr('0'.$now["minutes"], -2).' '.$now["mday"].".".$now["mon"].".".$now["year"];
$search_order = JRequest::getVar('search_order', '>');
$search_type = JRequest::getVar('search_type', 'product');
$order_id = JRequest::getInt('order_id', false);
$format = '%m-%d-%Y';
$rows = count( $this->report );

if( $this->pagination->limit < $rows ){
	if( ($this->pagination->limitstart + $this->pagination->limit) < $rows ) {
		$rows = $this->pagination->limitstart + $this->pagination->limit;
	}
}

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div id="header">
        <h2><?php echo JHTML::_('date', $this->from_period, $format) . ' - ' . JHTML::_('date', $this->until_period, $format); ?></h2>
        <div id="filterbox" style="float: left">
            
            <table>
                <tr>
                    <td align="left" width="100%">
                        <?php echo $this->lists['select_date']; ?>
                        <?php echo JHTML::_('calendar', $this->from_period, 'from_period', 'from-period', '%m-%d-%Y'); ?>
                        <?php echo JHTML::_('calendar', $this->until_period, 'until_period', 'until-period', '%m-%d-%Y'); ?>
                        <button onclick="this.form.submit();"><?php echo JText::_('Go'); ?>
                        </button>
                    </td>
                </tr>
            </table>
        </div>
        <div id="resultscounter" style="float: right;">
            <?php echo $this->pagination->getResultsCounter();?>
        </div>
    </div>
    <br clear="all" />

    <div id="editcell">
        <table class="adminlist">
            <thead>
                <tr>
                    <th>
                        <?php echo JHTML::_('grid.sort','COM_VIRTUEMART_REPORT_BASIC_DATE','order_date',$this->lists['filter_order_Dir'], $this->lists['filter_order']); ?>
                    </th>
                    <th>
                        <?php echo JHTML::_('grid.sort','COM_VIRTUEMART_REPORT_BASIC_ORDERS','order_id',$this->lists['filter_order_Dir'], $this->lists['filter_order']); ?>
                    </th>
                    <th>
                        <?php echo JHTML::_('grid.sort','COM_VIRTUEMART_REPORT_BASIC_TOTAL_ITEMS','order_total_items',$this->lists['filter_order_Dir'],$this->lists['filter_order']); ?>
                    </th>
                    <th>
                        <?php echo JHTML::_('grid.sort','COM_VIRTUEMART_REPORT_BASIC_REVENUE','order_revenue',$this->lists['filter_order_Dir'],$this->lists['filter_order']); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="10">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php 
	    $i = 0;
	    for ($j =0; $j < $rows; ++$j ){
	    	$r = $this->report[$j];
	    	$is = $this->itemsSold[$j];
	    	$s = 0;
	    	?>
                <tr class="row"
                    <?php echo $i;?>">
                    <td align="center">
                        <?php echo $r->order_date;?>
                    </td>
                    <td align="center">
                        <?php echo $r->number_of_orders;?>
                    </td>
                    <td align="center">
                        <?php echo $is->items_sold;?>
                    </td>
                    <td align="right">
                        <?php echo $r->revenue;?>
                    </td>
                </tr>
                <?php
	    	$i = 1-$i; 
	    } 
	    ?>
            </tbody>
        </table>
    </div>

    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="controller" value="report" />
    <input type="hidden" name="view" value="report" />
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_order" value=""<?php echo $this->lists['filter_order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value=""<?php echo $this->lists['filter_order_Dir']; ?>" />
    <input type="hidden" name=""<?php echo JUtility::getToken(); ?>" value="1" />
</form>

<?php AdminMenuHelper::endAdminArea(); ?>
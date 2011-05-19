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
$virtuemart_order_id = JRequest::getInt('virtuemart_order_id', false);
$format = JText::_('DATE_FORMAT_LC');
$rows = count( $this->report );

if( $this->pagination->limit < $rows ){
	if( ($this->pagination->limitstart + $this->pagination->limit) < $rows ) {
		$rows = $this->pagination->limitstart + $this->pagination->limit;
	}
}

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div id="header">
        <h2><?php echo JText::sprintf('COM_VIRTUEMART_REPORT_TITLE', JHTML::_('date', $this->from_period, $format) , JHTML::_('date', $this->until_period, $format)); ?></h2>
        <div id="filterbox" style="float: left">

            <table>
                <tr>
                    <td align="left" width="100%">
                        <?php echo JText::_('COM_VIRTUEMART_REPORT_SET_PERIOD') . $this->lists['select_date']; ?>
                        <?php echo JText::_('COM_VIRTUEMART_REPORT_FROM_PERIOD') . JHTML::_('calendar', $this->from_period, 'from_period', 'from-period', $format); ?>
                        <?php echo JText::_('COM_VIRTUEMART_REPORT_UNTIL_PERIOD') . JHTML::_('calendar', $this->until_period, 'until_period', 'until-period', $format); ?>
                        <button onclick="this.form.submit();"><?php echo JText::_('COM_VIRTUEMART_GO'); ?>
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
                        <?php echo JHTML::_('grid.sort','COM_VIRTUEMART_REPORT_BASIC_ORDERS','virtuemart_order_id',$this->lists['filter_order_Dir'], $this->lists['filter_order']); ?>
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
    <?php echo JHTML::_( 'form.token' ); ?>
</form>

<?php AdminMenuHelper::endAdminArea(); ?>
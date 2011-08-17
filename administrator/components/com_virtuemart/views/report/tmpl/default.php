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

AdminUIHelper::startAdminArea();
/* Load some variables */
$search_date = JRequest::getVar('search_date', null); // Changed search by date
$now = getdate();
$nowstring = $now["hours"].":".substr('0'.$now["minutes"], -2).' '.$now["mday"].".".$now["mon"].".".$now["year"];
$search_order = JRequest::getVar('search_order', '>');
$search_type = JRequest::getVar('search_type', 'product');
$virtuemart_order_id = JRequest::getInt('virtuemart_order_id', false);
$format = JText::_('DATE_FORMAT_LC');
$rows = count( $this->report );
$intervalTitle = JRequest::getVar('intervals', 'REPORT_BASIC_DATE');
if ($intervalTitle =='N') { $intervalTitle = 'REPORT_BASIC_DATE';}

if( $this->pagination->limit < $rows ){
	if( ($this->pagination->limitstart + $this->pagination->limit) < $rows ) {
		$rows = $this->pagination->limitstart + $this->pagination->limit;
	}
}

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
    <div id="header">
        <h2><?php echo JText::sprintf('COM_VIRTUEMART_REPORT_TITLE', JHTML::_('date', $this->from_period, $format) , JHTML::_('date', $this->until_period, $format)); ?></h2>
        <div id="filterbox">

            <table>
                <tr>
                    <td align="left" width="100%">
						<?php echo JText::_('COM_VIRTUEMART_ORDERSTATUS').':'. $this->lists['state_list']; ?>
						<?php echo JText::_('COM_VIRTUEMART_REPORT_INTERVAL').':'. $this->lists['intervals']; ?>
                        <?php echo JText::_('COM_VIRTUEMART_REPORT_SET_PERIOD') . $this->lists['select_date']; ?>
                        
                        <?php echo JText::_('COM_VIRTUEMART_REPORT_FROM_PERIOD') .  VmConfig::jDate($this->from_period, 'from_period'); ?>
                        <?php echo JText::_('COM_VIRTUEMART_REPORT_UNTIL_PERIOD') . VmConfig::jDate($this->until_period, 'until_period'); ?>
                        <button onclick="this.form.period.value='';this.form.submit();"><?php echo JText::_('COM_VIRTUEMART_GO'); ?>
                        </button>
                    </td>
                </tr>
            </table>
        </div>
        <div id="resultscounter">
            <?php echo $this->pagination->getResultsCounter();?>
        </div>
    </div>

    <div id="editcell">
        <table class="adminlist" cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>
                        <?php echo JHTML::_('grid.sort','COM_VIRTUEMART_'.$intervalTitle,'created_on',$this->lists['filter_order_Dir'], $this->lists['filter_order']); ?>
                    </th>
                    <th>
                        <?php echo JHTML::_('grid.sort','COM_VIRTUEMART_REPORT_BASIC_ORDERS','virtuemart_order_id',$this->lists['filter_order_Dir'], $this->lists['filter_order']); ?>
                    </th>
                    <th>
                        <?php echo JHTML::_('grid.sort','COM_VIRTUEMART_REPORT_BASIC_TOTAL_ITEMS','product_quantity',$this->lists['filter_order_Dir'],$this->lists['filter_order']); ?>
                    </th>
                    <th>
                        <?php echo JHTML::_('grid.sort','COM_VIRTUEMART_REPORT_BASIC_REVENUE','revenue',$this->lists['filter_order_Dir'],$this->lists['filter_order']); ?>
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

	    	//$is = $this->itemsSold[$j];
	    	$s = 0;
	    	?>
                <tr class="row"
                    <?php echo $i;?>">
                    <td align="center">
                        <?php echo $r['intervals'];?>
                    </td>
                    <td align="center">
                        <?php echo $r['number_of_orders'];?>
                    </td>
                    <td align="center">
                        <?php echo $r['itemsSold'];?>
                    </td>
                    <td align="right">
                        <?php echo $r['revenue'];?>
                    </td>
                </tr>
                <?php
	    	$i = 1-$i;
	    }
	    ?>
            </tbody>
           <thead>
                <tr>
                    <th  class="right"><?php echo JText::_('COM_VIRTUEMART_TOTAL').' : '; ?></th>
                    <th><?php echo $this->totalReport['number_of_ordersTotal']?></th>
                    <th><?php echo $this->totalReport['itemsSoldTotal'];?></th>
                    <th class="right"><?php echo $this->totalReport['revenueTotal'];?></th>
				</tr>
            </thead>
        </table>
    </div>

    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="controller" value="report" />
    <input type="hidden" name="view" value="report" />
    <input type="hidden" name="task" value="" />
	<input type="hidden" name="task" value="" />
    <input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value=""<?php echo $this->lists['filter_order_Dir']; ?>" />
    <?php echo JHTML::_( 'form.token' ); ?>
</form>

<?php AdminUIHelper::endAdminArea(); ?>
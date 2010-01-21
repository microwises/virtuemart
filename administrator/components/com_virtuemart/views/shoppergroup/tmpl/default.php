<?php 
defined('_JEXEC') or die('Restricted access'); 

AdminMenuHelper::startAdminArea(); 

?>

<form action="index.php" method="post" name="adminForm">
  <div id="editcell">
	  <table class="adminlist">
	    <thead>
		    <tr>
		      <th width="10">
			      <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->shoppergroups); ?>);" />
		      </th>
		      <th>
			      <?php echo JText::_( 'VM_SHOPPER_GROUP_LIST_NAME' ); ?>
		      </th>
		      <th>
			      <?php echo JText::_( 'VM_PRODUCT_FORM_VENDOR' ); ?>
		      </th>
		      <th>
			      <?php echo JText::_( 'VM_SHOPPER_GROUP_LIST_DESCRIPTION' ); ?>
		      </th>
		      <th>
			      <?php echo JText::_( 'VM_SHOPPER_GROUP_LIST_INCLUDE_TAX' ); ?>
		      </th>
		      <th>
            <?php echo JText::_( 'VM_SHOPPER_GROUP_LIST_DISCOUNT' ); ?>
          </th>
		      <th width="20">
			      <?php echo JText::_( 'VM_DEFAULT' ); ?>
		      </th>
		    </tr>
	    </thead><?php
	    
	    $k = 0;
	    for ($i = 0, $n = count( $this->shoppergroups ); $i < $n; $i++) {
		    $row = $this->shoppergroups[$i];

		    $checked = JHTML::_('grid.id', $i, $row->shopper_group_id);
		    $editlink = JROUTE::_('index.php?option=com_virtuemart&controller=shoppergroup&task=edit&cid[]=' . $row->shopper_group_id); ?>
	      
	      <tr class="<?php echo "row$k"; ?>">
			    <td width="10">
				    <?php echo $checked; ?>
			    </td>
			    <td align="left">
			      <a href="<?php echo $editlink; ?>"><?php echo $row->shopper_group_name; ?></a>
			    </td>
			    <td align="left">
            <?php echo $row->vendor_id; ?>
          </td>
			    <td align="left">
				    <?php echo $row->shopper_group_desc; ?>
			    </td>
			    <td>
				    <?php echo $row->show_price_including_tax; ?>
			    </td>
			    <td>
				    <?php echo $row->shopper_group_discount; ?>
			    </td>
			    <td>
				    <?php echo $row->default; ?>
			    </td>
	      </tr><?php
		    $k = 1 - $k;
	    } ?>
	    <tfoot>
		    <tr>
		      <td colspan="10">
			      <?php echo $this->pagination->getListFooter(); ?>
		      </td>
		    </tr>
	    </tfoot>
	  </table>
  </div>

  <input type="hidden" name="option" value="com_virtuemart" />
  <input type="hidden" name="controller" value="shoppergroup" />
  <input type="hidden" name="view" value="shoppergroup" />
  <input type="hidden" name="task" value="" />
  <input type="hidden" name="boxchecked" value="0" />
</form><?php 
AdminMenuHelper::endAdminArea(); ?> 
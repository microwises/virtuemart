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
 * Page navigation support class
 * @package VirtueMart
 */
class vmPageNav {
  /** @var int The record number to start dislpaying from */
  var $limitstart = null;
  /** @var int Number of rows to display per page */
  var $limit = null;
  /** @var int Total number of rows */
  var $total = null;

  function vmPageNav( $total, $limitstart, $limit ) {
    $this->total = intval( $total );
    $this->limitstart = max( $limitstart, 0 );
    $this->limit = max( $limit, 1 );
    if ($this->limit > $this->total) {
      $this->limitstart = 0;
    }
    if (($this->limit-1)*$this->limitstart > $this->total) {
      $this->limitstart -= $this->limitstart % $this->limit;
    }
  }
  /**
   * Writes the html limit # input box
   * Modified by shumisha to handle SEF URLs 2008-06-28
   */
  function writeLimitBox ( $link = '',$category_id = null) {
    echo $this->getLimitBox( $link, $category_id);
  }
  /**
   * Modified by shumisha to handle SEF URLs 2008-06-28
   * @return string The html for the limit # input box
   */
  function getLimitBox ( $link = '', $category_id = null) {
    $limits = array();

    if (!empty($link) && strpos( 'limitstart=', $link) === false) {  // insert limitstart in url if missing // shumisha
      $link .= '&limitstart='.$this->limitstart;
    }
    if($category_id) {
    	$limit_list = ps_product_category::get_category_row_limits($category_id);
    	extract($limit_list);
    } else {
    	$start_record = 5;
    	$record_max = 30;
    	$step_record = 5;
    }
    
    $max = $record_max;// ? $record_max : intval(50 / $start_record) * $start_record;
//    $GLOBALS['vmLogger'] = ('$start_record '.$start_record.' $max '.$max.' $step_record '.$step_record);
    echo('$start_record '.$start_record.' $max '.$max.' $step_record '.$step_record);
    //$start_record 1 $max 1 $step_record 1
    for($i=$start_record; $i <= $max; $i+=$step_record) {
      if (empty( $link)) {
        $limits[$i] = $i;
      } else {
        $limits[vmRoute($link.'&limit='.$i)] = $i;
      }
    }
    
    if (empty( $link)) {
      $limits[$max] = $max;
      $limits[0] = 'All';
    } else {
      $limits[vmRoute($link.'&limit='.$max)] = $max;
      $limits[vmRoute($link.'&limit=0')] = 'All';
    }
	if($this->limit == 1) $this->limit = 0;
    // build the html select list
    if (empty( $link)) {
    $html = ps_html::selectList( 'limit', $this->limit, $limits, 1, '',  'onchange="this.form.submit();"' );
    } else {
      $current = vmRoute($link.'&limit='.$this->limit);
      $html = ps_html::selectList( 'limit', $current, $limits, 1, '',  'onchange="location.href=this.value"' );
    }
    $html .= "\n<input type=\"hidden\" name=\"limitstart\" value=\"$this->limitstart\" />";
    return $html;
  }

  function writePagesCounter() {
    echo $this->getPagesCounter();
  }
  /**
   * @return string The html for the pages counter, eg, Results 1-10 of x
   */
  function getPagesCounter() {
    $html = '';
    $from_result = $this->limitstart+1;
    if ($this->limitstart + $this->limit < $this->total) {
      $to_result = $this->limitstart + $this->limit;
    } else {
      $to_result = $this->total;
    }
    if ($this->total > 0) {
      $html .= JText::_('PN_RESULTS')." $from_result - $to_result ".JText::_('PN_OF')." $this->total";
    } else {
      //$html .= "\nNo records found.";
    }
    return $html;
  }
  /**
   * Writes the html for the pages counter, eg, Results 1-10 of x
   */
  function writePagesLinks($link='') {
    echo $this->getPagesLinks($link);
  }
  /**
   * @return string The html links for pages, eg, previous, next, 1 2 3 ... x
   */
  function getPagesLinks($link='') {
    
     
    $displayed_pages = 10;
    
    //To Prevent Division by Zero
    (int)$limit = (int)$this->limit;
    if($limit==0){
    	$limit = 1;
    }
    $total_pages = ceil( $this->total / $limit );
    $this_page = ceil( ($this->limitstart+1) / $limit );
//    $total_pages = ceil( $this->total / $this->limit );
//    $this_page = ceil( ($this->limitstart+1) / $this->limit );
    $start_loop = (floor(($this_page-1)/$displayed_pages))*$displayed_pages+1;
    if ($start_loop + $displayed_pages - 1 < $total_pages) {
      $stop_loop = $start_loop + $displayed_pages - 1;
    } else {
      $stop_loop = $total_pages;
    }
    $html = '<ul class="pagination">';
    if ($this_page > 1) {
      $page = ($this_page - 2) * $this->limit;
      if( $link != '') {
        $html .= "\n<li><a href=\"".vmRoute($link.'&limit='.$this->limit.'&limitstart=0')."\" class=\"pagenav\" title=\"".JText::_('PN_START')."\">&laquo;&laquo; ".JText::_('PN_START')."</a></li>";
        $html .= "\n<li><a href=\"".vmRoute($link.'&limit='.$this->limit.'&limitstart='.$page)."\" class=\"pagenav\" title=\"".JText::_('PN_PREVIOUS')."\">&laquo; ".JText::_('PN_PREVIOUS')."</a></li>";
      } else {
        $html .= "\n<li><a href=\"#beg\" class=\"pagenav\" title=\"".JText::_('PN_START')."\" onclick=\"javascript: document.adminForm.limitstart.value=0; document.adminForm.submit();return false;\">&laquo;&laquo; ".JText::_('PN_START')."</a></li>";
        $html .= "\n<li><a href=\"#prev\" class=\"pagenav\" title=\"".JText::_('PN_PREVIOUS')."\" onclick=\"javascript: document.adminForm.limitstart.value=$page; document.adminForm.submit();return false;\">&laquo; ".JText::_('PN_PREVIOUS')."</a></li>";
      }
    } else {
      $html .= "\n<li><span class=\"pagenav\">&laquo;&laquo; ".JText::_('PN_START')."</span></li>";
      $html .= "\n<li><span class=\"pagenav\">&laquo; ".JText::_('PN_PREVIOUS')."</span></li>";
    }

    for ($i=$start_loop; $i <= $stop_loop; $i++) {
      $page = ($i - 1) * $this->limit;
      if ($i == $this_page) {
        $html .= "\n<li><span class=\"pagenav\"> $i </span></li>";
      } else {
        if( $link != '') {
          $html .= "\n<li><a href=\"".vmRoute($link.'&limit='.$this->limit.'&limitstart='.$page)."\" class=\"pagenav\"><strong>$i</strong></a></li>";
        } else {
          $html .= "\n<li><a href=\"#$i\" class=\"pagenav\" onclick=\"javascript: document.adminForm.limitstart.value=$page; document.adminForm.submit();return false;\"><strong>$i</strong></a></li>";
        }
      }
    }

    if ($this_page < $total_pages) {
      $page = $this_page * $this->limit;
      $end_page = ($total_pages-1) * $this->limit;
      if( $link != '') {
        $html .= "\n<li><a href=\"".vmRoute($link.'&limit='.$this->limit.'&limitstart='.$page)."\" class=\"pagenav\" title=\"".JText::_('PN_NEXT')."\"> ".JText::_('PN_NEXT')." &raquo;</a></li>";
        $html .= "\n<li><a href=\"".vmRoute($link.'&limit='.$this->limit.'&limitstart='.$end_page)."\" class=\"pagenav\" title=\"".JText::_('PN_END')."\"> ".JText::_('PN_END')." &raquo;&raquo;</a></li>";
      } else {
        $html .= "\n<li><a href=\"#next\" class=\"pagenav\" title=\"".JText::_('PN_NEXT')."\" onclick=\"javascript: document.adminForm.limitstart.value=$page; document.adminForm.submit();return false;\"> ".JText::_('PN_NEXT')." &raquo;</a></li>";
        $html .= "\n<li><a href=\"#end\" class=\"pagenav\" title=\"".JText::_('PN_END')."\" onclick=\"javascript: document.adminForm.limitstart.value=$end_page; document.adminForm.submit();return false;\"> ".JText::_('PN_END')." &raquo;&raquo;</a></li>";
      }
    } else {
      $html .= "\n<li><span class=\"pagenav\">".JText::_('PN_NEXT')." &raquo;</span></li>";
      $html .= "\n<li><span class=\"pagenav\">".JText::_('PN_END')." &raquo;&raquo;</span></li>";
    }
    $html .= "\n</ul>";
    return $html;
  }

  function getListFooter() {
    $html = '<table class="adminlist">';
    if( $this->total > $this->limit || $this->limitstart > 0) {

      $html .= '<tr><th colspan="3">';

      $html .= $this->getPagesLinks();
      $html .= '</th></tr>';
    }
    $html .= '<tr><td nowrap="nowrap" width="48%" align="right">'.JText::_('PN_DISPLAY_NR').'</td>';
    $html .= '<td>' .$this->getLimitBox() . '</td>';
    $html .= '<td nowrap="nowrap" width="48%" align="left">' . $this->getPagesCounter() . '</td>';
    $html .= '</tr></table>';
  		return $html;
  }
  /**
   * @param int The row index
   * @return int
   */
  function rowNumber( $i ) {
    return $i + 1 + $this->limitstart;
  }
  /**
   * @param int The row index
   * @param string The task to fire
   * @param string The alt text for the icon
   * @return string
   */
  function orderUpIcon( $i, $condition=true, $task='orderup', $alt='', $page, $func ) {
    global $mosConfig_live_site;
    if( $alt == '') {
      $alt = JText::_('CMN_ORDER_UP');
    }
    if (($i > 0 || ($i+$this->limitstart > 0)) && $condition) {
      return '<a href="#reorder" onclick="return vm_listItemTask(\'cb'.$i.'\',\''.$task.'\', \'adminForm\', \''.$page.'\', \''.$func.'\')" title="'.$alt.'">
				<img src="'.$mosConfig_live_site.'/administrator/images/uparrow.png" width="12" height="12" border="0" alt="'.$alt.'" />
			</a>';
  		} else {
  		  return '&nbsp;';
  		}
  }
  /**
   * @param int The row index
   * @param int The number of items in the list
   * @param string The task to fire
   * @param string The alt text for the icon
   * @return string
   */
  function orderDownIcon( $i, $n, $condition=true, $task='orderdown', $alt='', $page, $func ) {
    global $mosConfig_live_site;
    if( $alt == '') {
      $alt = JText::_('CMN_ORDER_DOWN');
    }
    if (($i < $n-1 || $i+$this->limitstart < $this->total-1) && $condition) {
      return '<a href="#reorder" onclick="return vm_listItemTask(\'cb'.$i.'\',\''.$task.'\', \'adminForm\', \''.$page.'\', \''.$func.'\')" title="'.$alt.'">
				<img src="'.$mosConfig_live_site.'/administrator/images/downarrow.png" width="12" height="12" border="0" alt="'.$alt.'" />
			</a>';
  		} else {
  		  return '&nbsp;';
  		}
  }
}
?>

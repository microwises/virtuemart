<?php

/**
 *
 * Modify user form view, User info
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
 * @version $Id$
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');



$typefields = array('corefield', 'billto');
$corefields = VirtueMartModelUserfields::getCoreFields();
foreach ($typefields as $typefield) {
    $_k = 0;
    $_set = false;
    $_table = false;
    $_hiddenFields = '';

//             for ($_i = 0, $_n = count($this->userFields['fields']); $_i < $_n; $_i++) {
    for ($_i = 0, $_n = count($this->userFields['fields']); $_i < $_n; $_i++) {
	// Do this at the start of the loop, since we're using 'continue' below!
	if ($_i == 0) {
	    $_field = current($this->userFields['fields']);
	} else {
	    $_field = next($this->userFields['fields']);
	}

	if ($_field['hidden'] == true) {
	    $_hiddenFields .= $_field['formcode'] . "\n";
	    continue;
	}
	if ($_field['type'] == 'delimiter') {
	    if ($_set) {
		// We're in Fieldset. Close this one and start a new
		if ($_table) {
		    echo '	</table>' . "\n";
		    $_table = false;
		}
		echo '</fieldset>' . "\n";
	    }
	    $_set = true;
	    echo '<fieldset>' . "\n";
	    echo '	<legend>' . "\n";
	    echo '		' . $_field['title'];
	    echo '	</legend>' . "\n";
	    continue;
	}



	if (($typefield == 'corefield' && (in_array($_field['name'], $corefields) && $_field['name'] != 'email' && $_field['name'] != 'agreed') )
		or ($typefield == 'billto' && !(in_array($_field['name'], $corefields) && $_field['name'] != 'email' && $_field['name'] != 'agreed') )) {
	    if (!$_table) {
		// A table hasn't been opened as well. We need one here,
		if ( $typefield == 'corefield') {
		    echo '<span class="userfields_info">' . $this->corefield_title . '</span>';
		} else {
		    echo '<span class="userfields_info">' . $this->vmfield_title . '</span>';
		}
		echo '	<table class="adminform user-details">' . "\n";
		$_table = true;
	    }
	    echo '		<tr>' . "\n";
	    echo '			<td class="key">' . "\n";
	    echo '				<label class="' . $_field['name'] . '" for="' . $_field['name'] . '_field">' . "\n";
	    echo '					' . $_field['title'] . ($_field['required'] ? ' *' : '') . "\n";
	    echo '				</label>' . "\n";
	    echo '			</td>' . "\n";
	    echo '			<td>' . "\n";
	    echo '				' . $_field['formcode'] . "\n";
	    echo '			</td>' . "\n";
	    echo '		</tr>' . "\n";
	}
    }

    if ($_table) {
	echo '	</table>' . "\n";
    }
    if ($_set) {
	echo '</fieldset>' . "\n";
    }
    $_k = 0;
    $_set = false;
    $_table = false;
    $_hiddenFields = '';
    reset($this->userFields['fields']);
}

echo $_hiddenFields;
?>

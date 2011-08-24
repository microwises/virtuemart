<?php
if( !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' );
/**
*
* @version $Id$
* @package VirtueMart
* @subpackage core
* @copyright Copyright (C) 2005-2011 VirtueMart Team - All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/
if( class_exists( 'vmVersion' ) ) {

	$shortversion = vmVersion::$PRODUCT . " " . vmVersion::$RELEASE . " " . vmVersion::$DEV_STATUS. " ";

	$myVersion = $shortversion . " [".vmVersion::$CODENAME ."] <br />" . vmVersion::$RELDATE . " "
	. vmVersion::$RELTIME . " " . vmVersion::$RELTZ;

	return;
}

if( !class_exists( 'vmVersion' ) ) {
/** Version information */
class vmVersion {
	/** @var string Product */
	static $PRODUCT = 'VirtueMart';
	/** @var int Release Number */
	static $RELEASE = '2.0.0-RC-2E';
	/** @var string Development Status */
	static $DEV_STATUS = 'release candidate';
	/** @var string Codename */
	static $CODENAME = 'BeJooKu';
	/** @var string Date */
	static $RELDATE = 'August 24, 2011';
	/** @var string Time */
	static $RELTIME = '20:30';
	/** @var string Timezone */
	static $RELTZ = 'GMT';
	/** @var string Revision */
	static $REVISION = '$Revision: 3940 $';
	/** @var string Copyright Text */
	static $COPYRIGHT = 'Copyright (C) 2005-2011 VirtueMart Development Team - All rights reserved.';
	/** @var string URL */
	static $URL = '<a href="http://virtuemart.net">VirtueMart</a> is a Free Component for Joomla! released under the GNU/GPL2 and higher License.';
}

$shortversion = vmVersion::$PRODUCT . " " . vmVersion::$RELEASE . " " . vmVersion::$DEV_STATUS. " ";

$myVersion = $shortversion . " [".vmVersion::$CODENAME ."] <br />" . vmVersion::$RELDATE . " "
	. vmVersion::$RELTIME . " " . vmVersion::$RELTZ;

}

// pure php no closing tag
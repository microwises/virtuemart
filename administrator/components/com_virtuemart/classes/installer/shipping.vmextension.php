<?php
if (! defined ( '_VALID_MOS' ) && ! defined ( '_JEXEC' ))
	die ( 'Direct Access to ' . basename ( __FILE__ ) . ' is not allowed.' );

/**
*
* @version $Id: shipping.class.php 27/09/2008
* @package VirtueMart
* @subpackage classes
* @copyright Copyright 2008 HoaNT-Vsmarttech for this class
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/ 

class vmInstallerShipping {
	function getTitle() {
		return 'Shipping Modules';
	}

	
	/**
	 * Method to detect the extension type from a package directory
	 *
	 * @static
	 * @param array $package all about the installation
	 * @return 
	 * @since 1.2.0
	 */
	function install($package) {
		global $vmLogger;
		
		//print_r($package);
		$files = JFolder::files ( $package ['dir'], '\.xml$', 1, true );
		foreach ( $files as $file ) {
			//Get the information of the XML 
			$info = vmInstaller::getInfo ( $file );
			
			//Get the data of the installation
			$file_install = vmInstaller::getFile ( $file );
			if( empty($file_install)) {
				return false;
			}
			//validate the Shipping method
			$valid = $this->is_valid_installpackage( $info, $file_install ['file'], $file );
			
			if (! $valid) {
				return false;
			}
			if( !empty($file_install ['languages'])) {
				$lang_path = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_virtuemart". DS . 'languages' . DS . 'plg_shipping_'.$info ['element'];
				$check_file = vmInstaller::install_file ( $package ['dir'], $file_install ['languages'], $lang_path );
			}
			$path = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_virtuemart". DS . 'plugins' . DS . 'shipping';
			$check_file = vmInstaller::install_file ( $package ['dir'], $file_install ['file'], $path );
			
			if ($check_file === 'exists') {
				return false;
			} else {
				$check_query = vmInstaller::install_query ( $file_install ['query'] );
				if ($check_file && $check_query) {
					$src = $package ['dir'] . DS . JFile::getName ( $file );
					$path = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_virtuemart" . DS . "plugins" . DS . "shipping" . DS . $info ['element'] . ".xml";
					
					JFile::copy ( $src, $path );
					$vmLogger->info( JText::_('SUCCESSFUL_INSTALLATION'));
					
					$this->insert_plugin($info, 'shipping');
					
				} else {
					$vmLogger->info( JText::_('FAILED_INSTALLATION'));
					vmInstaller::rollback ( $file_install ['file'], $file_install ['query'], $path );
				}
			}
		}
		return true;
	}
	
	/**
	 * Method show list of shipping method
	 *
	 * @static
	 * @param 
	 * @return list of shipping method
	 * @since 1.2.0
	 */
	function get_extension_list()
	{
		global $vmLogger;
		$files = vmReadDirectory ( ADMINPATH . "plugins/shipping/", ".php$", true, true );
		
		$array = array ( );
		foreach ( $files as $file ) {
			$file_info = pathinfo ( $file );
			$filename = $file_info ['basename'];
			if (stristr ( $filename, '.cfg' )) {
				continue;
			}
			if( !file_exists(ADMINPATH . "plugins/shipping/".basename ( $filename, '.php' ).'.xml')) continue;
			$array [basename ( $filename, '.php' )] = basename ( $filename, '.php' );
		}
		return $array;
	}
}

?>
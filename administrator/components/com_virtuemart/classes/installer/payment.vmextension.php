<?php
if (! defined ( '_VALID_MOS' ) && ! defined ( '_JEXEC' ))
	die ( 'Direct Access to ' . basename ( __FILE__ ) . ' is not allowed.' );

/**
*
* @version $Id: payment.class.php 27/09/2008
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

class vmInstallerPayment {
	function getTitle() {
		return 'Payment Modules';
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
		
		//print_r($package);exit;
		$files = JFolder::files ( $package ['dir'], '\.xml$', 1, true );
		foreach ( $files as $file ) {
			//Get the information of the XML 
			$info = vmInstaller::getInfo ( $file );
			
			//Get the data of the installation
			$file_install = vmInstaller::getFile ( $file );
			
			//valid the Payment method
			$valid = $this->is_valid_installpackage( $info, $file_install ['file'], $file );
			
			if (! $valid) {
				return false;
			}
			if( !empty($file_install ['languages'])) {
				$lang_path = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_virtuemart". DS . 'languages' . DS . 'plg_payment_'.$info ['element'];
				$check_file = vmInstaller::install_file ( $package ['dir'], $file_install ['languages'], $lang_path );
			}
			$path = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_virtuemart" . DS . 'plugins'. DS . 'payment';
			$check_file = vmInstaller::install_file ( $package ['dir'], $file_install ['file'], $path );
			
			if ($check_file === 'exists') {
				$vmLogger->err( 'One or more files already exist in the destination directory.');
				return false;
			} else {
				$check_query = vmInstaller::install_query ( $file_install ['query'] );
				
				if ($check_file && $check_query) {
					$src = $package ['dir'] . DS . JFile::getName ( $file );
					$path = JPATH_ADMINISTRATOR . DS . "components" . DS . "com_virtuemart" . DS . "plugins" . DS . "payment"  . DS . $info ['name'] . ".xml";
					
					JFile::copy ( $src, $path );

					$vmLogger->info( JText::_('SUCCESSFUL_INSTALL'));
					$this->insert_plugin($info, 'payment');
				} else {
					vmInstaller::rollback ( $file_install ['file'], $file_install ['query'], $path );
					JFile::delete($package['packagefile']);
					vmRemoveDirectoryR($package['extractdir']);
				}
			}
		}
		return true;
	}
	
	function insert_plugin($info, $type='payment') {
		
		$db = new ps_db;
		$fields = array('name' => $info['name'],
								'element' => $info['element'],
								'folder' => $type,
								'ordering' => '1',
								'published' => '0',
								'type' => $info['payment_type'],
								'is_creditcard' => ($info['payment_type']=='C' || $info['payment_type']=='A') ? '1' : '0',
								'vendor_id' => $_SESSION['ps_vendor_id'],
								'shopper_group_id' => $_SESSION['auth']['default_shopper_group']			
		);
		$db->buildQuery('INSERT', '#__{vm}_payment_method', $fields );
	}
	/**
	 * Deletes a plugin record
	 *
	 * @param string $plugin
	 * @param string $type
	 */
	function delete_plugin( $plugin, $type='' ) {
		$db = new ps_db;
		$query = 'DELETE FROM `#__{vm}_payment_method` 
						WHERE element=\''.$db->getEscaped($plugin).'\'';
		return $db->query( $query ) !== false;
		
	}
	/**
	 * Method show payment method
	 *
	 * @static
	 * @param 
	 * @return List payment method
	 * @since 1.2.0
	 */
	function get_extension_list() {

		$files = vmReadDirectory ( ADMINPATH . "plugins/payment/", ".php$", true, true );
		$array = array ( );
		foreach ( $files as $file ) {
			$file_info = pathinfo ( $file );
			$filename = $file_info ['basename'];
			if (stristr ( $filename, '.cfg' )) {
				continue;
			}
			if( $filename == 'payment.php' ) continue;
			$array [basename ( $filename, '.php' )] = basename ( $filename, '.php' );
		}
		return $array;
	}
}

?>
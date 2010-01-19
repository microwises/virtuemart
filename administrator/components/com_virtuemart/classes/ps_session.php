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
 * This class handles the session initialization, restart
 * and the re-init of a session after redirection to a Shared SSL domain
 *
 */
class ps_session {

	var $component_name;
	var $_session_name = 'virtuemart';
	/**
     * Initialize the Session environment for VirtueMart
     *
     */
	function ps_session() {
		
		$this->component_name = 'option='.VM_COMPONENT_NAME;
		
		$this->initSession();
	}
	/**
     * Initiate the Session
     *
     */
	function initSession() {
		global $vmLogger, $mainframe, $mosConfig_absolute_path;
		
		// We only care for the session if it is not started!
		if( empty( $_SESSION ) || session_id() == '') {
			
			if(ini_get('session.session_save_handler') == 'files') {
				// Check if the session_save_path is writable
				$this->checkSessionSavePath();
			}
			session_name( $this->_session_name );
			
			if( @$_REQUEST['option'] == 'com_virtuemart' ) {
				ob_start();// Fix for the issue that printed the shop contents BEFORE the page begin
			}
			@session_start();
			
			if( !empty($_SESSION) && !empty($_COOKIE[$this->_session_name])) {
				$vmLogger->debug( 'A Session called '.$this->_session_name.' (ID: '.session_id().') was successfully started!' );
			}
			else {
			}
		}
		else {
			if( empty( $_COOKIE['virtuemart']) ) {
				$_COOKIE['virtuemart'] = $this->getSessionId();			
				if( USE_AS_CATALOGUE == '' ) {
					$vmLogger->debug( 'A Cookie had to be set to keep the session (there was none - does your Browser keep the Cookie?) although a Session already has been started! If you see this message on each page load, your browser doesn\'t accept Cookies from this site.' );
				}
			}
			$vmLogger->debug( 'Using existing Session '.session_name().', ID: '.session_id().'.');
		}
		// Cookie Check
		// Introduced to check if the user-agent accepts cookies
		if( @$_REQUEST['option'] == 'com_virtuemart' && empty($_GET['martID']) 
			&& USE_AS_CATALOGUE != '1' && VM_ENABLE_COOKIE_CHECK == '1' && !vmIsAdminMode() ) {
			$this->doCookieCheck();
		}
	}
	/**
	 * Checks if the user-agent accepts cookies
	 * @since VirtueMart 1.0.7
	 * @author soeren
	 */
	function doCookieCheck() {
		global $mm_action_url;
		
		$doCheck = JRequest::getVar(  'vmcchk', 0 );
		$isOK = vmGet( $_SESSION, 'VMCHECK' );
		
		if( $doCheck && $isOK != 'OK' ) {
			$GLOBALS['vmLogger']->info( JText::_('VM_SESSION_COOKIES_NOT_ACCEPTED_TIP',false) );
		}
		elseif( empty( $isOK )) {
			$_SESSION['VMCHECK'] = 'OK';
			$query_string = vmGet($_SERVER,'QUERY_STRING');
			if( !empty($query_string) && empty( $_POST )) {
				vmRedirect( $this->url( $mm_action_url . 'index.php?' .$query_string .'&vmcchk=1', true, false ));
			}
		}
	}
		
	/**
	 * Returns the Joomla/Mambo Session ID
	 * @static 
	 */
	function getSessionId() {
		global $mainframe;
		// Joomla >= 1.0.8
		if( is_callable( array( 'mosMainframe', 'sessionCookieName'))) {			
			// Session Cookie `name`
			$sessionCookieName 	= mosMainFrame::sessionCookieName();
			// Get Session Cookie `value`
			$sessionCookie 		= vmGet( $_COOKIE, $sessionCookieName, null );
			// Session ID / `value`
			return mosMainFrame::sessionCookieValue( $sessionCookie );
			
		}
		// Mambo 4.6
		elseif( is_callable( array('mosSession', 'getCurrent' ))) {
			$session =& mosSession::getCurrent();
			return $session->session_id;
		}
		// Mambo <= 4.5.2.3 and Joomla <= 1.0.7
		elseif( !empty( $mainframe->_session->session_id )) {
			// Set the sessioncookie if its missing
			// this is needed for joomla sites only
			return $mainframe->_session->session_id;
		}
		else {
			return session_id();
		}
	}
	/**
	 * This function returns a base64_encoded string:
	 * VMsessionId|JsessionID
	 *
	 */
	function getMartId() {
		global $vmuser, $mosConfig_secret;
		
		// Get the Joomla! / Mambo session ID
		$sessionId = ps_session::getSessionId();
		
		$userNameSeed = '';
		if( $vmuser->id ) {
			$userNameSeed = '|'.md5( $vmuser->username . $vmuser->password . $mosConfig_secret );
			if( is_callable(array('mosMainFrame', 'remCookieName_User'))) {
				if( !empty( $GLOBALS['real_mosConfig_live_site'] ) && empty( $_REQUEST['real_mosConfig_live_site'])) {
					$GLOBALS['mosConfig_live_site'] = $GLOBALS['real_mosConfig_live_site'];
				}
				$userNameSeed .= '|' . vmGet( $_COOKIE, mosMainFrame::remCookieName_User(), '' );
			}
		}
		
		$martID = base64_encode( vmCreateHash($_COOKIE[$this->_session_name] . $sessionId) . $userNameSeed );
		return $martID;
	}
	/**
	 * Saves the session contents to a file (so they the session can be continued later on) and does a redirect
	 *
	 * @param boolean $toSecure Redirect to a https or http domain?
	 */
	function saveSessionAndRedirect( $toSecure = true ) {
		$martID = $this->getMartId();
				
		$sessionFile = IMAGEPATH. md5( $martID ).'.sess';
		$session_contents = session_encode();
		
		if( file_exists( ADMINPATH.'install.copy.php')) {
			require_once( ADMINPATH.'install.copy.php');
		}
		
		file_put_contents( $sessionFile, $session_contents );
		
		$url = $toSecure ? SECUREURL : URL;
		
		// Redirect and send the Cookie Values within the variable martID
		vmRedirect( $this->url( $url . basename($_SERVER['PHP_SELF']).'?'.vmGet($_SERVER,'QUERY_STRING')."&martID=$martID&redirected=1", true, false, true ) );
	}
	/**
	 * It does what the name says. It starts a session again (with a certain ID when a $sid is given)
	 *
	 * @param string $sid
	 */
	function restartSession( $sid = '') {
		
		// Save the session data and close the session
		session_write_close();
		
		// Prepare the new session
		if( $sid != '' ) {
			session_id( $sid );
		}
		session_name( $this->_session_name );
		// Start the new Session.
		session_start();
		
	}
	
	function emptySession() {
		global $mainframe;
		$_SESSION = array();
		$_COOKIE[$this->_session_name] = md5( $this->getSessionId() );
	}
	/**
     * This is a solution for  the Shared SSL problem
     * We have to copy some cookies from the Main Mambo site domain into
     * the shared SSL domain (only when necessary!)
	 *
	 * The function is called on each page load.
	 */
	function prepare_SSL_Session() {
		global $mainframe, $my, $database, $mosConfig_secret, $page, $VM_MODULES_FORCE_HTTPS;
//		if( vmIsAdminMode() && vmIsJoomla('1.0')) {
//			return;
//		}
		$ssl_redirect = vmGet( $_GET, "ssl_redirect", 0 );
		$redirected = vmGet( $_GET, "redirected", 0 );
		$martID = vmGet( $_GET, 'martID', '' );
		$ssl_domain = "";
		
		if (!empty( $VM_MODULES_FORCE_HTTPS )) {
			$pagearr = explode( '.', $page );
			$module = $pagearr[0];
			// When NOT in https mode, but the called page is part of a shop module that is
			// forced to use https, we prepare the redirection to https here
			if( array_search( $module, $VM_MODULES_FORCE_HTTPS ) !== false 
				&& !vmIsHttpsMode()
				&& $this->check_Shared_SSL( $ssl_domain ) 
				) {
					
				$ssl_redirect = 1;
			}
		}
		// Generally redirect to HTTP (from HTTPS) when it is not necessary? (speed up the pageload)
		if( VM_GENERALLY_PREVENT_HTTPS == '1' 
			&& vmIsHttpsMode() && $redirected != 1
			&& $ssl_redirect == 0 && !vmIsAdminMode()
			&& URL != SECUREURL
			&& @$_REQUEST['option']=='com_virtuemart') {
				
			$pagearr = explode( '.', $page );
			$module = $pagearr[0];
			
			// When it is not necessary to stay in https mode, we leave it here
			if( array_search( $module, $VM_MODULES_FORCE_HTTPS ) === false ) {
				if( $this->check_Shared_SSL($ssl_domain)) {
					$this->saveSessionAndRedirect( false );
				}
				$query_string = vmGet($_SERVER,'QUERY_STRING');
				if( !empty($query_string) && empty( $_POST )) {
					vmRedirect( $this->url( URL.basename( $_SERVER['PHP_SELF']).'?'.vmGet($_SERVER,'QUERY_STRING').'&redirected=1', true, false, true ));
				}
			}
		}
		
		/**
        * This is the first part of the Function:
        * We check if the function must be called at all
        * Usually this is only called once: Before we go to the checkout.
        * The variable ssl_redirect=1 is appended to the URL, just for this function knows
        * is must be active! This has nothing to do with SSL / Shared SSL or whatever
        */
		if( $ssl_redirect == 1 ) {
			
			$_SERVER['QUERY_STRING'] = str_replace('&ssl_redirect=1', '', vmGet($_SERVER, 'QUERY_STRING' ) );
			// check_Shared_SSL compares the normal http domain name
			// and the https Domain Name. If both do not match, we move on
			// else we leave this function.
			if( $this->check_Shared_SSL( $ssl_domain ) && !vmIsHttpsMode() && $redirected == 0) {
				
				$this->saveSessionAndRedirect( true );
			}
			// do nothing but redirect
			elseif( !vmIsHttpsMode() && $redirected == 0 ) {
				vmRedirect( $this->url(SECUREURL . basename($_SERVER['PHP_SELF'])."?".vmGet($_SERVER,'QUERY_STRING').'&redirected=1', true, false, true ) );
			}
		}
		/**
        * This is part two of the function
        * If the redirect (see 4/5 lines above) was successful
        * and the Store uses Shared SSL, we have the variable martID
        * So let's copy the Session contents ton the new domain and start the session again
        * othwerwise: do nothing.
        */
		if( !empty( $martID ) ) {
			
			if( $this->check_Shared_SSL( $ssl_domain ) ) {
	
				// We now need to copy the Session Data to the SSL Domain
				if( $martID ) {					
					
					require_once( ADMINPATH.'install.copy.php');
					
					$sessionFile = IMAGEPATH. md5( $martID ).'.sess';
					
					// Read the contents of the session file
					$session_data = file_get_contents( $sessionFile );
					
					// Delete it for security and disk space reasons
					unlink( $sessionFile );
					
					// Read the session data into $_SESSION
					// From now on, we can use all the data in $_SESSION
					session_decode( $session_data );
					
					$check = base64_decode( $martID );
					$checkValArr = explode( "|", $check );
					
					if( defined('_JEXEC') ) {
						//TODO
					}
					elseif( class_exists('mambocore')) {
						//TODO
					}
					elseif( $GLOBALS['_VERSION']->RELEASE == '1.0' && (int)$GLOBALS['_VERSION']->DEV_LEVEL >= 13) {
						if( !empty( $GLOBALS['real_mosConfig_live_site'] ) && empty( $_REQUEST['real_mosConfig_live_site'])) {
							$GLOBALS['mosConfig_live_site'] = $GLOBALS['real_mosConfig_live_site'];
						}
						if( !empty( $checkValArr[2] )) {
							// Joomla! >= 1.0.13 can be cheated to log in a user who has previsously logged in and checked the "Remember me" box
							setcookie( mosmainframe::remCookieName_User(), $checkValArr[2], false, '/' );
							// there's no need to call "$mainframe->login"
						}
					} else {
						// Check if the user was logged in in the http domain
						// and is not yet logged in at the Shared SSL domain
						if( isset( $checkValArr[1] ) && !$my->id ) {
							// user should expect to be logged in,
							// we can use the values from $_SESSION['auth'] now
							$username = $database->getEscaped( trim( $_SESSION['auth']['user_name'] ) );
							if( !empty( $username )) {
								$database->setQuery('SELECT username, password FROM `#__users` WHERE `username` = \''.$username.'\';');
								$database->loadObject( $user );
								if( is_object( $user )) {
									// a last security check using the transmitted md5 hash and the rebuilt hash
									$check = md5( $user->username . $user->password . $mosConfig_secret );
									if( $check === $checkValArr[1] ) {
										// Log the user in with his username
										$mainframe->login( $user->username, $user->password );
									}
								}
								
							}
						}
					}
									
					
					session_write_close();
					
					// Prevent the martID from being displayed in the URL
					if( !empty( $_GET['martID'] )) {
						$query_string = substr_replace( vmGet($_SERVER,'QUERY_STRING'), '', strpos( vmGet($_SERVER,'QUERY_STRING'), '&martID'));
						$url = vmIsHttpsMode() ? SECUREURL : URL;
						vmRedirect( $this->url( $url . "index.php?$query_string&cartReset=N&redirected=1", true, false, true) );
					}
	
				}
	
			}
		}
	}
	/**
	 * This function compares the store URL with the SECUREURL
	 * and returns the result
	 *
	 * @param string $ssl_domain The SSL domain (empty string to be filled here)
	 * @return boolean True when we have to do a SSL redirect (for Shared SSL)
	 */
	function check_Shared_SSL( &$ssl_domain ) {
		
		if( URL == SECUREURL ) {
			$ssl_domain = str_replace("http://", "", URL );
			$ssl_redirect = false;
			return $ssl_redirect;
		}
		
		// Extract the Domain Names without the Protocol
		$domain = str_replace("http://", "", URL );
		$ssl_domain = str_replace("https://", "", SECUREURL );
		// If SSL and normal Domain do not match,
		// we assume that you use Shared SSL

		if( $ssl_domain != $domain ) {
			$ssl_redirect = true;
		}
		else {
			$ssl_redirect = false;
		}

		return $ssl_redirect;
	}
	/**
	 * Correct the session save path if necessary
	 * or generate an error if the save path can't be corrected
	 *
	 * @return mixed
	 */
	function checkSessionSavePath() {
		global $mosConfig_absolute_path, $vmLogger;
		
		if( !@is_writable( session_save_path()) ) {
			// If the session save path is not writable this can have different
			// reasons. One reason is that the open_basedir directive is set, but
			// doesn't include the session_save_path
			$open_basedir = @ini_get('open_basedir'); // Get the list of allowed directories
			if( !empty($open_basedir)) {
				switch( substr( strtoupper( PHP_OS ), 0, 3 ) ) {
					case "WIN":
						$basedirs = explode(';', $open_basedir );
						break;
					case "MAC": // fallthrough
					case "DAR": // Does PHP_OS return 'Macintosh' or 'Darwin' ?
					default: // change nothing
						$basedirs = explode(':', $open_basedir );
						break;
					break;
				}
				$session_save_path_is_allowed_directory = false;
				foreach ( $basedirs as $basedir ) {
					$basedir_strlen = strlen( $basedir );
					// If the session save path is a subdirectory of a directory allowed by open_basedir
					// we need to do further investigation
					if( strtolower( substr( session_save_path(), 0, $basedir_strlen )) == $basedir ) {
						$session_save_path_is_allowed_directory = true;
					}
				}
				if( !$session_save_path_is_allowed_directory) {
					// PHP Sessions can be stored in a session save path which is not
					// allowed through open_basedir!
					return true;
				}
			}
			$try_these_paths = array( 'Cache Path' => $mosConfig_absolute_path. '/cache',
										'Media Directory' => $mosConfig_absolute_path.'/media',
										'Shop Image Directory' => IMAGEPATH );
			foreach( $try_these_paths as $name => $session_save_path ) {
				if( @is_writable( $session_save_path )) {
					$vmLogger->debug( sprintf( JText::_('VM_SESSION_SAVEPATH_UNWRITABLE_TMPFIX',false), session_save_path(), $name));
					session_save_path( $session_save_path );
					break;
				}
			}
		}
		// If the path is STILL not writable, generate an error
		if( !@is_writable( session_save_path()) ) {
			$vmLogger->err( JText::_('VM_SESSION_SAVEPATH_UNWRITABLE',false) );
		}
	}
	/**
     * Gets the Itemid for the com_virtuemart Component
     * and stores it in a global Variable
     *
     * @return int Itemid
     */
	function getShopItemid() {

		if( empty( $_REQUEST['shopItemid'] )) {
			$db = new ps_DB;
			$db->query( "SELECT id FROM #__menu WHERE link='index.php?option=com_virtuemart' AND published=1");
			if( $db->next_record() ) {
				$_REQUEST['shopItemid'] = $db->f("id");
			}
			else {
				if( !empty( $_REQUEST['Itemid'] )) {
					$_REQUEST['shopItemid'] = intval( $_REQUEST['Itemid'] );
				}
				else {
					$_REQUEST['shopItemid'] = 1;
				}
			}
		}

		return intval($_REQUEST['shopItemid']);

	}
	
	/**
	 * Prints a reformatted URL
	 *
	 * @param string $text
	 */
	function purl($text, $createAbsoluteURI=false, $encodeAmpersands=true, $ignoreSEF=false) {		
		echo $this->url( $text, $createAbsoluteURI, $encodeAmpersands, $ignoreSEF );		
	}
	
	/**
	 * This reformats an URL, appends "option=com_virtuemart" and "Itemid=XX"
	 * where XX is the Id of an entry in the table mos_menu with "link: option=com_virtuemart"
	 * It also calls sefRelToAbs to apply SEF formatting
	 * 
	 * @param string $text THE URL
	 * @param boolean False: Create a URI like /joomla/index.php?....; True: Create a URI like http://www.domain.com/index.php?....
	 * @return string The reformatted URL
	 */
	function url($text, $createAbsoluteURI=false, $encodeAmpersands=true, $ignoreSEF=false ) {
		global $mm_action_url, $page, $mainframe;
		
		if( !defined( '_VM_IS_BACKEND' )) {
			
			// Strip the parameters from the $text variable and parse to a temporary array
			$tmp_text=str_replace('amp;','',substr($text,strpos($text,'?')));
			if(substr($tmp_text,0,1)=='?') $tmp_text=substr($tmp_text,1);
			//if statement added by Max Milbers
			if(!empty($ii_arr)){
				parse_str($tmp_text,$ii_arr);

				// Init the temp. Itemid
				$tmp_Itemid='';

				$db = new ps_DB;
				
				// Check if the is a menuitem for a product_id (highest priority)
				if (!empty($ii_arr['product_id'])) {
					if ($ii_product_id=intval($ii_arr['product_id'])) {
						$db->query( "SELECT id FROM #__menu WHERE link='index.php?option=com_virtuemart' AND params like '%product_id=$ii_product_id%' AND published=1");
						if( $db->next_record() ) $tmp_Itemid = $db->f("id");
					} 
				}
				// Check if the is a menuitem for a category_id
				if (!empty($ii_arr['category_id'])) {
					$ii_cat_id=intval($ii_arr['category_id']);
					if ( $ii_cat_id && $tmp_Itemid=='') {
						$db->query( "SELECT id FROM #__menu WHERE link='index.php?option=com_virtuemart' AND params like '%category_id=$ii_cat_id%' AND published=1");
						if( $db->next_record() ) $tmp_Itemid = $db->f("id");
					}
				}
				// Check if the is a menuitem for a flypage
				if (!empty($ii_arr['flypage'])) {
					$ii_flypage=$ii_arr['flypage'];
					if ($ii_flypage && $tmp_Itemid=='') {
						$db->query( "SELECT id FROM #__menu WHERE link='index.php?option=com_virtuemart' AND params like '%flypage=$ii_flypage%' AND published=1");
						if( $db->next_record() ) $tmp_Itemid = $db->f("id");
					}
				}
				// Check if the is a menuitem for a page
				if (!empty($ii_arr['page'])) {
					$ii_page=$ii_arr['page'];
					if ($ii_page && $tmp_Itemid=='') {
						$db->query( "SELECT id FROM #__menu WHERE link='index.php?option=com_virtuemart' AND params like '%page=$ii_page%' AND published=1");
						if( $db->next_record() ) $tmp_Itemid = $db->f("id");
					}
				}
				// If we haven't found an Itemid, use the standard VM-Itemid
				$Itemid = "&Itemid=" . ($tmp_Itemid ? $tmp_Itemid : $this->getShopItemid()); 
				//by tkahl end
			} else {
				$Itemid = "&Itemid=".$this->getShopItemid();
			}
			
		} else {
			$Itemid = '';
		}

		switch ($text) {
			case SECUREURL:
				$text =  SECUREURL.basename( $_SERVER['SCRIPT_NAME'] )."?".$this->component_name.$Itemid;
				break;
			case URL:
				$text =  URL.basename( $_SERVER['SCRIPT_NAME'] )."?".$this->component_name.$Itemid;
				break;
				
			default:
				$limiter = strpos($text, '?');
				if( !stristr( $text, $_SERVER['SCRIPT_NAME']) && $limiter === false ) {
					$text = '?'.$text;
				}
				$appendix = "";
				// now append "&option=com_virtuemart&Itemid=XX"
				if (!strstr($text, "option=")) {
					$appendix .= "&" . $this->component_name;
				}
				$appendix .= $Itemid;
				
				$script = basename( substr( $text, 0, $limiter ));
				if( $script == '' ) {
					$script = basename( $_SERVER['SCRIPT_NAME'] );
				}
				
				if (!defined( '_VM_IS_BACKEND' )) {
					if( $script == 'index3.php') {
						$script = 'index2.php'; // index3.php is not available in the frontend!
					}
	
					$appendix = $script.substr($text, $limiter, strlen($text)).$appendix;
				
					if( class_exists('JRoute') && !$ignoreSEF && $mainframe->getCfg('sef') ) {
						$appendix = JRoute::_( str_replace( $script.'&', $script.'?', $appendix ) );
						
					} 
					else if( function_exists('sefRelToAbs') && !$ignoreSEF && !defined( '_JLEGACY' ) ) {
						$appendix = sefRelToAbs( str_replace( $script.'&', $script.'?', $appendix ) );
					}
					if( $createAbsoluteURI && substr($appendix,0,4)!='http'  && ($ignoreSEF || !$mainframe->getCfg('sef')) ) {
						$appendix = URL . $appendix;
					}
					
					
				}
				elseif( $_SERVER['SERVER_PORT'] == 443 ) {
					//$script = strstr($_SERVER['PHP_SELF'], 'index2.php') ? 'index2.php' : 'index3.php';
					
					$appendix = SECUREURL."administrator/$script".substr($text, $limiter, strlen($text)-1).$appendix;
				}
				else {
					//$script = strstr($_SERVER['PHP_SELF'], 'index2.php') ? 'index2.php' : 'index3.php';
					$appendix = URL."administrator/$script".substr($text, $limiter, strlen($text)-1).$appendix;
				}
				if( vmIsAdminMode() && strstr($text, 'func') !== false ) {
					$appendix .= '&vmtoken='.vmSpoofValue($this->getSessionId());
				}
				if ( stristr($text, SECUREURL)) {
					$appendix = str_replace(URL, SECUREURL, $appendix);
				}
				elseif( stristr($text, URL) && $createAbsoluteURI ) {
					$appendix = str_replace(SECUREURL, URL, $appendix);
				}
	
				$text = $appendix;
	
				break;
		}
		if( $encodeAmpersands ) {
	        $text = vmAmpReplace( $text );
	    }  else {
		$text = str_replace( '&amp;', '&', $text );
	    }
		return $text;
	}

	/**
	 * Formerly printed the session id into a hidden field
	 * @deprecated 
	 * @return boolean
	 */
	function hidden_session() {
		return true;
	}

	function initRecentProducts() {
		global $recentproducts, $sess;
		// Register the recentproducts
		if (empty($_SESSION['recent'])) {
			$recentproducts = array();
			$recentproducts['idx'] = 0;
			$_SESSION['recent'] = $recentproducts;
			return $recentproducts;
		}
		
		return $_SESSION['recent'];
	}

} // end of class session
?>

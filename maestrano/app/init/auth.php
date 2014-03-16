<?php
//-----------------------------------------------
// Define root folder and load base
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}
require MAESTRANO_ROOT . '/app/init/base.php';

//-----------------------------------------------
// Require your app specific files here
//-----------------------------------------------
define('APP_DIR', realpath(MAESTRANO_ROOT . '/../'));

// Load Joomla
if (!defined('_JEXEC')){
  define( '_JEXEC', 1 );
  define( '_VALID_MOS', 1 );
  // JPATH_BASE should point to Joomla!'s root directory
  #define( 'JPATH_PLUGIN', dirname(__FILE__) );
  define( 'DS', DIRECTORY_SEPARATOR );
  define( 'JPATH_BASE', dirname(__FILE__).DS.'..'.DS.'..'.DS.'..'.DS );
  require_once ( JPATH_BASE .'includes'.DS.'defines.php' );
  require_once ( JPATH_BASE .'includes'.DS.'framework.php' );
  #require_once ( JPATH_PLUGIN . DS .'php' . DS .'authenticate.php');
  $mainframe =& JFactory::getApplication('site');
  $mainframe->initialise();
}

//-----------------------------------------------
// Perform your custom preparation code
//-----------------------------------------------
// If you define the $opts variable then it will
// automatically be passed to the MnoSsoUser object
// for construction
$opts = array();
$opts['db_connection'] = JFactory::getDBO();
$opts['jsession'] =& JFactory::getSession();



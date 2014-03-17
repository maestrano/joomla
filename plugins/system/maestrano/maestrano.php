<?php
/**
 * @version    3.1.0
 * @package    Joomla.Plugin
 * @subpackage System.SSO
 * @license    GNU/GPL
 */

error_log("Loading MNO Plugin");
defined('_JEXEC') or die;

// Do not run the authentication code below
// if we are in the maestrano sso actions (index/consume)
if (defined('MAESTRANO_ROOT')) return 1;

// Load the current application and check we are in administrator
$app = JFactory::getApplication();
if ($app->getName() != 'administrator') return 1;

// Load Maestrano
define( 'DS', DIRECTORY_SEPARATOR );
$root = realpath(dirname(__FILE__).DS.'..'.DS.'..'.DS.'..');
require_once $root . '/maestrano/app/init/base.php';
$maestrano = MaestranoService::getInstance();


// Check Maestrano session and perform
// redirects based on context
if ($maestrano->isSsoEnabled()) {
  
  // Destroy Session and
  // Redirect to Maestrano logout page 
  // if action is logout
  $params = JUri::getInstance()->getQuery(true);
  if ($params && $params['task'] == 'logout') {
    $session = JFactory::getSession();
    $session->destroy();
    session_unset();
    session_destroy();
    
    header("Location: " . $maestrano->getSsoLogoutUrl());
    exit;
  }
  
  // Get User
  $user = JFactory::getUser();
  
  // Check user is logged in and mno session is still valid
  // (User redirected to SSO automatically if not
  // logged in)
  if (!$user->id || !$maestrano->getSsoSession()->isValid()) {
    header("Location: " . $maestrano->getSsoInitUrl());
    exit;
  }
}

?>
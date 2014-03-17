<?php
/**
 * @version    3.1.0
 * @package    Joomla.Plugin
 * @subpackage Authentication.joomla
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

$uri = clone JUri::getInstance();
var_dump($uri->getQuery());
$params = parse_str($uri->getQuery());
var_dump($params);
echo "<br/><br/>";
var_dump($app->getRouter());
exit;

if ($maestrano->isSsoEnabled()) {
  // Get User
  $user = JFactory::getUser();
  
  // Check mno session is still valid
  // (User redirected to SSO automatically if not
  // logged in)
  if (!$maestrano->getSsoSession()->isValid()) {
    header("Location: " . $maestrano->getSsoInitUrl());
    exit;
  }
  
  // if ($user->id) {
  //   if (!$maestrano->getSsoSession()->isValid()) {
  //     header("Location: " . $maestrano->getSsoInitUrl());
  //     exit;
  //   }
  //   error_log("MNO SESSION IS VALID!");
  // } else {
  //   error_log("USER NOT LOGGED IN!");
  // }
}

// class plgAuthenticationMaestrano extends JPlugin
// {
//     
//     public function onUserLogout() 
//     {
//       error_log("WE ARE IN THE MNO onUserLogout METHOD!!!");
//     }
//     
//     // 
//     // public function onUserAuthorisation() 
//     // {
//     //   JLog::add(JText::_("WE ARE IN THE MNO onUserAuthorisation METHOD!!!"), JLog::INFO, 'jerror');
//     //   error_log("WE ARE IN THE MNO onUserAuthorisation METHOD!!!");
//     // }
//     
//     /**
//      * This method handles authentication via Maestrano
//      *
//      * @access    public
//      * @param     array     $credentials    Array holding the user credentials ('username' and 'password')
//      * @param     array     $options        Array of extra options
//      * @param     object    $response       Authentication response object
//      * @return    boolean
//      * @since 1.5
//      */
//     // public function onUserAuthenticate( $credentials, $options, &$response )
//     // {
//     //     error_log("WE ARE IN THE MNO onUserAuthenticate METHOD!!!");
//     //     /*
//     //      * Here you would do whatever you need for an authentication routine with the credentials
//     //      *
//     //      * In this example the mixed variable $return would be set to false
//     //      * if the authentication routine fails or an integer userid of the authenticated
//     //      * user if the routine passes
//     //      */
//     //     $query  = $this->db->getQuery(true)
//     //             ->select('id')
//     //             ->from('#__users')
//     //             ->where('username=' . $db->quote($credentials['username']));
//     //  
//     //     $this->db->setQuery($query);
//     //     $result = $this->db->loadResult();
//     //  
//     //     if (!$result) {
//     //         $response->status = STATUS_FAILURE;
//     //         $response->error_message = 'User does not exist';
//     //     }
//     //  
//     //     /**
//     //      * To authenticate, the username must exist in the database, and the password should be equal
//     //      * to the reverse of the username (so user joeblow would have password wolbeoj)
//     //      */
//     //     if($result && ($credentials['username'] == $credentials['password']))
//     //     {
//     //         $email = JUser::getInstance($result); // Bring this in line with the rest of the system
//     //         $response->email = $email->email;
//     //         $response->status = JAuthentication::STATUS_SUCCESS;
//     //     }
//     //     else
//     //     {
//     //         $response->status = JAuthentication::STATUS_FAILURE;
//     //         $response->error_message = 'Invalid username and password';
//     //     }
//     // }
// }





// Login page should redirect to SSO automatically



/**
 * Maestrano Authentication Plugin
 *
 * @package    Joomla.Plugin
 * @subpackage Authentication.joomla
 * @license    GNU/GPL
 */

?>
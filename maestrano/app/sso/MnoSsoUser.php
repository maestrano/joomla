<?php

/**
 * Configure App specific behavior for 
 * Maestrano SSO
 */
class MnoSsoUser extends MnoSsoBaseUser
{
  /**
   * Database connection
   * @var PDO
   */
  public $connection = null;
  
  
  /**
   * Extend constructor to inialize app specific objects
   *
   * @param OneLogin_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct(OneLogin_Saml_Response $saml_response, &$session = array(), $opts = array())
  {
    // Call Parent
    parent::__construct($saml_response,$session);
    
    // Assign new attributes
    $this->connection = $opts['db_connection'];
    
  }
  
  
  /**
   * Sign the user in the application. 
   * Parent method deals with putting the mno_uid, 
   * mno_session and mno_session_recheck in session.
   *
   * @return boolean whether the user was successfully set in session or not
   */
  protected function setInSession()
  {
    $db = $this->connection;
    
    
    if ($this->local_id) {
        $user = JUser::getInstance($this->local_id);
        
        // Register the needed session variables
        $session = JFactory::getSession();
        $session->set('user', $user);
        
    		// Check to see the the session already exists.
    		$app = JFactory::getApplication();
    		$app->checkSession();
    
        
        // Update the user related fields for the Joomla sessions table.
        $db->setQuery(
          'UPDATE '.$db->quoteName('#__session') .
          ' SET '.$db->quoteName('guest').' = '.$db->quote($user->get('guest')).',' .
          '	'.$db->quoteName('username').' = '.$db->quote($user->get('username')).',' .
          '	'.$db->quoteName('userid').' = '.(int) $user->get('id') .
          ' WHERE '.$db->quoteName('session_id').' = '.$db->quote($session->getId())
        );
        $db->query();

        // Hit the user last visit field
        $user->setLastVisit();
        
        return true;
    } else {
        return false;
    }
  }
  
  
  /**
   * Used by createLocalUserOrDenyAccess to create a local user 
   * based on the sso user.
   * If the method returns null then access is denied
   *
   * @return the ID of the user created, null otherwise
   */
  protected function createLocalUser()
  {
    $lid = null;
    
    if ($this->accessScope() == 'private') {
      
      // Set a temporary admin user
      $admin = JUser::getInstance();
      $admin->username = 'maestrano';
      
			$config = JFactory::getConfig();
      $config->set('root_user','maestrano');
      $session = JFactory::getSession();
      $session->set('user', $admin);
      
      // Create the user
      $user = $this->buildLocalUser();
      $user->save();
      
      
      // Remove the root user
      $config->set('root_user',null);
      $session->set('user', null);
      
      
      
      // Create user
      if ($user->id) {
        $lid = $user->id;
      }
      
    }
    
    return $lid;
  }
  
  /**
   * Build a local user for creation
   *
   * @return a hash of user attributes
   */
  protected function buildLocalUser()
  {
    $fullname = ($this->name . ' ' . $this->surname);
    $password = $this->generatePassword();
    
    $user = JUser::getInstance();
    $attr = Array(
      'name'      => $fullname,
      'email'     => $this->email,
      'username'  => $this->uid,
      'password'  => $password,
      'password2' => $password,
      'groups'    => $this->getRoleToAssign()
    );
    $user->bind($attr);
    
    return $user;
  }
  
  /**
   * Return the role to give to the user based on context
   * If the user is the owner of the app or at least Admin
   * for each organization, then it is given the role of 'Admin'.
   * Return 'User' role otherwise
   *
   * @return the ID of the user created, null otherwise
   */
  public function getRoleToAssign() {
    $role = []; // User
    
    $default_user_role = [];
    $default_admin_role = [];
    
    // Get the Super Users role
    $db = $this->connection;
    $query = $db->getQuery(true);
    $query->select($db->quoteName('id'));
    $query->from($db->quoteName('#__usergroups'));
    $query->where($db->quoteName('title') . ' = '. $db->quote('Super Users'));
    $db->setQuery($query);
    $result = $db->loadResult();
    
    if ($result && $result['id']) {
      $default_admin_role[] = $result['id'];
    }
    
    if ($this->app_owner) {
      $role = $default_admin_role; // Admin
    } else {
      foreach ($this->organizations as $organization) {
        if ($organization['role'] == 'Admin' || $organization['role'] == 'Super Admin') {
          $role = $default_admin_role;
        } else {
          $role = $default_user_role;
        }
      }
    }
  
    return $role;
  }
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {
    $db = $this->connection;
    
    $query = $db->getQuery(true);
    $query->select($db->quoteName('id'));
    $query->from($db->quoteName('#__users'));
    $query->where($db->quoteName('mno_uid') . ' = '. $db->quote($this->uid));
    $db->setQuery($query);
    $result = $db->loadResult();
    
    if ($result) {
      return intval($result);
    }
    
    return null;
  }
  
  /**
   * Get the ID of a local user via email lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByEmail()
  {
    $db = $this->connection;
    
    $query = $db->getQuery(true);
    $query->select($db->quoteName('id'));
    $query->from($db->quoteName('#__users'));
    $query->where($db->quoteName('email') . ' = '. $db->quote($this->email));
    $db->setQuery($query);
    $result = $db->loadResult();
    
    if ($result) {
      return intval($result);
    }
    
    return null;
  }
  
  /**
   * Set all 'soft' details on the user (like name, surname, email)
   * Implementing this method is optional.
   *
   * @return boolean whether the user was synced or not
   */
   protected function syncLocalDetails()
   {
     $db = $this->connection;
     
     if($this->local_id) {
       $fields = Array(
         $db->quoteName('name') . ' = '. $db->quote($this->name . ' ' . $this->surname),
         $db->quoteName('username') . ' = '. $db->quote($this->uid),
       );
     
       
       $query = $db->getQuery(true);
       $query->update($db->quoteName('#__users'));
       $query->set($fields);
       $query->where($db->quoteName('id') . ' = '. $db->quote($this->local_id));
       $db->setQuery($query);
       $upd = $db->query();
       
       return $upd;
     }
     
     return false;
   }
  
  /**
   * Set the Maestrano UID on a local user via id lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function setLocalUid()
  {
    $db = $this->connection;
    
    if($this->local_id) {
      $fields = Array(
        $db->quoteName('mno_uid') . ' = '. $db->quote($this->uid),
      );
    
      
      $query = $db->getQuery(true);
      $query->update($db->quoteName('#__users'));
      $query->set($fields);
      $query->where($db->quoteName('id') . ' = '. $db->quote($this->local_id));
      $db->setQuery($query);
      $upd = $db->query();
      
      return $upd;
    }
    
    return false;
  }
}
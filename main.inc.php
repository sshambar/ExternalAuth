<?php
/*
Plugin Name: ExternalAuth
Version: 0.2.0
Description: Supports login via webserver provided identity
Plugin URI: http://piwigo.org/ext/extension_view.php?eid=894
Author: Scott Shambarger
Author URI: http://github.com/sshambar/ExternalAuth
*/

/*
 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

defined('PHPWG_ROOT_PATH') or trigger_error('Hacking attempt!', E_USER_ERROR);

// +-----------------------------------------------------------------------+
// | Define plugin constants                                               |
// +-----------------------------------------------------------------------+
define('EXTERNALAUTH_ID',      basename(dirname(__FILE__)));
define('EXTERNALAUTH_PATH' ,   PHPWG_PLUGINS_PATH . EXTERNALAUTH_ID . '/');
define('EXTERNALAUTH_ADMIN',   get_root_url() . 'admin.php?page=plugin-' . EXTERNALAUTH_ID);

class ExternalAuth
{
  // config keys and defaults (only bool | string | array)
  public static $defaults = array(

    // General config

    // global enable for plugin (disabled initially)
    'global_enable' => false,
    // optional debug logging (or $conf['{<store>|externalauth}_debug'])
    'debug' => false,
    // $_SERVER name for remote_user
    'remote_name' => array('REMOTE_USER', 'REDIRECT_REMOTE_USER',
			   'PHP_AUTH_USER'),
    // remote guest names
    'remote_guests' => array('guest'),
    // if remote_user is guest or unknown, allow regular login
    'fallback' => true,
    // $_SERVER name for remote password
    'remote_password' => array('PHP_AUTH_PW'),
    // sync Piwigo password to Remote on login
    'sync_password' => false,
    // also handle Web API urls (perhaps with proxy)
    'webapi_also' => false,

    // Site login/logout links

    // URL for external login
    'login_url' => '',
    // label for external login
    'login_label' => 'Site Login',
    // URL for external logout
    'logout_url' => '',
    // label for external logout
    'logout_label' => 'Site Logout',

    // Registration of unknown remote_users

    // if disabled, treat them as guests (so fallback can be used)
    'register_new' => false,
    // redirect auto-created users to profile page
    'profile_new' => true,
    // notify admins on auto-created users (also requires global admin notify)
    'notify_admin_new' => false,
    // send notifation to auto-created users
    'notify_new' => false,
    // use Remote User password (if non-empty) for auto-created users
    'sync_password_new' => true,
    // don't use random passwords if Remote User password blank (USE CAUTION!)
    'norand_password_new' => false,
    // email domain to use for auto-created users
    'email_domain_new' => '',
    // username of default user (empty to use regular default)
    'default_new' => '',
    // initial status of auto-created users (empty for default)
    'status_new' => '',
  );
  // flag if fallback allowed, and current login differs from remote_user
  public $fallback_active = false;
  // $_SERVER variable remote_user found in
  public $remote_user_var = null;
  // discovered remote_user
  public $remote_user = null;
  // flag if remote_user is unknown, and register_new off
  public $remote_unknown = false;
  // flag if disabled, but would logout on enable
  public $would_logout = false;

  // name of global conf store
  protected $store = 'externalauth';
  // flag to register new user and login
  protected $register_new_user = false;
  // cache of saved configuration
  protected $conf = array();
  // true if $conf changed
  protected $config_change = false;
  // header filter hook (different on admin pages)
  protected $filter_hook = 'menubar';
  // true if we auto-registered the remote_user
  protected $user_registered = false;

  /**
   * ExternalAuth constructor
   *   - unserialize configuration from $param
   *   - locate remote_user
   *   - add handlers
   *
   * @param string $store configuration store
   */
  public function __construct($store = null)
  {
    global $conf;

    // prepare plugin configuration
    $store = empty($store) ? $this->store : $store;
    $this->store = $store;

    if (isset($conf[$store]))
    {
      $conf[$store] = safe_unserialize($conf[$store]);
      if (! is_array($conf[$store]))
      {
	$conf[$store] = array();
      }
      $this->conf = $conf[$store];
    }

    // stay out of the way on Web API requests (unless requested)
    if(script_basename() == 'ws' && ! $this->get_conf('webapi_also'))
    {
      return;
    }

    // add event handlers
    add_event_handler('user_init', array($this, 'user_init'));
    if(script_basename() == 'ws')
    {
      return;
    }
    add_event_handler('init', array($this, 'init'));
    add_event_handler('loc_begin_identification', array($this, 'ident_page'));

    if (defined('IN_ADMIN') && IN_ADMIN)
    {
      // admin plugins menu link
      $this->filter_hook = 'header';
      add_event_handler('get_admin_plugin_menu_links',
			array($this, 'admin_plugin_menu_links'),
			EVENT_HANDLER_PRIORITY_NEUTRAL);
      add_event_handler('loc_end_page_header', array($this, 'template_apply'));
    }
    else
    {
      add_event_handler('blockmanager_apply', array($this, 'template_apply'),
			EVENT_HANDLER_PRIORITY_NEUTRAL+10);
    }
  }

  /**
   * Log debug $message to logger
   *
   * @param string $message
   * @param array $args
   */
  public function debug($message, $args = array())
  {
    global $logger, $conf;
    // check if debug logging enabled
    if (@!empty($conf[$this->store . '_debug'])
	|| $this->get_conf('debug'))
    {
      $logger->debug($message, $this->store, $args);
    }
  }

  /**
   * Log error $message to logger
   *
   * @param string $message
   * @param array $args
   */
  public function error($message, $args = array())
  {
    global $logger;
    $logger->error($message, $this->store, $args);
  }

  /**
   * Validate param name, return true if valid
   *
   * @param string $param
   * @return bool
   */
  public function is_param($param)
  {
    if (! isset($this::$defaults[$param]))
    {
      $this->error('unknown config name '.$param);
      return false;
    }
    return true;
  }

  /**
   * Return config value, or default is config unset
   *
   * @param string $param
   * @return mixed
   */
  public function get_conf($param)
  {
    if(! $this->is_param($param))
    {
      return null;
    }

    return isset($this->conf[$param]) ? $this->conf[$param] :
	   $this::$defaults[$param];
  }

  /**
   * Sets cached config value $param to $val (unsetting if default)
   *
   * @param string $param
   * @param mixed $val
   */
  public function set_conf($param, $val)
  {
    $this->debug('  set_conf '.$param.'='.var_export($val, true));
    if (! ($this->is_param($param) &&
	   ($val !== $this->get_conf($param))))
    {
      return;
    }
    // new value, check if default
    $default = $this::$defaults[$param];
    if($val === $default)
    {
      unset($this->conf[$param]);
    }
    else {
      $this->conf[$param] = $val;
    }
    // flag change
    $this->config_change = true;
  }

  /**
   * Saves config if changed.  Return true if changes saved.
   *
   * @return bool
   */
  public function save_conf()
  {
    if (! $this->config_change)
    {
      return false;
    }
    if (count($this->conf))
    {
      $this->debug('save_conf: '.serialize($this->conf));
      conf_update_param($this->store, $this->conf, true);
    }
    else
    {
      $this->debug('save_conf: deleted');
      conf_delete_param($this->store);
    }
    $this->config_change = false;
    return true;
  }

  /**
   * Redirects to current page to reload session
   *
   * Always exits
   * @param bool $gohome true to redirect to homepage
   */
  protected function redirect($gohome = false)
  {
    if ($this->user_registered && $this->get_conf('profile_new'))
    {
      $url = 'profile.php';
    }
    else
    {
      $url = $gohome ? get_gallery_home_url() : $this->current_url();
    }
    $this->debug('redirect to: '.$url);
    redirect($url);
  }

  /**
   * loc_begin_identification event handler
   *   - redirect to homepage if fallback is disabled
   *
   * May exit if redirect is performed
   */
  public function ident_page()
  {
    if (! $this->get_conf('fallback'))
    {
      $this->debug('identification page and fallback disabled, redirect home');
      $this->redirect(true);
    }
  }

  /**
   * admin menu event handler
   *   - append admin link to menu
   *
   * @param array $menu
   * @return array
   */
  public static function admin_plugin_menu_links($menu)
  {
    $menu[] = array(
      'NAME' => 'ExternalAuth',
      'URL' => EXTERNALAUTH_ADMIN,
    );

    return $menu;
  }

  protected function remote_user_match($username)
  {
    global $conf;
    if ($conf['insensitive_case_logon'] == true)
    {
      return (strcasecmp($this->remote_user, $username) == 0);
    }
    return $this->remote_user === $username;
  }

  /**
   * Return true if remote_user is considered guest
   *
   * @return bool
   */
  public function remote_is_guest()
  {
    // empty/missing remote_user is always a guest (no login available)
    if (empty($this->remote_user))
    {
      return true;
    }
    foreach ($this->get_conf('remote_guests') as $name)
    {
      if ($this->remote_user_match($name))
      {
	return true;
      }
    }
    return false;
  }

  /**
   * Return true if current login is the guest_id
   *
   * @return bool
   */
  protected static function is_logged_out()
  {
    global $user, $conf;

    return $user['id'] == $conf['guest_id'];
  }

  /**
   * Return current login username
   *
   * @return string
   */
  protected static function get_login_name()
  {
    global $user;

    return $user['username'];
  }

  /**
   * Logout current user session (if not already logged out)
   *   - return true if logout performed
   *
   * @return bool
   */
  protected function logout_session()
  {
    if (! $this->is_logged_out())
    {
      $this->debug('logging out '.$this->get_login_name());
      logout_user();
      return true;
    }
    return false;
  }

  /**
   * Returns true if apache_authentication enabled
   *
   * @returns bool
   */
  public static function apache_auth()
  {
    global $conf;
    return $conf['apache_authentication'];
  }

  /**
   * Returns true if all criteria to enable plugin are met.
   *
   * @returns bool
   */
  protected function is_enabled()
  {
    // requires apache_auth disabled and global_enable
    return $this->apache_auth() ? false : $this->get_conf('global_enable');
  }

  /**
   * Performs logout if there is a current login session,
   * returns if already logged out.
   *
   * May exit if logout is performed (redirect)
   * @param bool gohome true to redirect home rather than current page
   */
  protected function perform_logout($gohome = false)
  {
    if (! $this->is_enabled())
    {
      $this->would_logout = true;
      return;
    }
    if ($this->logout_session())
    {
      $this->redirect($gohome);
    }
  }

  /**
   * Updates USERS_TABLE for $user_id with $data after mapping to 'user_fields'
   *
   * @param int $user_id
   * @param array $data array of values to update
   */
  protected static function update_user($user_id, $data)
  {
    global $conf;
    $fields = array();
    $vals = array();
    foreach ($data as $key => $val)
    {
      if (isset($conf['user_fields'][$key]))
      {
	$dbkey = $conf['user_fields'][$key];
	$fields[] = $dbkey;
	$vals[$dbkey] = $val;
      }
    }
    if (count($fields))
    {
      $dbid = $conf['user_fields']['id'];
      $vals[$dbid] = $user_id;
      mass_updates(USERS_TABLE,
		   array('primary' => array($dbid), 'update' => $fields),
                   array($vals));
    }
  }

  /**
   * Updates USER_INFOS_TABLE for $user_id with $data
   *
   * @param int $user_id
   * @param array $data array of values to update
   */
  protected static function update_user_infos($user_id, $data)
  {
    $fields = array_keys($data);
    $data['user_id'] = $user_id;
    mass_updates(USER_INFOS_TABLE,
		 array('primary' => array('user_id'), 'update' => $fields),
                 array($data));
  }

  /**
   * get_server_var
   *   - walks all names in config $config array for a value
   *   - set $varname to named used
   *
   * @param string $config config parameter of array
   * @param string &$var variable name used
   * @return string|null addslashed value
   */
  protected function get_server_var($config, &$varname = null)
  {
    foreach ($this->get_conf($config) as $name)
    {
      if (isset($_SERVER[$name]))
      {
	$varname = $name;
	return addslashes($_SERVER[$name]);
      }
    }
    return null;
  }

  /**
   * Attempts to sync any remote password with Piwigo account
   *
   * @param int $user_id
   */
  protected function check_sync_password($user_id)
  {
    if ($this->get_conf('sync_password') && ! $this->user_registered)
    {
      global $conf;
      $password = $this->get_server_var('remote_password');
      if (! empty($password))
      {
	$user = getuserdata($user_id);
	if (isset($user['id']) &&
		  ! $conf['password_verify']($password, $user['password'],
					     $user_id))
	{
	  // check if password matches
	  $this->debug('syncing password for remote user "' .
		       $this->remote_user . '"');
	  $data = array('password' => $conf['password_hash']($password));
	  $this->update_user($user_id, $data);
	  deactivate_user_auth_keys($user_id);
	}
      }
    }
  }

  /**
   * Attempts to login as $user_id
   *
   * This function exits (redirect)
   *
   * @param int $user_id
   */
  protected function perform_login($user_id)
  {
    if (! $this->is_enabled())
    {
      $this->would_logout = true;
      return;
    }
    $this->logout_session();
    $this->debug('logging in '.$this->remote_user);
    $this->check_sync_password($user_id);
    log_user($user_id, false);
    trigger_notify('login_success', stripslashes($this->remote_user));

    // now redirect so correct $user is setup
    $this->redirect();
  }

  /**
   * Check if fallback allowed, and if not force logout
   *   - caches unknown remote_users in guest SESSION cache
   *
   * May exit if changing to logout needed (redirect)
   */
  protected function check_fallback()
  {
    // if registering, wait for init
    if ($this->register_new_user)
    {
      // we need to be guest to register new users (fallthrough if guest)
      // also, redirect home so any errors may be displayed...
      $this->perform_logout(true);
      return;
    }
    if ($this->remote_unknown && isset($_SESSION))
    {
      // set cache of unknown user
      $_SESSION[$this->store.'_nouser'] = $this->remote_user;
    }

    if ($this->get_conf('fallback'))
    {
      // allow any login
      $this->fallback_active = true;
      $this->debug('fallback enabled, allow other logins');
    }
    else
    {
      // logout, or falls through
      $this->perform_logout();
    }
  }

  /**
   * Create random password
   *
   * @param int $length
   * @return string
   */
  protected static function random_password( $length = 12 ) {
    // avoid non-websafe and ambigous characters
    $chars = "abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#^*_-=:,.";
    return substr( str_shuffle( $chars ), 0, $length );
  }

  /**
   * Register Remote User
   *   - handles using alternate default user
   *   - notifies admin is requested
   *   - updates status of new users
   *
   * @return int|false
   */
  protected function register_remote_user() {
    global $cache, $conf, $page;

    // if default user exists, temporarily replace global default (clear cache)
    $default_user = $this->get_conf('default_new');
    $default_id = empty($default_user) ? false : get_userid($default_user);
    if ($default_id)
    {
      $this->debug('using remote default user "' . $default_user . '"');
      unset($cache['default_user']);
      $orig_default_id = $conf['default_user_id'];
      $conf['default_user_id'] = $default_id;
    }

    $this->debug('registering remote user "' . $this->remote_user . '"');

    // use sync password if available
    $password = ($this->get_conf('sync_password_new') ||
		 $this->get_conf('sync_password')) ?
		$this->get_server_var('remote_password') : '';
    if (empty($password))
    {
      // use random password unless disabled
      $password = $this->get_conf('norand_password_new') ? '' :
		  $this->random_password();
    }

    // check for auto-email
    $domain = $this->get_conf('email_domain_new');
    $email = empty($domain) ? null : $this->remote_user . '@' . $domain;
    $notify = empty($email) ? false : $this->get_conf('notify_new');

    // use our admin notify setting
    $old_notify = $conf['email_admin_on_new_user'];
    $conf['email_admin_on_new_user'] = $this->get_conf('notify_admin_new');

    // perform the registration
    if (($id = register_user($this->remote_user, $password, $email, true,
			       $page['errors'], $notify)))
    {
      $this->user_registered = true;
    }
    else
    {
      // show errors on page
      if(empty($email))
      {
	$msg = sprintf(l10n('Auto-registration of user "%s" failed'),
		       $this->remote_user);
      }
      else
      {
	$msg = sprintf(l10n('Auto-registration of user "%s" (email <%s>) failed'),
		       $this->remote_user, $email);
      }
      // prepend our message to the reasons
      array_unshift($page['errors'], htmlspecialchars($msg.':'));
    }

    // restore the overrides
    $conf['email_admin_on_new_user'] = $old_notify;

    // restore original default user
    if ($default_id)
    {
      unset($cache['default_user']);
      $conf['default_user_id'] = $orig_default_id;
    }

    // check if custom status requested
    $default_status = $this->get_conf('status_new');
    if ($id && ! empty($default_status))
    {
      // validate our status is real
      foreach (get_enums(USER_INFOS_TABLE, 'status') as $status)
      {
	if ($status === $default_status)
	{
	  // update new user status
	  $this->debug('updating new user "' . $this->remote_user .
		       '" status status to ' . $status);
	  $this->update_user_infos($id, array('status' => $status));
	}
      }
    }

    return $id;
  }

  /**
   * init event handler
   *   - load language files
   */
  public function init()
  {
    // load plugin language file
    load_language('plugin.lang', EXTERNALAUTH_PATH);
    if ($this->register_new_user)
    {
      // now attempt to register user
      if (($id = $this->register_remote_user()))
      {
	// login as new userid
	$this->perform_login($id);
      }
      else {
	// fallback or logout since register failed
	$this->debug('failed to register user, treating as unknown');
	$this->register_new_user = false;
	$this->remote_unknown = true;
	$this->check_fallback();
      }
    }
  }

  /**
   * Search for user_id (possibly case insensitively) using $username
   *
   * @param string $username
   * @return int|false
   */
  protected static function search_for_userid($username) {

    global $conf;
    if (($id = get_userid($username)))
    {
      return $id;
    }
    if ($conf['insensitive_case_logon'] == true)
    {
      $id = get_userid(search_case_username($username));
    }
    return $id;
  }

  /**
   * Returns userid of remote_user, or 0 if unknown and register_new off
   *
   * @return int|false
   */
  protected function get_remote_userid() {

    // check cache (case sensitively)
    if (isset($_SESSION) && isset($_SESSION[$this->store.'_nouser'])
	&& ($this->remote_user === $_SESSION[$this->store.'_nouser']))
    {
      $this->debug('remote_user unknown (cached)');
      $this->remote_unknown = true;
      return false;
    }

    if (!($id = $this->search_for_userid($this->remote_user)))
    {
      // user not in db
      $this->remote_unknown = true;
      if (! $this->get_conf('register_new'))
      {
	$this->debug('remote_user not in db, register_new off');
      }
      elseif ($this->is_enabled())
      {
	// defer registration until language is loaded...
	$this->register_new_user = true;
      }
    }

    return $id;
  }

  /**
   * user_init event handler
   *   - main extention handler: verify current login matches remote_user
   *     with allowed exceptions.
   *
   * May exit if changing to another login (redirect)
   */
  public function user_init()
  {
    $this->remote_user = $this->get_server_var('remote_name',
					       $this->remote_user_var);

    if ($this->remote_is_guest())
    {
      $this->debug('remote_user is guest');
      $this->check_fallback();
    }
    else {
      $username = $this->get_login_name();

      $this->debug('remote_user is '.$this->remote_user.
		   ', current login is '.$username);

      if(! $this->remote_user_match($username))
      {
	if (($id = $this->get_remote_userid()))
	{
	  $this->perform_login($id);
	}
	else
	{
	  // we've checked, and this user doesn't exist...
	  $this->check_fallback();
	}
      }
    }
  }

  /**
   * Reload ExternalAuth instance
   *   - reset state
   *   - retry user_init
   *
   * May exit if changing to another login (redirect)
   */
  public function reload()
  {
    // set state to initial values
    $this->fallback_active = false;
    $this->remote_user_var = null;
    $this->remote_user = null;
    $this->remote_unknown = false;
    $this->would_logout = false;
    $this->user_registered = false;

    if (isset($_SESSION))
    {
      // clear cache of unknown user
      unset($_SESSION[$this->store.'_nouser']);
    }

    // now re-try user_init with current config
    $this->user_init();
    // might need to register...
    $this->init();
  }

  /**
   * Static output filter to remove links
   *
   * @param string $output html to remove link from
   * @param string $template (not used)
   * @return string modified html
   */
  public static function output_filter($output, $template)
  {
    // should work on default themes, and bootstrap
    return preg_replace('#(<li>)?[ ]*<a [^>]*\"EA_REMOVE_LINK\"[^>]*>((?!</a>).)*</a>[ ]*(</li>)?#is',
			'', $output);
  }

  /**
   * Retrieve current page URL
   *
   * @return string current URL
   */
  protected static function current_url()
  {
    return $_SERVER['REQUEST_URI'];
  }

  /**
   * Modify link that has embedded variables
   *  - replaces %(url) and %(absurl)
   *
   * @param string $link URL template
   * @return string URL with variables substituted
   */
  protected function url_substitution($link)
  {
    $url = $this->current_url();
    $aurl = substr(get_absolute_root_url(), 0, -strlen(cookie_path())) . $url;
    $map = array('url' => urlencode($url),
		 'absurl' => urlencode($aurl));
    $keys = array_map(function($k) { return "/%\\($k\\)/"; },
		      array_keys($map));
    return preg_replace($keys, array_values($map), $link);
  }

  /**
   * Assigns $url_conf to U_LOGOUT (if non-empty), and updates label
   * with $label_conf (translated)
   *
   * @param string $url_conf config name of URL
   * @param string $label_conf config name of label
   */
  protected function set_action_link($url_conf, $label_conf)
  {
    global $template, $lang;

    $url = $this->get_conf($url_conf);
    if (empty($url))
    {
      // remove any links marked at output (since some themes condition
      // the display of other elements on them at compile time)
      $template->assign('U_LOGOUT', 'EA_REMOVE_LINK');
      // add static output_filter (gets serialized, so can't use $this)
      $template->set_outputfilter($this->filter_hook,
				  array(get_class($this), 'output_filter'));
      $template->block_html_style(null, '.icon-logout{display:none;}');
      if ($url_conf === 'login_url' && ! $this->get_conf('fallback'))
      {
	// no login or link possible, display static guest
	$template->assign('USERNAME', l10n('guest'));
      }
    }
    else
    {
      // use logout block as link (login block may be a form...)
      $url = $this->url_substitution($url);
      $template->assign('U_LOGOUT', htmlspecialchars($url, ENT_QUOTES));
      $label = $this->get_conf($label_conf);
      if (empty($label))
      {
	// label can't be empty
	$label = $this::$defaults[$label_conf];
      }
      // translate if possible...
      $lang['Logout'] = isset($lang[$label]) ? l10n($label) :
			htmlspecialchars($label, ENT_QUOTES);
    }
  }

  /**
   * Updates template when logged out
   */
  protected function template_when_logged_out()
  {
    global $template;

    $this->set_action_link('login_url', 'login_label');

    if (! $this->get_conf('fallback'))
    {
      // we only allow remote_user logins, no fallback
      $template->clear_assign('U_LOGIN');
      $template->clear_assign('U_REGISTER');
    }
  }

  /**
   * Updates template when currently logged in
   */
  protected function template_when_logged_in()
  {
    if ($this->fallback_active || $this->remote_is_guest())
    {
      // we don't control this login, leave logout alone
      return;
    }

    // Set logout link, or remove it if unset
    $this->set_action_link('logout_url', 'logout_label');
  }

  /**
   * blockmanager_apply event handler
   *   - updates template based on current login state
   */
  public function template_apply()
  {
    if (! $this->is_enabled())
    {
      return;
    }
    if ($this->is_logged_out())
    {
      $this->template_when_logged_out();
    }
    else
    {
      $this->template_when_logged_in();
    }
  }
}

// Initialize global with default instance
global $ExternalAuth;
$ExternalAuth = new ExternalAuth();


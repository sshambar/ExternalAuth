<?php

// This file is part of the ExternalAuth Piwigo plugin

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

class ExternalAuthAdmin
{
  // our tabs
  protected static $tabs = array(
    'auth' => 'Authentication',
    'reg'=> 'New Users',
    'help'=> 'Help',
  );
  // keys for each form action
  protected static $action_keys = array(
    'enable' => array('global_enable'),
    'fallback' => array('fallback'),
    'auth' => array('remote_name', 'remote_guests',
		    'sync_password', 'remote_password',
		    'login_url', 'login_label',
		    'logout_url', 'logout_label',
		    'webapi_also', 'debug'),
    'reg' => array('register_new', 'profile_new', 'notify_admin_new',
		   'notify_new', 'sync_password_new', 'norand_password_new',
		   'email_domain_new', 'default_new', 'status_new'));
  // form post action (if any)
  protected $form_action = null;
  // key => value posted values
  protected $post_vals = array();
  // cache of default $ExternalAuth
  protected $ea = null;
  // we don't allow status_new to have these values
  protected $blocked_statuses = array('webmaster', 'guest', 'generic');

  /**
   * ExternalAuthAdmin constructor
   *   - add handlers
   *   - verify token if POST
   *
   * @param object $ExternalAuth
   */
  public function __construct($ExternalAuth)
  {
    // this is the default 'externalauth' instance
    $this->ea = $ExternalAuth;

    if (isset($_POST['action']))
    {
      // check this early before we start messing with templates
      check_pwg_token();
      $this->form_action = $_POST['action'];
    }

    // only admins here (webmaster checked for in post...)
    check_status(ACCESS_ADMINISTRATOR);

    // add user_init handler after ExternalAuth class
    add_event_handler('loc_begin_page_header', array($this, 'admin_page'));
    add_event_handler('loc_end_page_header', array($this, 'add_layout'));

    include_once(PHPWG_ROOT_PATH .'admin/include/tabsheet.class.php');
  }

  /**
   * Log debug $message to logger
   *
   * @param string $message
   * @param array $args
   */
  protected function debug($message, $args = array())
  {
    $this->ea->debug($message, $args);
  }

  /**
   * Log error $message to logger
   *
   * @param string $message
   * @param array $args
   */
  protected function error($message, $args = array())
  {
    $this->ea->error($message, $args);
  }

  /**
   * Returns the current tab name
   *
   * @return string
   */
  protected function get_tab()
  {
    global $page;
    $tab = isset($_GET['tab']) ? $_GET['tab'] : null;
    $page['tab'] = isset($this::$tabs[$tab]) ?
		   $tab : current(array_keys($this::$tabs));
    return $page['tab'];
  }

  /**
   * Show message in page alert box
   *
   * @param string $section "error" or "infos"
   * @param string $msg
   */
  protected static function show_msg($section, $msg)
  {
    global $page;
    $page[$section][] = l10n($msg);
  }

  /**
   * Export all externalauth config to the template
   *
   * @param object $template
   * @param string $tab tab to export for
   */
  protected function export_config($template, $tab)
  {
    // send variables to template
    $vals = array();
    $ea = $this->ea;
    foreach (array_keys($ea::$defaults) as $key)
    {
      $val = $ea->get_conf($key);
      if (is_array($val))
      {
	$val = implode(', ', $val);
      }
      if (! is_bool($val) || $val)
      {
	$vals[$key] = $val;
      }
    }
    $template->assign('EA', $vals);

    $template->assign('EA_FALLBACK_ACTIVE', $ea->fallback_active ? '1':'');
    $template->assign('EA_WOULD_LOGOUT', $ea->would_logout ? '1':'');
    $template->assign('EA_REMOTE_USER_VAR', $ea->remote_user_var);
    $template->assign('EA_REMOTE_USER', $ea->remote_user);
    $template->assign('EA_REMOTE_UNKNOWN', $ea->remote_unknown ? '1':'');
    $template->assign('EA_REMOTE_IS_GUEST', $ea->remote_is_guest() ? '1':'');
    if ($tab == 'reg')
    {
      // warn if default user is not found
      $default_user = $ea->get_conf('default_new');
      if ((! empty($default_user)) && (! get_userid($default_user)))
      {
	$template->assign('EA_DEFAULT_NEW_WARNING', '1');
      }
      $status_options = array('' => '(default)');
      foreach (get_enums(USER_INFOS_TABLE, 'status') as $status)
      {
	// only override with users with at least profile access
	if (! in_array($status, $this->blocked_statuses))
	{
	  $status_options[$status] = l10n('user_status_'.$status);
	}
      }

      $template->assign('EA_STATUS_OPTIONS', $status_options);
    }
  }

  /**
   * Array map callback to compact a server name value
   *
   * @param string &$val string to compact
   * @param string $key array index (ignored)
   */
  protected static function compact_name_value(&$val, $key)
  {
    $val = preg_replace("/[^a-zA-Z0-9_]/", "", $val);
  }

  /**
   * Parse $_POST[EA_$param] based on type
   *
   * @param string $param
   * @return mixed
   */
  protected function import_param($param)
  {
    $ea = $this->ea;
    $default = $ea::$defaults[$param];
    if (isset($_POST['EA_'.$param]))
    {
      $val = trim($_POST['EA_'.$param]);
      if (is_array($default))
      {
	$val = explode(',', $val);
	array_walk($val, array($this, 'compact_name_value'));
	$val = array_diff($val, array(''));
      }
      elseif (is_bool($default))
      {
	$val = $val ? true : false;
      }
    }
    elseif (is_array($default))
    {
      $val = array();
    }
    else
    {
      $val = is_bool($default) ? false : "";
    }
    if (in_array($param, array('login_url', 'logout_url', 'email_domain_new',
			       'status_new')))
    {
      // remove whitespace from these values
      $val = preg_replace('/[[:space:]]/', '', $val);
    }
    if ($param === 'email_domain_new' && ! empty($val))
    {
      if(strpos($val, '@') === 0)
      {
	// handle common typo
	$val = substr($val, 1);
      }
      // test creating an email address
      if (! email_check_format('user@'.$val))
      {
	// no good, fallback to empty
	$this->show_msg('errors', 'Email domain invalid');
	$val = '';
      }
    }
    if ($param === 'status_new')
    {
      // validate against database enums
      $test_val = $val;
      $val = '';
      foreach (get_enums(USER_INFOS_TABLE, 'status') as $status)
      {
	if ((! in_array($status, $this->blocked_statuses)) &&
	    ($test_val === $status))
	{
	  $val = $test_val;
	  break;
	}
      }
    }
    return $val;
  }

  /**
   * Load any posted config values from current form into $this->post_vals
   * Return true if changes made.
   *
   * @return bool
   */
  protected function process_post()
  {
    // look for and handle only the keys expected on current tab
    if (! isset(self::$action_keys[$this->form_action]))
    {
      return false;
    }

    foreach (self::$action_keys[$this->form_action] as $param)
    {
      if ($this->ea->is_param($param))
      {
	$this->ea->set_conf($param, $this->import_param($param));
      }
    }

    // always give saved feedback even if no changes...
    $this->show_msg('infos', 'Information data registered in database');

    return $this->ea->save_conf();
  }

  /**
   * Return post action if can be processed.
   *
   * @return string|null
   */
  protected function check_post()
  {
    if (is_webmaster())
    {
      // make sure confirmation present
      if (! isset($_POST['confirm']))
      {
	switch ($this->form_action)
	{
	  case 'enable':
	    $msg = 'Enabling plugin was not confirmed!';
	    break;
	  case 'fallback':
	    $msg = 'Disabling Fallback was not confirmed!';
	    break;
	  default:
	    $msg = null;
	    break;
	}
	if ($msg)
	{
	  $this->show_msg('errors', $msg);
	  return null;
	}
      }
      return $this->form_action;
    }

    if ($this->form_action)
    {
      $this->show_msg('errors', 'Webmaster status is required.');
    }

    return null;
  }

  /**
   * loc_begin_admin_page event handler
   */
  public function admin_page()
  {
    // process tab
    if($this->check_post() && $this->process_post())
    {
      // reload user_init with updated config
      $this->ea->reload();
    }
  }

  /**
   * Add any alerts the user should be aware of
   */
  protected function display_warnings()
  {
    if(! $this->ea->get_conf('global_enable'))
    {
      // show notice that plugin is disabled
      $this->show_msg('errors', 'NOTE: This plugin is currently disabled.');
      $this->show_msg('errors', 'You can modify settings until the Status section looks correct, and then Enable the plugin.');
    }
    if ($this->ea->apache_auth())
    {
      $this->show_msg('errors', 'This plugin cannot function when $conf[\'apache_configuration\'] is enabled, and so is disabled.');
    }
  }

  /**
   * Handle the authentication tab
   *
   * @param object $template
   */
  protected function layout_auth($template)
  {
    $this->display_warnings();
    return;
  }

  /**
   * Handle the registration tab
   *
   * @param object $template
   */
  protected function layout_reg($template)
  {
    $this->display_warnings();
    return;
  }

  /**
   * Handle the help tab
   *
   * @param object $template
   */
  protected function layout_help($template)
  {
    $template->assign(array(
      'HELP_CONTENT' => load_language('help.html', EXTERNALAUTH_PATH, array('return'=>true)),
    ));
  }

  /**
   * loc_end_page_header event handler
   *   - export all common elements to the template
   */
  public function add_layout()
  {
    global $template;

    $tab = $this->get_tab();
    // tabsheet
    $tabsheet = new tabsheet();
    $tabsheet->set_id('externalauth');
    foreach (array_keys($this::$tabs) as $key)
    {
      $tabsheet->add($key, l10n($this::$tabs[$key]), EXTERNALAUTH_ADMIN.'-'.$key);
    }
    $tabsheet->select($tab);
    $tabsheet->assign();

    // export form values
    $this->export_config($template, $tab);

    // template vars
    call_user_func(array($this, 'layout_'.$tab), $template);
    $template->assign(array(
      'F_ACTION' => EXTERNALAUTH_ADMIN.'-'.$tab,
      'PWG_TOKEN' => get_pwg_token(),
    ));

    // define template file
    $template->set_filename('externalauth_content',
			    realpath(EXTERNALAUTH_PATH.'template/'.$tab.'.tpl'));
    // send page content
    $template->assign_var_from_handle('ADMIN_CONTENT', 'externalauth_content');
  }
}

// Initialize global with default instance
global $ExternalAuthAdmin;
global $ExternalAuth;
// initialize with the global 'externalauth' instance
$ExternalAuthAdmin = new ExternalAuthAdmin($ExternalAuth);

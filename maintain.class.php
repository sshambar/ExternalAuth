<?php
defined('PHPWG_ROOT_PATH') or die('Hacking attempt!');

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

/**
 * This class is used to expose maintenance methods to the plugins manager
 * It must extends PluginMaintain and be named "PLUGINID_maintain"
 * where PLUGINID is the directory name of your plugin.
 */
class ExternalAuth_maintain extends PluginMaintain
{

  protected $store = 'externalauth';

  function __construct($plugin_id)
  {
    parent::__construct($plugin_id); // always call parent constructor
  }

  /**
   * Plugin activation
   */
  function activate($plugin_version, &$errors=array())
  {
    global $conf;

    // ensure that plugin is disabled on activation
    if (isset($conf[$this->store]))
    {
      $old_conf = safe_unserialize($conf[$this->store]);
      if (isset($old_conf['global_enable']) && $old_conf['global_enable'])
      {
	unset($old_conf['global_enable']);
	conf_update_param($this->store, $old_conf, true);
      }
    }
  }

  /**
   * Plugin uninstallation
   */
  function uninstall()
  {
    // delete configuration
    conf_delete_param($this->store);
  }
}

<?php
// from main.inc.php
$lang['External Authentication Plugin'] = 'External Authentication Plugin';
$lang['Site Login'] = 'Site Login';
$lang['Site Logout'] = 'Site Logout';
$lang['Auto-registration of user "%s" failed'] = 'Auto-registration of user "%s" failed';
$lang['Auto-registration of user "%s" (email <%s>) failed'] = 'Auto-registration of user "%s" (email <%s>) failed';
// from admin.php
$lang['Email domain invalid'] = 'Email domain invalid';
$lang['Enabling plugin was not confirmed!'] = 'Enabling plugin was not confirmed!';
$lang['Disabling Fallback was not confirmed!'] = 'Disabling Fallback was not confirmed!';
$lang['NOTE: This plugin is currently disabled.'] = 'NOTE: This plugin is currently disabled.';
$lang['You can modify settings until the Status section looks correct, and then Enable the plugin.'] = 'You can modify settings until the Status section looks correct, and then Enable the plugin.';
$lang['This plugin cannot function when $conf[\'apache_configuration\'] is enabled, and so is disabled.'] = 'This plugin cannot function when $conf[\'apache_configuration\'] is enabled, and so is disabled.';
// tab labels
$lang['Authentication'] = 'Authentication';
$lang['New Users'] = 'New Users';
// from auth.tpl
$lang['Introduction'] = 'Introduction';
$lang['Current Status'] = 'Current Status';
$lang['Server Variable Used'] = 'Server Variable Used';
$lang['Remote User'] = 'Remote User';
$lang['a guest'] = 'a guest';
$lang['empty'] = 'empty';
$lang['Remote User is known to Piwigo'] = 'Remote User is known to Piwigo';
$lang['CAUTION'] = 'CAUTION';
$lang['Remote User doesn\'t match your current login.'] = 'Remote User doesn\'t match your current login.';
$lang['Enabling plugin will log you out!'] = 'Enabling plugin will log you out!';
$lang['Try enabling Fallback'] = 'Try enabling Fallback';
$lang['Disable Plugin'] = 'Disable Plugin';
$lang['Enable Plugin'] = 'Enable Plugin';
$lang['I understand this action will immediately log me out'] = 'I understand this action will immediately log me out';
$lang['Fallback Authentication'] = 'Fallback Authentication';
$lang['Disable Fallback Authentication, forcing login to always match the current Remote User'] = 'Disable Fallback Authentication, forcing login to always match the current Remote User';
$lang['Enable Fallback Authentication, allowing native logins when Remote User is a guest or unknown'] = 'Enable Fallback Authentication, allowing native logins when Remote User is a guest or unknown';
$lang['Disabling Fallback will log you out!'] = 'Disabling Fallback will log you out!';
$lang['Disable Fallback'] = 'Disable Fallback';
$lang['Enable Fallback'] = 'Enable Fallback';
$lang['Authentication Options'] = 'Authentication Options';
$lang['Comma separated list of $_SERVER variables searched to locate the Remote User\'s login (in order)'] = 'Comma separated list of $_SERVER variables searched to locate the Remote User\'s login (in order)';
$lang['Server Login Variables'] = 'Server Login Variables';
$lang['Since fallback is disabled, removing the variable in use (%s) will immediately log you out!'] = 'Since fallback is disabled, removing the variable in use (%s) will immediately log you out!';
$lang['Remote Guests'] = 'Remote Guests';
$lang['Comma separated list of Remote Users that are considered guests.'] = 'Comma separated list of Remote Users that are considered guests.';
$lang['Empty or missing Remote Users are always guests.'] = 'Empty or missing Remote Users are always guests.';
$lang['Enforce on Web API requests'] = 'Enforce on Web API requests';
$lang['These requests use their own login API. This setting will only be useful if you have a custom-built client (or a specially configured proxy server).'] = 'These requests use their own login API. This setting will only be useful if you have a custom-built client (or a specially configured proxy server).';
$lang['Password Sync Options'] = 'Password Sync Options';
$lang['Sync Piwigo account password on login'] = 'Sync Piwigo account password on login';
$lang['Overwrite the Piwigo password with Remote User password (if not empty).'] = 'Overwrite the Piwigo password with Remote User password (if not empty).';
$lang['Server Password Variables'] = 'Server Password Variables';
$lang['Comma separated list of $_SERVER variables searched to locate the Remote User\'s password (in order).  The password is used for sync and user auto-creation.'] = 'Comma separated list of $_SERVER variables searched to locate the Remote User\'s password (in order).  The password is used for sync and user auto-creation.';
$lang['Remote Authentication Links'] = 'Remote Authentication Links';
$lang['If not empty, link is added to the menu when appropriate.'] = 'If not empty, link is added to the menu when appropriate.';
$lang['References to relative \'%(url)\' or absolute \'%(absurl)\' URLs can be included (will be URL escaped).'] = 'References to relative \'%(url)\' or absolute \'%(absurl)\' URLs can be included (will be URL escaped).';
$lang['Label will be translated if possible'] = 'Label will be translated if possible';
$lang['Login URL'] = 'Login URL';
$lang['Label'] = 'Label';
$lang['Logout URL'] = 'Logout URL';
$lang['Troubleshooting'] = 'Troubleshooting';
$lang['Enable plugin debug logging'] = 'Enable plugin debug logging';
$lang['Send debug messages to Piwigo logs.  Debug logging can also be enabled by setting %s'] = 'Send debug messages to Piwigo logs.  Debug logging can also be enabled by setting %s';

// reg.tpl
$lang['Auto-Register Remote Users'] = 'Auto-Register Remote Users';
$lang['Create new Piwigo users when a Remote Users is unknown'] = 'Create new Piwigo users when a Remote Users is unknown';
$lang['Redirect auto-created users to the profile page'] = 'Redirect auto-created users to the profile page';
$lang['Notify admin when user auto-created'] = 'Notify admin when user auto-created';
$lang['Send notification to auto-created users'] = 'Send notification to auto-created users';
$lang['Notifications are suggested if Remote User passwords are not available, as a random password will be assigned.'] = 'Notifications are suggested if Remote User passwords are not available, as a random password will be assigned.';
$lang['NOTE: Email Domain required for notifications to work'] = 'NOTE: Email Domain required for notifications to work';
$lang['Auto-Created User Profile'] = 'Auto-Created User Profile';
$lang['Use Remote User password if available'] = 'Use Remote User password if available';
$lang['User password discovered from Server Password Variables if not empty.'] = 'User password discovered from Server Password Variables if not empty.';
$lang['NOTE: Current sync password overrides this!'] = 'NOTE: Current sync password overrides this!';
$lang['Disable random password if the Remote User password is blank'] = 'Disable random password if the Remote User password is blank';
$lang['Empty passwords when Fallback or Web APIs are enabled (now or in the future) are insecure.'] = 'Empty passwords when Fallback or Web APIs are enabled (now or in the future) are insecure.';
$lang['USE WITH CAUTION!'] = 'USE WITH CAUTION!';
$lang['Email Domain'] = 'Email Domain';
$lang['If not empty, append to Remote User to form email address.'] = 'If not empty, append to Remote User to form email address.';
$lang['User To Use As Default Profile'] = 'User To Use As Default Profile';
$lang['If empty, use global default user.'] = 'If empty, use global default user.';
$lang['User %s was not found'] = 'User %s was not found';
$lang['Default User Status'] = 'Default User Status';
?>

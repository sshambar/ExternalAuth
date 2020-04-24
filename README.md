# ExternalAuth Piwigo Plugin

## Introduction

The **External Authentication** plugin supports login based on the
identity provided by the webserver (aka the Remote User), which is
usually supplied via proxy servers or webserver modules (eg. "Basic
Auth").  It differs from the Piwigo "apache_authentication" option by
providing a greater degree of flexibility, including:

 - Control over which variable(s) are used for authentication
 - Piwigo login will always follow changes in (recognized) Remote Users
 - Native Piwigo logins possible when the Remote User is considered a guest
 - Option to copy webserver supplied passwords to Piwigo accounts
 - External login/logout URLs replacing or co-existing with native ones
 - Possibily to auto-register unknown Remote Users as new Piwigo users
 - Control over auto-registration profiles, passwords, status and notifications
 - Flexible list of Remote Users considered guests

_This plugin is incompatible with the `$conf['apache_authentication']`
option, and will auto-disable if it's set.

## Fallback Authentication

**Fallback Authentication** is optional, and permits native Piwigo
logins when the current Remote User is considered a guest.  NOTE: If
Remote User auto-registration is disabled, any Remote User unknown to
Piwigo is considered a guest.  If Fallback is disabled, Piwigo logins
will always match the current Remote User.

## Configuration

All configuration is stored in the database parameter 'externalauth',
as a serialized array with any values differing from the following
defaults:


Param           | Default Value  | Comment
--------------- | -------------  | -------------
global_enable   | false          | Enable plugin
debug           | false          | optional debug logging
remote_name     | { 'REMOTE_USER', 'REDIRECT_REMOTE_USER', 'PHP_AUTH_USER' } | $_SERVER name for remote_user
remote_guests   | { 'guest' }    | remote guest names
fallback        | true           | if remote_user is guest or unknown, allow regular login
remote_password | { 'PHP_AUTH_PW' } | $_SERVER name for remote password
sync_password   | false          | sync Piwigo password to Remote on login
webapi_also     | false          | also handle Web API urls (perhaps with proxy)
login_url       | ''             | URL for external login
login_label     | 'Site Login'   | label for external login
logout_url      | ''             | URL for external logout
logout_label    | 'Site Logout'  | label for external logout
register_new    | false          | if disabled, treat them as guests (so fallback can be used)
profile_new     | true           | redirect auto-created users to profile page
notify_admin_new | false         | notify admins on auto-created users (also requires global admin notify)
notify_new      | false          | send notifation to auto-created users
sync_password_new | true         | use Remote User password (if non-empty) for auto-created users
norand_password_new | false      | don't use random passwords if Remote User password blank (USE CAUTION!)
email_domain_new | ''            | email domain to use for auto-created users
default_new     | ''             | username of default user (empty to use regular default)
status_new      | ''             | initial status of auto-created users (empty for default)

## Troubleshooting

The plugin makes every effort to prevent un-intentional account
lockout, and is always disabled upon activation to permit
configuration before login enforcement is enabled (which may
immediately log the current session out!).

Debug logging can be enabled on the plugin's admin page, or by
setting `$conf['externalauth_debug'] = true`

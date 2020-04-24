{combine_script id='common' load='footer' path='admin/themes/default/js/common.js'}
{footer_script}
jQuery(document).ready(function() {
  jQuery(".showInfo").tipTip({
    'delay': 0,
    'fadeIn': 200,
    'fadeOut': 200,
    'maxWidth':'300px',
    'keepAlive':true,
    'activation':'click'
  });
});
{/footer_script}

<div class="titrePage">
  <h2>{'External Authentication Plugin'|translate}</h2>
</div>

<form method="post" action="{$F_ACTION}" class="properties">
  <input type="hidden" name="pwg_token" value="{$PWG_TOKEN}">
  <input type="hidden" name="action" value="enable">
  {if ! $EA.global_enable}
    <input type="hidden" name="EA_global_enable" value="1">
  {/if}
  <fieldset>
    <legend>{'Current Status'|translate}</legend>
    <ul>
      <li>
	{'Server Variable Used'|translate}: {if $EA_REMOTE_USER_VAR}<strong>{$EA_REMOTE_USER_VAR}</strong>{else}&lt;{'none'|translate}&gt;{/if}
      </li>
      <li>
	{'Remote User'|translate}: {if $EA_REMOTE_USER}<strong>{$EA_REMOTE_USER}</strong>{else}&lt;{'empty'|translate}&gt;{/if}{if $EA_REMOTE_IS_GUEST} <i>({'a guest'|translate})</i>{/if}
      </li>
      {if $EA_REMOTE_USER}
	<li>
	  {'Remote User is known to Piwigo'|translate}: <strong>{if $EA_REMOTE_UNKNOWN}{'No'|translate}{else}{'Yes'|translate}{/if}</strong>
	</li>
	<li>
	  {'Auto-Register Remote Users'|translate}: <strong>{if $EA.register_new}{'Yes'|translate}{else}{'No'|translate}{/if}</strong>
	</li>
      {/if}
    </ul>
    {if $EA_WOULD_LOGOUT}
      <p>
	<div class="badge">
	  <strong>{'CAUTION'|translate}:</strong>&nbsp;{'Remote User doesn\'t match your current login.'|translate} <strong>{'Enabling plugin will log you out!'|translate}</strong> ({'Try enabling Fallback'|translate})
	</div>
      </p>
    {/if}
    <p class="formButtons">
      <button name="submit" type="submit" class="buttonLike">
	{if $EA.global_enable}
	  <i class="icon-block"></i> {'Disable Plugin'|translate}
	{else}
	  <i class="icon-ok"></i> {'Enable Plugin'|translate}
	{/if}
      </button>
      {if $EA_WOULD_LOGOUT}
	&nbsp;<label class="font-checkbox">
	  <span class="icon-check"></span>
	  <input type="checkbox" name="confirm"/> {'I understand this action will immediately log me out'|translate}
	</label>
      {else}
	<input type="hidden" name="confirm" value="1">
      {/if}
    </p>
  </fieldset>
</form>
<form method="post" action="{$F_ACTION}" class="properties">
  <input type="hidden" name="pwg_token" value="{$PWG_TOKEN}">
  <input type="hidden" name="action" value="fallback">
  <input type="hidden" name="EA_fallback" value="{if ! $EA.fallback}1{/if}">

  <fieldset>
    <legend>{'Fallback Authentication'|translate}</legend>
    {if $EA.fallback}
      {'Disable Fallback Authentication, forcing login to always match the current Remote User'|translate}
    {else}
      {'Enable Fallback Authentication, allowing native logins when Remote User is a guest or unknown'|translate}
    {/if}
    {if $EA_FALLBACK_ACTIVE && $EA.global_enable}
      <p>
	<div class="badge">
	  <strong>{'CAUTION'|translate}:</strong>&nbsp;{'Remote User doesn\'t match your current login.'|translate} <strong>{'Disabling Fallback will log you out!'|translate}</strong>
	</div>
      </p>
    {/if}
    <p class="formButtons">
      <button name="submit" type="submit" class="buttonLike">
      {if $EA.fallback}
	<i class="icon-lock"></i> {'Disable Fallback'|translate}
      {else}
	<i class="icon-ccw"></i> {'Enable Fallback'|translate}
      {/if}
      </button>
      {if $EA_FALLBACK_ACTIVE && $EA.global_enable}
	&nbsp;<label class="font-checkbox">
	  <span class="icon-check"></span>
	  <input type="checkbox" name="confirm"/> {'I understand this action will immediately log me out'|translate}
	</label>
      {else}
	<input type="hidden" name="confirm" value="1">
      {/if}
    </p>
  </fieldset>
</form>

<form method="post" action="{$F_ACTION}" class="properties">
  <input type="hidden" name="pwg_token" value="{$PWG_TOKEN}">
  <input type="hidden" name="action" value="auth">
  <input type="hidden" name="confirm" value="1">
  <div id="configContent">

    <fieldset>
      <legend>{'Authentication Options'|translate}</legend>
      <ul>
	<li>
	  <label for="remote_name">{'Server Login Variables'|translate}</label>
	  <a class="icon-info-circled-1 showInfo" title="{'Comma separated list of $_SERVER variables searched to locate the Remote User\'s login (in order)'|translate}"></a>
	  <br>
	  {if $EA_REMOTE_USER_VAR && ! $EA.fallback}
	    <div class="badge">
	      <strong>{'CAUTION'|translate}:</strong> {'Since fallback is disabled, removing the variable in use (%s) will immediately log you out!'|translate:$EA_REMOTE_USER_VAR}
	    </div>
	  {/if}
	  <input type="text" maxlength="255" size="50" id="remote_name" name="EA_remote_name" value="{$EA.remote_name|escape}">
	</li>
	<li>
	  <label for="remote_guests">{'Remote Guests'|translate}</label>
	  <a class="icon-info-circled-1 showInfo" title="{'Comma separated list of Remote Users that are considered guests.'|translate} {'Empty or missing Remote Users are always guests.'|translate}"></a>
	  <br>
	  <input type="text" maxlength="255" size="50" id="remote_guests" name="EA_remote_guests" value="{$EA.remote_guests|escape}">
	</li>
	<li>
	  <label class="font-checkbox">
	    <span class="icon-check"></span>
	    <input type="checkbox" name="EA_webapi_also"{if $EA.webapi_also} checked="checked"{/if}/> {'Enforce on Web API requests'|translate}
	  </label>
	  <a class="icon-info-circled-1 showInfo" title="{'These requests use their own login API. This setting will only be useful if you have a custom-built client (or a specially configured proxy server).'|translate}"></a>
	   <strong>{'USE WITH CAUTION!'|translate}</strong>
	</li>
      </ul>
    </fieldset>
    <fieldset>
      <legend>{'Password Sync Options'|translate}</legend>
      <ul>
	<li>
	  <label class="font-checkbox">
	    <span class="icon-check"></span>
	    <input type="checkbox" name="EA_sync_password"{if $EA.sync_password} checked="checked"{/if}/> {'Sync Piwigo account password on login'|translate}
	  </label>
	  <a class="icon-info-circled-1 showInfo" title="{'Overwrite the Piwigo password with Remote User password (if not empty).'|translate}"></a>
	</li>
	<li>
	  <label for="remote_password">{'Server Password Variables'|translate}</label>
	  <a class="icon-info-circled-1 showInfo" title="{'Comma separated list of $_SERVER variables searched to locate the Remote User\'s password (in order).  The password is used for sync and user auto-creation.'|translate}"></a>
	  <br>
	  <input type="text" maxlength="255" size="50" id="remote_password" name="EA_remote_password" value="{$EA.remote_password|escape}">
	</li>
      </ul>
    </fieldset>
    <fieldset>
      <legend>{'Remote Authentication Links'|translate}</legend>
      <ul>
	<li>
	  <label for="login_url">{'Login URL'|translate}</label>
	  <a class="icon-info-circled-1 showInfo" title="{'If not empty, link is added to the menu when appropriate.'|translate} {'References to relative \'%(url)\' or absolute \'%(absurl)\' URLs can be included (will be URL escaped).'|translate}"></a>
	  <br>
	  <input type="text" maxlength="255" size="50" id="login_url" name="EA_login_url" value="{$EA.login_url|escape}">
	  <label for="login_label">{'Label'|translate}</label>
	  <a class="icon-info-circled-1 showInfo" title="{'Label will be translated if possible'|translate}"></a>
	  <input type="text" maxlength="25" size="10" id="login_label" name="EA_login_label" value="{$EA.login_label|escape}">
	</li>
	<li>
	  <label for="logout_url">{'Logout URL'|translate}</label>
	  <br>
	  <input type="text" maxlength="255" size="50" id="logout_url" name="EA_logout_url" value="{$EA.logout_url|escape}">
	  <label for="logout_label">{'Label'|translate}</label>
	  <a class="icon-info-circled-1 showInfo" title="{'Label will be translated if possible'|translate}"></a>
	  <input type="text" maxlength="25" size="10" id="logout_label" name="EA_logout_label" value="{$EA.logout_label|escape}">
	</li>
      </ul>
    </fieldset>
    <fieldset>
      <legend>{'Troubleshooting'|translate}</legend>
      <ul>
	<li>
	  <label class="font-checkbox">
	    <span class="icon-check"></span>
	    <input type="checkbox" name="EA_debug"{if $EA.debug} checked="checked"{/if}/> {'Enable plugin debug logging'|translate}
	  </label>
	  <a class="icon-info-circled-1 showInfo" title="{'Send debug messages to Piwigo logs.  Debug logging can also be enabled by setting %s'|translate:'$conf[\'externalauth_debug\']'}"></a>
	</li>
      </ul>
    </fieldset>
  </div>
  <p class="formButtons">
    <button name="submit" type="submit" class="buttonLike">
      <i class="icon-floppy"></i> {'Save Settings'|translate}
    </button>
  </p>
</form>

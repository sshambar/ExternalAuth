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

<form method="post" class="properties" action="{$F_ACTION}">
  <input type="hidden" name="pwg_token" value="{$PWG_TOKEN}">
  <input type="hidden" name="action" value="reg">
  <input type="hidden" name="confirm" value="1">
  <div id="configContent">
    <fieldset>
      <legend>{'Auto-Register Remote Users'|translate}</legend>
      <ul>
	<li>
	  <label class="font-checkbox">
	    <span class="icon-check"></span>
	    <input type="checkbox" name="EA_register_new"{if $EA.register_new} checked="checked"{/if}/> {'Create new Piwigo users when a Remote Users is unknown'|translate}
	  </label>
	</li>
	<li>
	  <label class="font-checkbox">
	    <span class="icon-check"></span>
	    <input type="checkbox" name="EA_profile_new"{if $EA.profile_new} checked="checked"{/if}/> {'Redirect auto-created users to the profile page'|translate}
	  </label>
	</li>
	<li>
	  <label class="font-checkbox">
	    <span class="icon-check"></span>
	    <input type="checkbox" name="EA_notify_admin_new"{if $EA.notify_admin_new} checked="checked"{/if}/> {'Notify admin when user auto-created'|translate}
	  </label>
	</li>
	<li>
	  <label class="font-checkbox">
	    <span class="icon-check"></span>
	    <input type="checkbox" name="EA_notify_new"{if $EA.notify_new} checked="checked"{/if}/> {'Send notification to auto-created users'|translate}
	  </label>
	  <a class="icon-info-circled-1 showInfo" title="{'Notifications are suggested if Remote User passwords are not available, as a random password will be assigned.'|translate}"></a>
	  {if ! $EA.email_domain_new}
	    {'NOTE: Email Domain required for notifications to work'|translate}
	  {/if}
	</li>
      </ul>
    </fieldset>
    <fieldset>
      <legend>{'Auto-Created User Profile'|translate}</legend>
      <ul>
	<li>
	  <label class="font-checkbox">
	    <span class="icon-check"></span>
	    <input type="checkbox" name="EA_sync_password_new"{if $EA.sync_password_new} checked="checked"{/if}/> {'Use Remote User password if available'|translate}
	  </label>
	  <a class="icon-info-circled-1 showInfo" title="{'User password discovered from Server Password Variables if not empty.'|translate}"></a>
	  {if $EA.sync_password}
	    <strong>{'NOTE: Current sync password overrides this!'|translate}</strong>
	  {/if}
	</li>
	<li>
	  <label class="font-checkbox">
	    <span class="icon-check"></span>
	    <input type="checkbox" name="EA_norand_password_new"{if $EA.norand_password_new} checked="checked"{/if}/> {'Disable random password if the Remote User password is blank'|translate}
	  </label>
	  <a class="icon-info-circled-1 showInfo" title="{'Empty passwords when Fallback or Web APIs are enabled (now or in the future) are insecure.'|translate}"></a>
	   <strong>{'USE WITH CAUTION!'|translate}</strong>
	</li>
	<li>
	  <label for="email_domain_new">{'Email Domain'|translate}</label>
	  <a class="icon-info-circled-1 showInfo" title="{'If not empty, append to Remote User to form email address.'|translate}"></a>
	  <br>
	  @<input type="text" maxlength="50" size="25" id="email_domain_new" name="EA_email_domain_new" value="{$EA.email_domain_new|escape}">
	</li>
	<li>
	  <label for="default_new">{'User To Use As Default Profile'|translate}</label>
	  <a class="icon-info-circled-1 showInfo" title="{'If empty, use global default user.'|translate}"></a>
	  <br>
	  <input type="text" maxlength="100" size="25" id="default_new" name="EA_default_new" value="{$EA.default_new|escape}">
	  {if $EA_DEFAULT_NEW_WARNING}
	    <strong>{'CAUTION'|translate}:</strong>{'User %s was not found'|translate:$EA.default_new}
	  {/if}
	</li>
	<li>
	  <label>{'Default User Status'|translate}</label>
	  <br>
          {html_options name=EA_status_new options=$EA_STATUS_OPTIONS selected=$EA.status_new}
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

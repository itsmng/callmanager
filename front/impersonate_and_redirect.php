<?php
/**
 * CallManager - Impersonate target user then redirect to standard ticket creation form
 *
 * GET/POST params:
 *  - caller_users_id (int) required: target user to impersonate
 *
 * Behavior:
 *  - Validates session login
 *  - Checks impersonation right and ability to impersonate the target user
 *  - Starts impersonation and redirects to /front/ticket.form.php
 *  - On failure, redirects to /front/ticket.form.php without impersonation and shows an INFO message
 */

define('GLPI_ROOT', dirname(dirname(dirname(__DIR__))));
include (GLPI_ROOT . "/inc/includes.php");
use GlpiPlugin\CallManager\PluginCallManagerConfig;

// Must be logged in
Session::checkLoginUser();

// Obtain the caller user ID from request
$caller_users_id = (isset($_REQUEST['caller_users_id']) && is_numeric($_REQUEST['caller_users_id'])) 
    ? (int)$_REQUEST['caller_users_id'] 
    : 0;

// Compute redirect URL depending on Formcreator configuration
$redirect_url = "/front/ticket.form.php";
$plugin = new Plugin();
if ($plugin->isActivated('formcreator')) {
   $form_id = PluginCallManagerConfig::get('formcreator_form_id', '');
   if (!empty($form_id)) {
      $redirect_url = "/plugins/formcreator/front/formdisplay.php?id=" . urlencode((string)$form_id);
   }
}

// Permission checks
if ($caller_users_id > 0 && Session::haveRight('impersonate', Session::IMPERSONATE) && Session::canImpersonate($caller_users_id)) {
   // Try impersonation
   if (Session::startImpersonating($caller_users_id)) {
      Html::redirect($redirect_url);
   } else {
      Session::addMessageAfterRedirect(__('Failed to start impersonation; opening ticket form without impersonation.', 'callmanager'), false, INFO);
      Html::redirect($redirect_url);
   }
} else {
   Session::addMessageAfterRedirect(__('You are not allowed to impersonate this user; opening ticket form without impersonation.', 'callmanager'), false, INFO);
   Html::redirect($redirect_url);
}

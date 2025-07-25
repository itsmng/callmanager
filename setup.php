<?php

global $CFG_GLPI;
// Version of the plugin (major.minor.bugfix)
define('CALLMANAGER_VERSION', '1.0.0');

define ('CALLMANAGER_ITSMNG_MIN_VERSION', '1.0');

// This code injects the autoloader for the plugin
$hostLoader = require __DIR__ . '/../../vendor/autoload.php';

$hostLoader->addPsr4(
    'GlpiPlugin\\CallManager\\',
    __DIR__ . '/src/'
);

use GlpiPlugin\CallManager\PluginCallManagerConfig;
use GlpiPlugin\CallManager\PluginCallManagerProfile;
use GlpiPlugin\CallManager\PluginCallManagerUser;

/**
 * Define the plugin's version and informations
 *
 * @return array [name, version, author, homepage, license, minGlpiVersion]
 */
function plugin_version_callmanager() {
   $requirements = [
      'name'           => 'Call Manager Plugin',
      'version'        => CALLMANAGER_VERSION,
      'author'         => 'ITSMNG Team',
      'homepage'       => 'https://github.com/itsmng/callmanager',
      'license'        => '<a href="../plugins/callmanager/LICENSE" target="_blank">GPLv3</a>',
   ];
   return $requirements;
}

/**
 * Initialize all classes and generic variables of the plugin
 */
function plugin_init_callmanager() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   // Set the plugin CSRF compliance (required since GLPI 0.84)
   $PLUGIN_HOOKS['csrf_compliant']['callmanager'] = true;

   $PLUGIN_HOOKS['add_javascript']['callmanager'] = [
      "/node_modules/preact/dist/preact.min.umd.js",
      "/node_modules/preact/hooks/dist/hooks.umd.js",
      "/node_modules/htm/dist/htm.umd.js",
   ];

   // Register profile rights
   Plugin::registerClass(PluginCallManagerProfile::class, ['addtabon' => Profile::class]);
   Plugin::registerClass(PluginCallManagerConfig::class, [
      'addtabon' => Config::class
   ]);
   Plugin::registerClass(PluginCallManagerUser::class, ['addtabon' => User::class]);
   
   $PLUGIN_HOOKS['change_profile']['callmanager'] = [PluginCallManagerProfile::class, 'changeProfile'];
   $PLUGIN_HOOKS['post_init']['callmanager'] = 'plugin_callmanager_postinit';

   if (Session::haveRight('plugin_callmanager_config', UPDATE)) {
       $PLUGIN_HOOKS['config_page']['callmanager'] = 'front/config.form.php';
   }
}

/**
 * Post init function
 */
function plugin_callmanager_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_add']['callmanager'] = [
      'User' => 'plugin_callmanager_user_add'
   ];
   
   $PLUGIN_HOOKS['item_update']['callmanager'] = [
      'User' => 'plugin_callmanager_user_update'
   ];
}

/**
 * Check plugin's prerequisites before installation
 *
 * @return boolean
 */
function callmanager_check_prerequisites() {
   $prerequisitesSuccess = true;

   if (version_compare(ITSM_VERSION, CALLMANAGER_ITSMNG_MIN_VERSION, 'lt')) {
      echo "This plugin requires ITSM >= " . CALLMANAGER_ITSMNG_MIN_VERSION . "<br>";
      $prerequisitesSuccess = false;
   }

   if (!is_readable(__DIR__ . '/vendor/autoload.php') || !is_file(__DIR__ . '/vendor/autoload.php')) {
      echo "Run composer install --no-dev in the plugin directory<br>";
      return false;
   }

   return $prerequisitesSuccess;
}

/**
 * Check plugin's config before activation (if needed)
 *
 * @param string $verbose Set true to show all messages (false by default)
 * @return boolean
 */
function callmanager_check_config($verbose = false) {
   if ($verbose) {
      echo "Checking plugin configuration<br>";
   }
   return true;
}

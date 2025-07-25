<?php

use GlpiPlugin\CallManager\PluginCallManagerConfig;
use GlpiPlugin\CallManager\PluginCallManagerProfile;
use GlpiPlugin\CallManager\PluginCallManagerUser;

function plugin_callmanager_install() {
   set_time_limit(900);
   ini_set('memory_limit', '2048M');

   $classesToInstall = [
      PluginCallManagerConfig::class,
      PluginCallManagerProfile::class,
      PluginCallManagerUser::class,
   ];

   echo "<center>";
   echo "<table class='tab_cadre_fixe'>";
   echo "<tr><th>".__("MySQL tables installation", "callmanager")."<th></tr>";

   echo "<tr class='tab_bg_1'>";
   echo "<td align='center'>";

   //install
   foreach ($classesToInstall as $class) {
      if (isPluginItemType($class)) {
         if (!call_user_func([$class, 'install'])) {
            return false;
         }
      }
   }

   echo "</td>";
   echo "</tr>";
   echo "</table></center>";

   return true;
}

function plugin_callmanager_uninstall() {
   echo "<center>";
   echo "<table class='tab_cadre_fixe'>";
   echo "<tr><th>".__("MySQL tables uninstallation", "callmanager")."<th></tr>";

   echo "<tr class='tab_bg_1'>";
   echo "<td align='center'>";

   $classesToUninstall = [
      PluginCallManagerUser::class,
      PluginCallManagerProfile::class,
      PluginCallManagerConfig::class,
   ];

   foreach ($classesToUninstall as $class) {
      if (isPluginItemType($class)) {
         if (!call_user_func([$class, 'uninstall'])) {
            return false;
         }
      }
   }

   echo "</td>";
   echo "</tr>";
   echo "</table></center>";

   return true;
}

/**
 * Hook function called after a user is added
 *
 * @param CommonDBTM $user
 * @return void
 */
function plugin_callmanager_user_add(CommonDBTM $user) {
   // Nothing to do here, the RIO number will be added when the form is saved
}

/**
 * Hook function called after a user is updated
 *
 * @param CommonDBTM $user
 * @return void
 */
function plugin_callmanager_user_update(CommonDBTM $user) {
   // Nothing to do here, the RIO number will be added when the form is saved
}

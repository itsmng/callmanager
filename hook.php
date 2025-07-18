<?php

use Itsmng\Plugin\CallManager\PluginCallManagerConfig;
use Itsmng\Plugin\CallManager\PluginCallManagerProfile;

function plugin_callmanager_install() {
   set_time_limit(900);
   ini_set('memory_limit', '2048M');

   $classesToInstall = [
      PluginCallManagerConfig::class,
      PluginCallManagerProfile::class,
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
      PluginCallManagerConfig::class,
      PluginCallManagerProfile::class,
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

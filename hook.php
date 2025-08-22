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
function plugin_callmanager_item_add_User(User $user) {
   // Check if this user creation came from Call Manager with a RIO number
   if (isset($_POST['callmanager_rio']) && !empty($_POST['callmanager_rio'])) {
      $rio = $_POST['callmanager_rio'];
      $storage = PluginCallManagerConfig::get('rio_storage_method', 'custom_table');
      
      if ($storage === 'custom_table') {
         // Store in plugin dedicated table
         PluginCallManagerUser::updateRIO([
            'users_id' => $user->getID(),
            'rio_number' => $rio
         ]);
      } else if ($storage === 'name') {
         // The name field should already be pre-filled by JavaScript
         // but let's make sure it's set correctly
         if (empty($user->fields['name']) || $user->fields['name'] !== $rio) {
            $user->update([
               'id' => $user->getID(),
               'name' => $rio
            ]);
         }
      } else if ($storage === 'registration_number') {
         // Update the user's registration_number field with RIO
         $user->update([
            'id' => $user->getID(),
            'registration_number' => $rio
         ]);
      }
      
      // Redirect back to Call Manager search with the new user
      global $CFG_GLPI;
      Html::redirect($CFG_GLPI["root_doc"] . "/plugins/callmanager/front/callmanager.php?rio=" . urlencode($rio));
   }
}

/**
 * Alternative hook using the generic item_add hook
 */
function plugin_callmanager_item_add($item) {
   if ($item instanceof User) {
      plugin_callmanager_item_add_User($item);
   }
}

/**
 * Hook function called after a user is updated
 */
function plugin_callmanager_item_update_User(User $user) {
   // Nothing specific to do here for updates
}

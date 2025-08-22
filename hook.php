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
   $rio = null;
   if (isset($_POST['callmanager_rio']) && !empty($_POST['callmanager_rio'])) {
      $rio = $_POST['callmanager_rio'];
   } 
   
   else if (isset($_GET['rio']) && !empty($_GET['rio'])) {
      $rio = $_GET['rio'];
   }
   
   if ($rio) {
      $storage = PluginCallManagerConfig::get('rio_storage_method', 'custom_table');
      
      if ($storage === 'custom_table') {
         PluginCallManagerUser::updateRIO([
            'users_id' => $user->getID(),
            'rio_number' => $rio
         ]);
      } else if ($storage === 'name') {
         if (empty($user->fields['name']) || $user->fields['name'] !== $rio) {
            $user->update([
               'id' => $user->getID(),
               'name' => $rio
            ]);
         }
      } else if ($storage === 'registration_number') {
         if (empty($user->fields['registration_number']) || $user->fields['registration_number'] !== $rio) {
            $user->update([
               'id' => $user->getID(),
               'registration_number' => $rio
            ]);
         }
      }
      
      // Redirect back to Call Manager search with the new user
      global $CFG_GLPI;
      Html::redirect($CFG_GLPI["root_doc"] . "/plugins/callmanager/front/callmanager.php?rio=" . urlencode($rio));
   }
}

/**
 * Hook function called before a user is added
 *
 * @param CommonDBTM $user
 * @return void
 */
function plugin_callmanager_pre_item_add_user(User $user) {
   $rio = null;
   if (isset($_POST['callmanager_rio']) && !empty($_POST['callmanager_rio'])) {
      $rio = $_POST['callmanager_rio'];
   } 
   else if (isset($_GET['rio']) && !empty($_GET['rio'])) {
      $rio = $_GET['rio'];
   }
   
   if ($rio) {
      $storage = PluginCallManagerConfig::get('rio_storage_method', 'custom_table');
      
      // Pre-fill fields during user creation
      if ($storage === 'name' && (empty($user->fields['name']) || $user->fields['name'] !== $rio)) {
         $user->fields['name'] = $rio;
      } else if ($storage === 'registration_number' && (empty($user->fields['registration_number']) || $user->fields['registration_number'] !== $rio)) {
         $user->fields['registration_number'] = $rio;
      }
   }
}

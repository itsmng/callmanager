<?php

namespace GlpiPlugin\CallManager;

use CommonDBTM;
use CommonGLPI;
use User;
use Html;
use Plugin;

class PluginCallManagerUser extends CommonDBTM {

    /**
     * Get the table name for this class
     * @return string
     */
    static function getTable($classname = null) {
        return "glpi_plugin_callmanager_users";
    }

    static function install() {
        global $DB;

        $table = self::getTable();

        if (!$DB->tableExists($table)) {
            $query = <<<SQL
              CREATE TABLE `$table` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `users_id` int(11) NOT NULL COMMENT 'RELATION to glpi_users (id)',
                  `rio_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `date_creation` timestamp NULL DEFAULT NULL,
                  `date_mod` timestamp NULL DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `unicity` (`users_id`),
                  KEY `users_id` (`users_id`)
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            SQL;

            $DB->queryOrDie($query, $DB->error());
        }
        return true;
    }

    static function uninstall() {
        global $DB;

        $table = self::getTable();

        if ($DB->tableExists($table)) {
            $query = <<<SQL
              DROP TABLE `$table`
            SQL;

            $DB->queryOrDie($query, $DB->error());
        }

        return true;
    }

    /**
     * getTabNameForItem
     *
     * @param  object $item
     * @param  int $withtemplate
     * @return string
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if ($item->getType() == User::class) {
            return __('Call Manager', 'callmanager');
        }

        return '';
    }

    /**
     * displayTabContentForItem
     *
     * @param  object $item
     * @param  int $tabnum
     * @param  int $withtemplate
     * @return boolean
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        if ($item->getType() == User::class && $item instanceof User) {
            self::showRIOForm($item);
        }

        return true;
    }

    /**
     * Show RIO number form
     *
     * @param User $user
     * @return void
     */
    static function showRIOForm(User $user) {
        global $CFG_GLPI;

        $ID = $user->getID();
        $canedit = $user->can($ID, UPDATE);

        $rio_data = new self();
        if (!$rio_data->getFromDBByCrit(['users_id' => $ID])) {
            $rio_data->fields['rio_number'] = '';
        }

        echo "<div class='spaced'>";
        echo "<div class='center'>";

        if ($canedit) {
            echo "<form method='post' action='" . Plugin::getWebDir('callmanager') . "/front/user.form.php'>";
        }

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'>";
        echo "<th colspan='2'>" . __('Call Manager', 'callmanager') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<td width='30%'><strong>" . __('RIO Number', 'callmanager') . "</strong></td>";
        echo "<td>";
        
        if ($canedit) {
            echo Html::input('rio_number', [
                'value' => $rio_data->fields['rio_number'] ?? '',
                'size' => 20,
                'maxlength' => 20,
                'placeholder' => 'Ex: 1234567890'
            ]);
        } else {
            echo ($rio_data->fields['rio_number'] ?? __('None', 'callmanager'));
        }
        
        echo "</td>";
        echo "</tr>";

        if (!empty($rio_data->fields['rio_number'])) {
            echo "<tr class='tab_bg_2'>";
            echo "<td><strong>URL Call Manager</strong></td>";
            echo "<td>";
            $rio_url = $CFG_GLPI['root_doc'] . "/plugins/callmanager/front/callmanager.php?rio=" . 
                       urlencode($rio_data->fields['rio_number']);
            echo "<a href='$rio_url' target='_blank'>$rio_url</a>";
            echo "</td>";
            echo "</tr>";
        }

        if ($canedit) {
            echo "<tr class='tab_bg_2'>";
            echo "<td colspan='2' class='center'>";
            echo Html::hidden('users_id', ['value' => $ID]);
            echo Html::submit(_sx('button', 'Save'), [
                'name'  => 'update_rio',
                'class' => 'btn btn-primary'
            ]);
            echo "</td>";
            echo "</tr>";
        }

        echo "</table>";

        if ($canedit) {
            Html::closeForm();
        }

        echo "</div>";
        echo "</div>";

        if (!empty($rio_data->fields['rio_number'])) {
            echo "<script>
                console.log('Call Manager - RIO configuré: " . $rio_data->fields['rio_number'] . "');
                console.log('Test API: /~leo/itsm-ng/plugins/callmanager/api.php/users/" . $rio_data->fields['rio_number'] . "');
            </script>";
        }
    }
    /**
     * Update RIO number for user
     *
     * @param array $input
     * @return int
     */
    static function updateRIO($input) {
        global $DB;

        $users_id = $input['users_id'];
        $rio_number = $input['rio_number'] ?? '';

        $rio_data = new self();
        
        if ($rio_data->getFromDBByCrit(['users_id' => $users_id])) {
            // Update existing record
            $rio_data->update([
                'id' => $rio_data->getID(),
                'rio_number' => $rio_number,
                'date_mod' => $_SESSION["glpi_currenttime"]
            ]);
        } else {
            // Create new record
            $rio_data->add([
                'users_id' => $users_id,
                'rio_number' => $rio_number,
                'date_creation' => $_SESSION["glpi_currenttime"],
                'date_mod' => $_SESSION["glpi_currenttime"]
            ]);
        }

        return $rio_data->getID();
    }
    
    /**
     * Get RIO number for a user
     *
     * @param int $users_id
     * @return string|null
     */
    static function getRIOForUser($users_id) {
        $rio_data = new self();
        if ($rio_data->getFromDBByCrit(['users_id' => $users_id])) {
            return $rio_data->fields['rio_number'] ?? null;
        }
        return null;
    }

    static function getUsersByRio($rio) {
        global $DB;

        $result = $DB->request([
            'SELECT' => ['u.id', 'u.name'],
            'FROM' => 'glpi_users AS u',
            'JOIN' => [
                self::getTable() . ' AS pcu' => [
                    'ON' => [
                        'pcu' => 'users_id',
                        'u' => 'id'
                    ]
                ]
            ],
            'WHERE' => [
                'pcu.rio_number' => $rio
            ]
        ]);

        $users = [];
        foreach ($result as $row) {
            $users[] = ['id' => $row['id'], 'name' => $row['name']];
        }

        return $users;
    }
}

<?php

namespace GlpiPlugin\CallManager;

use CommonDBTM;
use CommonGLPI;
use User;
use Html;
use Plugin;

class PluginCallManagerUser extends CommonDBTM {

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

        echo "<div>";
        echo "<div>";

        if ($canedit) {
            echo "<form method='post' action='" . Plugin::getWebDir('callmanager') . "/front/user.form.php'>";
        }

        echo "<div class='form-group row'>";
        echo "<label class='col-sm-2 col-form-label'>" . __('RIO Number', 'callmanager') . "</label>";
        echo "<div class='col-sm-10'>";
        
        if ($canedit) {
            echo Html::input('rio_number', [
                'value' => $rio_data->fields['rio_number'] ?? '',
                'class' => 'form-control'
            ]);
        } else {
            echo "<div class='form-control-plaintext'>" . 
                 ($rio_data->fields['rio_number'] ?? __('None')) . 
                 "</div>";
        }
        
        echo "</div>";
        echo "</div>";

        if ($canedit) {
            echo Html::hidden('users_id', ['value' => $ID]);
            echo "<div class='form-group row'>";
            echo "<div class='col-sm-12 text-center'>";
            echo Html::submit(_sx('button', 'Save'), [
                'name'  => 'update_rio',
                'class' => 'btn btn-secondary'
            ]);
            echo "</div>";
            echo "</div>";
            
            Html::closeForm();
        }

        echo "</div>";
        echo "</div>";
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
                'glpi_plugin_callmanager_plugincallmanagerusers AS pcu' => [
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

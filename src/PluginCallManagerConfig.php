<?php

namespace GlpiPlugin\CallManager;

use CommonDBTM;
use Plugin;
use Html;

class PluginCallManagerConfig extends CommonDBTM {

    /**
     * Get the table name for this class
     * @return string
     */
    static function getTable($classname = null) {
        return "glpi_plugin_callmanager_configs";
    }

    static function install() {
        global $DB;

        $table = self::getTable();

        if (!$DB->tableExists($table)) {
            $query = <<<SQL
              CREATE TABLE `$table` (
                  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'RELATION to glpi_profiles (id)' ,
                  `name` VARCHAR(255) collate utf8_unicode_ci NOT NULL,
                  `value` TEXT collate utf8_unicode_ci default NULL,
                  PRIMARY KEY (`id`)
              ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            SQL;

            $DB->queryOrDie($query, $DB->error());
        }

        // Ensure default configuration rows exist
        // Default: store RIO in the plugin dedicated table (custom tab)
        if (!countElementsInTable($table, ['name' => 'rio_storage_method'])) {
            $DB->insert($table, [
                'name'  => 'rio_storage_method',
                'value' => 'custom_table',
            ]);
        }
        // Default: no Formcreator form selected
        if (!countElementsInTable($table, ['name' => 'formcreator_form_id'])) {
            $DB->insert($table, [
                'name'  => 'formcreator_form_id',
                'value' => ''
            ]);
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

    static private function getConfigValues() {
        global $DB;

        $table = self::getTable();

        $query = <<<SQL
          SELECT name, value from $table
        SQL;

        $results = iterator_to_array($DB->query($query));

        foreach($results as $id => $result) {
            $results[$result['name']] = $result['value'];
            unset($results[$id]);
        }
        return $results;
    }

    static function updateConfigValues($values) {
        global $DB;

        $table = self::getTable();
        $allowed = self::getAllowedConfigKeys();

        foreach ($allowed as $key) {
            if (!array_key_exists($key, $values)) {
                continue;
            }

            $val = $values[$key];

            // sanitize values for specific keys
            if ($key === 'rio_storage_method') {
                $allowedVals = ['custom_table', 'name', 'registration_number'];
                if (!in_array($val, $allowedVals, true)) {
                    $val = 'custom_table';
                }
            }

            if (countElementsInTable($table, ['name' => $key])) {
                $DB->update($table, ['value' => $val], ['name' => $key]);
            } else {
                $DB->insert($table, ['name' => $key, 'value' => $val]);
            }
        }
        return true;
    }

    /**
     * Allowed config keys maintained by this plugin.
     * @return string[]
     */
    static function getAllowedConfigKeys() {
        return [
            'rio_storage_method',
            'formcreator_form_id',
        ];
    }

    /**
     * Get a configuration value.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed|null
     */
    static function get(string $name, $default = null) {
        global $DB;
        $table = self::getTable();
        $res = $DB->request([
            'SELECT' => ['value'],
            'FROM'   => $table,
            'WHERE'  => ['name' => $name]
        ]);
        foreach ($res as $row) {
            return $row['value'];
        }
        return $default;
    }


    /**
     * Displays the configuration page for the plugin
     *
     * @return void
     */
    public function showConfigForm() {
        // Current value with default
        $current = self::get('rio_storage_method', 'custom_table');
        $currentFormId = self::get('formcreator_form_id', '');

        echo "<div class='spaced'>";
        echo "<div class='center'>";
        echo "<form method='post' action='" . Plugin::getWebDir('callmanager') . "/front/config.form.php'>";

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_1'><th colspan='2'>" . __('Call Manager settings', 'callmanager') . "</th></tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<td width='35%'><strong>" . __('RIO storage method', 'callmanager') . "</strong></td>";
        echo "<td>";
        echo "<select name='rio_storage_method' class='form-control'>";
        $options = [
            'custom_table' => __('Plugin dedicated table (custom user tab)', 'callmanager'),
            'name' => __('User login (name)', 'callmanager'),
            'registration_number' => __('Registration number', 'callmanager'),
        ];
        foreach ($options as $value => $label) {
            $selected = $current === $value ? " selected" : "";
            echo "<option value='" . Html::entities_deep($value) . "'{$selected}>" . Html::entities_deep($label) . "</option>";
        }
        echo "</select>";
        echo "</td>";
        echo "</tr>";

        // Formcreator integration row
        echo "<tr class='tab_bg_2'>";
        echo "<td width='35%'><strong>" . __('Formcreator form', 'callmanager') . "</strong></td>";
        echo "<td>";
        $plugin = new Plugin();
        global $DB;
        if ($plugin->isActivated('formcreator') && $DB->tableExists('glpi_plugin_formcreator_forms')) {
            $forms = $DB->request([
                'SELECT' => ['id', 'name'],
                'FROM'   => 'glpi_plugin_formcreator_forms',
                'ORDER'  => 'name ASC'
            ]);
            echo "<select name='formcreator_form_id' class='form-control'>";
            $noneSelected = ($currentFormId === '' || (string)$currentFormId === '0') ? " selected" : "";
            echo "<option value=''{$noneSelected}>" . __('None', 'callmanager') . "</option>";
            foreach ($forms as $form) {
                $fid = (int)$form['id'];
                $fname = Html::entities_deep($form['name']);
                $sel = ((string)$currentFormId === (string)$fid) ? " selected" : "";
                echo "<option value='{$fid}'{$sel}>{$fname}</option>";
            }
            echo "</select>";
        } else {
            echo "<input type='text' class='form-control' value='" . Html::entities_deep(__('Formcreator plugin not available', 'callmanager')) . "' disabled>";
            echo Html::hidden('formcreator_form_id', ['value' => '']);
        }
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<td colspan='2' class='center'>";
        echo Html::submit(_sx('button', 'Save'), [
            'name'  => 'update',
            'class' => 'btn btn-primary'
        ]);
        echo "</td>";
        echo "</tr>";

        echo "</table>";
        Html::closeForm();
        echo "</div>";
        echo "</div>";
    }
}

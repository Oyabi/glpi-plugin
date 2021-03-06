<?php
/**
 * LICENSE
 *
 * Copyright © 2016-2018 Teclib'
 * Copyright © 2010-2018 by the FusionInventory Development Team.
 *
 * This file is part of Flyve MDM Plugin for GLPI.
 *
 * Flyve MDM Plugin for GLPI is a subproject of Flyve MDM. Flyve MDM is a mobile
 * device management software.
 *
 * Flyve MDM Plugin for GLPI is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * Flyve MDM Plugin for GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License
 * along with Flyve MDM Plugin for GLPI. If not, see http://www.gnu.org/licenses/.
 * ------------------------------------------------------------------------------
 * @author    Thierry Bugier
 * @copyright Copyright © 2018 Teclib
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/flyve-mdm/glpi-plugin
 * @link      https://flyve-mdm.com/
 * ------------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginFlyvemdmCommon
{
   const SEMVER_VERSION_REGEX = '#\bv?(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[\da-z\-]+(?:\.[\da-z\-]+)*)?(?:\+[\da-z\-]+(?:\.[\da-z\-]+)*)?\b#i';


   /**
    * Convert int size to GiB
    * @param float $size
    * @return string format numeric
    */
   public static function convertToGiB($size) {
      $units = ['KiB', 'MiB', 'GiB', 'TiB'];
      $unit = 'B';

      while ($size > 1024 && count($units) > 0) {
         $size = $size / 1024;
         $unit = array_shift($units);
      }
      $size = Html::formatNumber($size, false, 2);
      return "$size $unit";
   }

   /**
    * Display massive actions
    * @param array $massiveactionparams
    * @return string an HTML
    */
   public static function getMassiveActions($massiveactionparams) {
      ob_start();
      Html::showMassiveActions($massiveactionparams);
      $html = ob_get_clean();

      return $html;
   }

   /**
    * Return an array of values from enum fields
    * @param string $table name
    * @param string $field name
    * @return array
    */
   static function getEnumValues($table, $field) {
      global $DB;

      $enum = [];
      if ($res = $DB->query( "SHOW COLUMNS FROM `$table` WHERE Field = '$field'" )) {
         $data = $DB->fetch_array($res);
         $type = $data['Type'];
         $matches = null;
         preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
         if (isset($matches[1])) {
            $enum = explode("','", $matches[1]);
         }
      }

      return $enum;
   }

   /**
    * @return string pseudo-random UUID version 4
    */
   public static function generateUUID() {
      return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

         // 32 bits for "time_low"
         mt_rand(0, 0xffff), mt_rand(0, 0xffff),

         // 16 bits for "time_mid"
         mt_rand(0, 0xffff),

         // 16 bits for "time_hi_and_version",
         // four most significant bits holds version number 4
         mt_rand(0, 0x0fff) | 0x4000,

         // 16 bits, 8 bits for "clk_seq_hi_res",
         // 8 bits for "clk_seq_low",
         // two most significant bits holds zero and one for variant DCE1.1
         mt_rand(0, 0x3fff) | 0x8000,

         // 48 bits for "node"
         mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
      );
   }

   /**
    * delete a directory and its content recursive
    * @param string $dir
    * @return bool
    */
   public static function recursiveRmdir($dir) {
      if (!file_exists($dir)) {
         return true;
      }
      if (!is_dir($dir)) {
         return unlink($dir);
      }
      $dirContent = scandir($dir);
      foreach ($dirContent as $item) {
         if ($item == '.' || $item == '..') {
            continue;
         }
         if (!self::recursiveRmdir($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
         }
      }
      return rmdir($dir);
   }
}
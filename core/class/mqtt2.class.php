<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class mqtt2 extends eqLogic {
   /*     * *************************Attributs****************************** */


   /*     * ***********************Methode static*************************** */

   public static function generateCertificates() {
      $path = __DIR__ . '/../../data/ssl';
      if (!file_exists($path)) {
         mkdir($path);
      }
      if (!file_exists($path . '/mosq-ca.key')) {
         shell_exec('openssl genrsa -out ' . $path . '/mosq-ca.key 2048');
      }
      if (!file_exists($path . '/mosq-ca.crt')) {
         shell_exec('openssl req -new -x509 -days 3650 -subj "/C=FR/ST=Paris/L=Paris/O=jeedom/CN=mosquitto_server" -key ' . $path . '/mosq-ca.key -out ' . $path . '/mosq-ca.crt');
      }
      if (!file_exists($path . '/mosq-serv.key')) {
         shell_exec('openssl genrsa -out ' . $path . '/mosq-serv.key 2048');
      }
      if (!file_exists($path . '/mosq-serv.csr')) {
         shell_exec('openssl req -new -subj "/C=FR/ST=Paris/L=Paris/O=jeedom/CN=mosquitto_server" -key ' . $path . '/mosq-serv.key -out ' . $path . '/mosq-serv.csr');
      }
      if (!file_exists($path . '/mosq-serv.crt')) {
         shell_exec('openssl x509 -req -in ' . $path . '/mosq-serv.csr -CA ' . $path . '/mosq-ca.crt -CAkey ' . $path . '/mosq-ca.key -CAcreateserial -out ' . $path . '/mosq-serv.crt -days 3650 -sha256');
      }
      config::save('ssl::ca', trim(file_get_contents($path . '/mosq-ca.crt')), 'mqtt2');
   }

   public static function installMosquitto() {
      self::generateCertificates();
      $compose = file_get_contents(__DIR__ . '/../../resources/docker_compose.yaml');
      $compose = str_replace('#jeedom_path#', realpath(__DIR__ . '/../../../../'), $compose);
      $docker = self::byLogicalId('1::mqtt2_mosquitto', 'docker2');
      if (!is_object($docker)) {
         $docker = new docker2();
      }
      $docker->setName('mqtt2_mosquitto');
      $docker->setIsEnable(1);
      $docker->setEqType_name('docker2');
      $docker->setConfiguration('name', 'mqtt2_mosquitto');
      $docker->setConfiguration('docker_number', 1);
      $docker->setConfiguration('create::mode', 'jeedom_compose');
      $docker->setConfiguration('create::compose', $compose);
      $docker->setIsEnable(1);
      $docker->save();
      $docker->rm();
      sleep(5);
      $docker->create();
      docker2::pull();
   }


   /*     * *********************Méthodes d'instance************************* */

   /*     * **********************Getteur Setteur*************************** */
}

class mqtt2Cmd extends cmd {
   /*     * *************************Attributs****************************** */


   /*     * ***********************Methode static*************************** */


   /*     * *********************Methode d'instance************************* */


   public function execute($_options = array()) {
   }

   /*     * **********************Getteur Setteur*************************** */
}

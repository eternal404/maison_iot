<?php
//fonctions communes à cron et ajax

//cette fonction est appelée par le handler AJAX pour renvoyer au relais de la chaudière les instructions (on/off).
function retourner_directive_chaudiere(){
$bdd = new bdd();
$resultat = $bdd->query('SELECT directive_chaudiere FROM thermostat');
$res = $resultat->fetchArray(SQLITE3_ASSOC);
$directive_chaudiere = $res['directive_chaudiere'];
$bdd->close(); unset($bdd);
$reponse = json_encode(array("directive" => $directive_chaudiere)); //la directive (on ou off) retournée au relais de la chaudière
echo $reponse;
}

//mise à jour des valeurs pour l'interface utilisateur et le thermostat-cron
function mode_chaudiere_active(){
activer_chaudiere();
$bdd = new bdd();
$bdd->query('UPDATE mode_chaudiere SET mode = "active"');
$bdd->close(); unset($bdd);
}

function mode_chaudiere_inactive(){
desactiver_chaudiere();
$bdd = new bdd();
$bdd->query('UPDATE mode_chaudiere SET mode = "inactive"');
$bdd->close(); unset($bdd);
}

function mode_chaudiere_automatique($temperature_cible){
$bdd = new bdd();
$bdd->query('UPDATE mode_chaudiere SET mode = "auto", temperature_cible="'.$temperature_cible.'"');
$bdd->close(); unset($bdd);
}


//mise à jour de la valeur (on/off) qui sera récupérée par le relais de la chaudière
function activer_chaudiere(){
$bdd = new bdd();
$bdd->query('UPDATE thermostat SET directive_chaudiere="on" WHERE `_rowid_`=1');
$bdd->close(); unset($bdd);
}

//mise à jour de la valeur (on/off) qui sera récupérée par le relais de la chaudière
function desactiver_chaudiere(){
$bdd = new bdd();
$bdd->query('UPDATE thermostat SET directive_chaudiere="off" WHERE `_rowid_`=1');
$bdd->close(); unset($bdd);
}

//insère dans l'historique les dernières températures renvoyées par les sondes.
function recuperer_temperature($emplacement, $temperature){
$bdd = new bdd();
$bdd->query('INSERT INTO historique_temperatures(emplacement, temperature) VALUES ("'.$emplacement.'",'.$temperature.')');
$bdd->close(); unset($bdd);
}


//OBSOLÈTE: envoie une commande au relais USB (à adapter pour les autres périphériques)
//function envoyer_commande_serial($commande){
// include "libs/php_serial.class.php";
//
//// Let's start the class
//$serial = new phpSerial;
//
//// First we must specify the device. This works on both linux and windows (if
//// your linux serial device is /dev/ttyS0 for COM1, etc)
//$serial->deviceSet("/dev/cu.usbserial-A501GS37");
//$serial->confBaudRate(9600);
//$serial->confStopBits(1);
//$serial->confCharacterLength(8);
//
//$serial->deviceOpen();
//
//$serial->sendMessage($commande);
//
//$serial->deviceClose();
//}


//connexion à la bdd
class bdd extends SQLite3
   {
      function __construct()
      {
         $this->open('bdd.sqlite');
        $this->busyTimeout(50);
      }
   }
   $db = new bdd();
   if(!$db){
      echo $db->lastErrorMsg();
   } else {
      //echo "Opened database successfully\n";
   }


?>

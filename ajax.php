<?php


//gère les requêtes AJAX

//fonctions communes à ajax et cron
require('fonctions.php');

// Set default timezone
date_default_timezone_set('UTC+1');


//ici, le parseur AJAX, qui appelle les fonctions en fonction de l'action demandée
$action = $_GET["action"];

if (isset($action)){

switch($action){
    case "obtenir_statut_temperature_et_chaudiere":
    retourner_statut_temperature_et_chaudiere(); //retourne les données en JSON
    break;

    case "changer_mode_chaudiere":
    changer_mode_chaudiere($_GET['mode'], $_GET['temperature_cible']);
    break;

    case "obtenir_directive_chaudiere": //appelée par le relais de contrôle de la chaudière à intervalles réguliers, pour qu'il sache s'il doit s'ouvrir ou se fermer
    retourner_directive_chaudiere(); //retourne une réponse (on/off) en JSON
    break;

    case "donner_temperature": //appel effectué par les sondes de température
    recuperer_temperature($_GET['emplacement'], $_GET['temperature']); //"emplacement" est une valeur de 0 à x, 1=salon, 0=chambre_youri,...
    break;

    default:
    echo "Type d'action inconnue.";
    break;

}




}else{ //si argument "action" non défini
    echo "Requête inconnue.";
}




function retourner_statut_temperature_et_chaudiere() {

$bdd = new bdd();

//recupération de l'historique des températures
$historique_temperatures = array();
$resultat = $bdd->query('SELECT timestamp, temperature, nom FROM historique_temperatures, emplacements where emplacements.id = historique_temperatures.emplacement ORDER BY timestamp LIMIT 10');

            $i=0;
             while($res = $resultat->fetchArray(SQLITE3_ASSOC)){

             if(!isset($res['timestamp'])) continue;

                $historique_temperatures[$i]['timestamp'] = $res['timestamp'];
                $historique_temperatures[$i]['temperature'] = $res['temperature'];
                $historique_temperatures[$i]['emplacement'] = $res['nom'];

              $i++;

          }
 $bdd->close(); unset($bdd);

//récupération des  températures actuelles
$temperatures_actuelles = end($historique_temperatures);


//récupération du mode de la chaudière et de la température cible

$bdd = new bdd();
$resultat = $bdd->query('SELECT mode, temperature_cible FROM mode_chaudiere LIMIT 1');
$res = $resultat->fetchArray(SQLITE3_ASSOC);
$mode_chaudiere = $res['mode'];
$temperature_cible = $res['temperature_cible'];
$bdd->close(); unset($bdd);


//envoi de la réponse en JSON
$arr = array('temperature' => array(
                                        'salon' => 42),
                'historique' => array(
                                        'salon' => $historique_temperatures

                                        ),
                'mode_chaudiere' => $mode_chaudiere,
                'temperature_cible' => $temperature_cible
                );
    echo json_encode($arr);

}


function changer_mode_chaudiere($mode, $temperature_cible){
  switch($mode){
   case "active":
      mode_chaudiere_active();
      break;
    case "inactive":
      mode_chaudiere_inactive();
      break;
    case "auto":
      mode_chaudiere_automatique($temperature_cible);
      break;
  }

}






?>

<?php
//tâches exécutées à intervalles réguliers

//fonctions communes à ajax et cron
require('fonctions.php');

//on regarde dans quel mode on est. si c'est en manuel, pas besoin de toucher au thermostat
//récupération du mode de la chaudière et de la température cible
$bdd = new bdd();
$resultat = $bdd->query('SELECT mode, temperature_cible FROM mode_chaudiere LIMIT 1');
$res = $resultat->fetchArray(SQLITE3_ASSOC);
$mode_chaudiere = $res['mode'];
$temperature_cible = $res['temperature_cible'];
$bdd->close(); unset($bdd);

if ($mode_chaudiere == "auto"){
     echo "La chaudière est en mode thermostat/auto.";
    thermostat($temperature_moyenne, $temperature_cible);
}else{
    echo "La chaudière est en mode manuel.";
}



//activer ou désactiver la chaudière en fonction de la température moyenne et de la température cible
function thermostat ($temperature_moyenne, $temperature_cible){
//obtenir la température de chaque pièce et en faire la moyenne
//print_r($bdd->version());
$bdd = new bdd();

$resultat = $bdd->query('SELECT timestamp, temperature, nom FROM historique_temperatures, emplacements where emplacements.id = historique_temperatures.emplacement GROUP BY emplacement ORDER BY timestamp');

            $i=0;
             while($res = $resultat->fetchArray(SQLITE3_ASSOC)){
                 $temperature_moyenne = $temperature_moyenne + $res['temperature'];
              $i++;

          }
$temperature_moyenne = $temperature_moyenne/$i;
$bdd->close(); unset($bdd);

//ensuite on compare la température moyenne avec la température cible ,et on active ou désactive la chaudière en conséquence
    if ($temperature_moyenne >= ($temperature_cible + 0.5)) { //on prend 0.5 degré de marge pour éviter les on/off permanents
                echo "la température  moyenne est de $temperature_moyenne, temp cible $temperature_cible. chaudière désactivée.";

        desactiver_chaudiere();
    }elseif ($temperature_moyenne < ($temperature_cible)){
                        echo "la température  moyenne est de $temperature_moyenne, temp cible $temperature_cible. chaudière activée.";

        activer_chaudiere();

    }

}


?>

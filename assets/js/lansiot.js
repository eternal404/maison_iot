//plugin bootstrap minus and plus
//http://jsfiddle.net/laelitenetwork/puJ6G/
$(document).ready(function() {

//fonction pour la gestion des boutons + et - du sélecteur de températures
$('.btn-number').click(function(e){
    e.preventDefault();

    fieldName = $(this).attr('data-field');
    type      = $(this).attr('data-type');
    var input = $("input[name='"+fieldName+"']");
    var currentVal = parseInt(input.val());
    if (!isNaN(currentVal)) {
        if(type == 'minus') {

            if(currentVal > input.attr('min')) {
                input.val(currentVal - 1).change();
            }
            if(parseInt(input.val()) == input.attr('min')) {
                $(this).attr('disabled', true);
            }

        } else if(type == 'plus') {

            if(currentVal < input.attr('max')) {
                input.val(currentVal + 1).change();
            }
            if(parseInt(input.val()) == input.attr('max')) {
                $(this).attr('disabled', true);
            }

        }

    } else {
        input.val(0);
    }

});
$('.input-number').focusin(function(){
   $(this).data('oldValue', $(this).val());
});
$('.input-number').change(function() {

    minValue =  parseInt($(this).attr('min'));
    maxValue =  parseInt($(this).attr('max'));
    valueCurrent = parseInt($(this).val());

    name = $(this).attr('name');
    if(valueCurrent >= minValue) {
        $(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
    } else {
        alert('Sorry, the minimum value was reached');
        $(this).val($(this).data('oldValue'));
    }
    if(valueCurrent <= maxValue) {
        $(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
    } else {
        alert('Sorry, the maximum value was reached');
        $(this).val($(this).data('oldValue'));
    }
        $.get( "ajax.php", { action: "changer_mode_chaudiere", mode: "auto", temperature_cible: $("#temperature_cible").val() } )
        .fail(function() {
        alert( "Erreur lors du traitement de la requête." );
  });


});
$(".input-number").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
             // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) ||
             // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });
//fin des fonctions des boutons du gestionnaire de température





//fonctions pour obtenir l'état de la chaudière, la température et ajuster les boutons en conséquence
$.getJSON( 'ajax.php', {
    action: "obtenir_statut_temperature_et_chaudiere"
  })
    .done(function(reponse) {

        $('#indicateur_temperature_salon').html('' + reponse.temperature.salon + '°C');
    //adaptation des boutons du sélecteur
    switch(reponse.mode_chaudiere){

                case 'active':
                case 'inactive':
                $("#selecteur_temperature :input").prop("disabled", true);
                break;

                case 'auto':
                $("#selecteur_temperature :input").prop("disabled", false);
                break;



        }

    //remplissage du champ de température cible
         $("#temperature_cible").val(reponse.temperature_cible);



    //remplissage de l'historique des températures

            Morris.Line({
                element: 'graphique_historique_temperatures',


                data: [
                                    //{ y: reponse.historique.salon[0].timestamp, salon: reponse.historique.salon[0].temperature},
                                    { y: reponse.historique.salon[1].timestamp, salon: reponse.historique.salon[1].temperature},

                                     { y: reponse.historique.salon[2].timestamp, salon: reponse.historique.salon[0].temperature},
                                    { y: reponse.historique.salon[3].timestamp, salon: reponse.historique.salon[1].temperature},

                                     { y: reponse.historique.salon[4].timestamp, salon: reponse.historique.salon[0].temperature},
                                    { y: reponse.historique.salon[5].timestamp, salon: reponse.historique.salon[1].temperature},
//
//                                     { y: reponse.historique.salon[6].timestamp, salon: reponse.historique.salon[0].temperature},
//                                    { y: reponse.historique.salon[7].timestamp, salon: reponse.historique.salon[1].temperature},
//
//                                     { y: reponse.historique.salon[8].timestamp, salon: reponse.historique.salon[0].temperature},
//                                    { y: reponse.historique.salon[9].timestamp, salon: reponse.historique.salon[1].temperature},

                    ],




      xkey: 'y',
      ykeys: ['salon'],
      labels: ['Salon (°C)'],
      fillOpacity: 0.6,
      hideHover: 'auto',
      behaveLikeLine: true,
      resize: true,
      pointFillColors:['#ffffff'],
      pointStrokeColors: ['black'],
      lineColors:['green']

            });
    //fin du remplissage de l'historique des températures

})
    .fail(function(){
        alert("Erreur lors de l'obtention des données. Vérifiez votre connexion à internet.");
        $('#indicateur_temperature_salon').html('--°C');

    });




//fonction pour griser/décriser le sélecteur de température en fonction du mode de la chaudière
    $("#btn_chaudiere_active, #btn_chaudiere_inactive").click(function(){
        $("#selecteur_temperature :input").prop("disabled", true);
    });
     $("#btn_chaudiere_auto").click(function(){
        $("#selecteur_temperature :input").prop("disabled", false);
    });



//fonctions d'envoi des commandes de chaudiere au central
    $("#btn_chaudiere_active").click(function(){
        $.get( "ajax.php", { action: "changer_mode_chaudiere", mode: "active" } )
        .fail(function() {
        alert( "Erreur lors du traitement de la requête." );
  });
    });

        $("#btn_chaudiere_inactive").click(function(){
        $.get( "ajax.php", { action: "changer_mode_chaudiere", mode: "inactive" } )
        .fail(function() {
        alert( "Erreur lors du traitement de la requête." );
  });
    });

        $("#btn_chaudiere_auto").click(function(){
        $.get( "ajax.php", { action: "changer_mode_chaudiere", mode: "auto", temperature_cible: $("#temperature_cible").val() } )
        .fail(function() {
        alert( "Erreur lors du traitement de la requête." );
  });
    });

// fin de document.ready();
});

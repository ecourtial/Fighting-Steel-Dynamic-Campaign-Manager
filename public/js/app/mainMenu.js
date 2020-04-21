/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 *
 * Handle the main menu and the scenario/savegame selection.
 */
define(
    ["jquery"],
    function ($) {
        "use strict";

        function hideSubMenus() {
            $('.subMenu').hide();
        }

        /** Validate a scenario */
        $('#validateScenario').click(function () {
            hideSubMenus();
            $('#scenarioValidationListContainer').show();
        });

        $('#scenarioValidationList').change(function () {
            jsonPost(scenarioValidationURL,{'scenario': $(this).val()});
        });

        // FS To TAS
        $('#fsToTas').click(function () {
            hideSubMenus();
            jsonPost(fsToTasURL);
        });

        // TAS TO FS
        $('#tasToFs').click(function () {
            hideSubMenus();
            $('#tasToFsElements').show();
        });

        $('#goTasToFs').click(function () {
            var oneShip = $('#oneShipName').val();
            var scenario = $('#tasToFsscenarioList').val();
            var level = $('#switchLevelList').val();

            jsonPost(tasToFsURL, {'scenario': scenario, 'oneShip': oneShip, 'switchLevel': level});
        });

        // Use a generic function, reusable?
        function jsonPost(route, payload) {
            $.ajax({
                url: route,
                type: "POST",
                data: payload,
                dataType: "json",
                success: function (data) {
                    if (data.length === 0) {
                        $('#messages').attr('class', 'message-good');
                        $('#messageContent').html('All good!');
                    } else {
                        $('#messages').attr('class', 'message-error');
                        $('#messageContent').html('Error!');
                        $.each( data, function( i, val ) {
                            $('#messageContent').append('<ul>');
                            $('#messageContent').append('<li>' +  val + '</li>');
                            $('#messageContent').append('</ul>');
                        });
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert("Sorry: an error occurred during the connexion to the back-end");
                }
            });
        }
    }
);

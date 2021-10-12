/**
 * @author Eric COURTIAL <e.courtial30@gmail.com>
 *
 * Handle the main menu and the scenario/savegame selection.
 */
    $("[id*='Button']") .click(function () {
        var element = $(this).attr('id');
        var id = element.substring(0, element.length - 6);

        $("#console").empty();
        hideSubMenus();

        switch (id) {
            // FS to TAS
            case 'fsToTas':
                jsonPost(fsToTasURL, []);
                break;
            // TAS to FS
            case 'tasToFs':
                $('#tasToFsElements').show();
                break;
            case 'tasToFsSubmit':
                jsonPost(
                    tasToFsURL,
                    {
                        'scenario': $('#scenarioValidationList').val(),
                        'switchLevel': $('#switchLevelList').val(),
                        'oneShip': $('#oneShipName').val(),
                    }
                );
                break;
            // Scenario Validation
            case 'validateScenario':
                $('#scenarioValidationListContainer').show();
                break;
            case 'validateScenarioSubmit':
                jsonPost(scenarioValidationURL, {'scenario': $('#scenarioValidationList').val()});
                break;
            // Scenario Generation
            case 'randomScenario':
                $('#scenarioGeneratorElements').show();
                break;
            case 'generateScenarioSubmit':
                var exploded = $('#theaterList').val().split('-');
                jsonPost(
                    randomScenarioURL,
                    {
                        'mixedNavies': $('#mixedNaviesLevelList').val(),
                        'code': exploded[0],
                        'period': exploded[1],
                    }
                );
                break;
            // Default
            default:
                console.log(`Unknown menu value ${id}.`);
                return;
        }
    });


    function hideSubMenus() {
        $('.subMenu').hide();
    }

    // Use a generic function, reusable?
    function jsonPost(route, payload) {
        $.ajax({
            url: route,
            type: "POST",
            data: payload,
            dataType: "json",
            success: function (data) {
                $("#console").append("Done.<br/>");
                displayMessages(data.messages);
            },
            error: function (data, textStatus, errorThrown) {
                $("#console").append("Sorry: an error occurred during the connexion to the back-end.<br/>");
                displayMessages(data.responseJSON.messages);
            }
        });
    }

    function displayMessages(messages) {
        $.each(messages, function(index, element) {
            $("#console").append(element + '<br/>');
        });
    }

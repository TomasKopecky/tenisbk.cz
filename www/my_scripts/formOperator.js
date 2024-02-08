$(function () {
    hideOrShow3Set();
    checkIfTbsAreSetCorrectly();
    checkLossByDefaultStartingSettings();
    $("input[name*='kontumace']").change(function () {
        $("input[name=skrec_hoste]").prop('checked', false);
        $("input[name=skrec_domaci]").prop('checked', false);
        if ($(this).attr("name") == "zapas_kontumace_domaci")
        {
            $("input[name=zapas_kontumace_hoste]").prop('checked', false);
            LossByDefaultSettings($(this));
        }
        if ($(this).attr("name") == "zapas_kontumace_hoste")
        {
            $("input[name=zapas_kontumace_domaci]").prop('checked', false);
            LossByDefaultSettings($(this));
        }
    });
    $("input[name*='skrec']").change(function () {
        if ($("input[name*='zapas_kontumace']").is(':checked')) {
            $('#hraci_domaci').show();
            $('#hraci_hoste').show();
            selectNullPlayers(false);
            $("select[name*='hrac']").prop('selectedIndex', 0);
            //$("select[name*='hrac']").prop('selectedIndex', 0);
            //$('select[name=hrac1_hoste]').prop('selectedIndex', 0);
        }
        $("input[name=zapas_kontumace_hoste]").prop('checked', false);
        $("input[name=zapas_kontumace_domaci]").prop('checked', false);
        if ($(this).attr("name") == "skrec_domaci")
        {
            $("input[name=skrec_hoste]").prop('checked', false);
        } else
        {
            $("input[name=skrec_domaci]").prop('checked', false);
        }
    });
    $("input[name*='set']").change(function () {
        hideOrShow3Set();
        checkIfTbsAreSetCorrectly();
    });
    //Date picker nastavení - JS bootstrap-datepicker
    $('input[name=datum]').datepicker({
        clearBtn: true,
        autoclose: false,
        language: 'cs',
        format: 'dd.mm.yyyy'
    });

    function checkIfSet1IsSetCorrectly() {
        if (
                !($('input[name=set1_domaci]').val() == 7 && $('input[name=set1_hoste]').val() == 6) &&
                !($('input[name=set1_domaci]').val() == 6 && $('input[name=set1_hoste]').val() == 7) &&
                !($('input[name=set1_domaci]').val() == 7 && $('input[name=set1_hoste]').val() == 5) &&
                !($('input[name=set1_domaci]').val() == 5 && $('input[name=set1_hoste]').val() == 7) &&
                !($('input[name=set1_domaci]').val() == 6 && $('input[name=set1_hoste]').val() >= 0 && $('input[name=set1_hoste]').val() <= 4) &&
                !($('input[name=set1_domaci]').val() >= 0 && $('input[name=set1_domaci]').val() <= 4 && $('input[name=set1_hoste]').val() == 6)
                ) {
            return false;
        }
        return true;
    }

    function checkIfSet2IsSetCorrectly() {
        if (
                !($('input[name=set2_domaci]').val() == 7 && $('input[name=set2_hoste]').val() == 6) &&
                !($('input[name=set2_domaci]').val() == 6 && $('input[name=set2_hoste]').val() == 7) &&
                !($('input[name=set2_domaci]').val() == 7 && $('input[name=set2_hoste]').val() == 5) &&
                !($('input[name=set2_domaci]').val() == 5 && $('input[name=set2_hoste]').val() == 7) &&
                !($('input[name=set2_domaci]').val() == 6 && $('input[name=set2_hoste]').val() >= 0 && $('input[name=set2_hoste]').val() <= 4) &&
                !($('input[name=set2_domaci]').val() >= 0 && $('input[name=set2_domaci]').val() <= 4 && $('input[name=set2_hoste]').val() == 6)
                ) {
            return false;
        }
        return true;
    }

    function calcSet1Winner() {
        if ($('input[name=set1_domaci]').val() > $('input[name=set1_hoste]').val()) {
            return 1;
        } else {
            return 2;
        }
    }

    function calcSet2Winner() {
        if ($('input[name=set2_domaci]').val() > $('input[name=set2_hoste]').val()) {
            return 1;
        } else {
            return 2;
        }
    }


    function checkIfSet3HasToBePlayed() {
        if (
                (calcSet1Winner() == 1 && calcSet2Winner() == 2) ||
                (calcSet1Winner() == 2 && calcSet2Winner() == 1)
                ) {
            return true;
        }
        return false;
    }


    function hideOrShow3Set() {
        if (
                (checkIfSet1IsSetCorrectly() && checkIfSet2IsSetCorrectly() && checkIfSet3HasToBePlayed())
                ) {

            $("input[name=set3_domaci]").prop('disabled', false);
            $("input[name=set3_hoste]").prop('disabled', false);
        } else
        {
            $("input[name=set3_domaci]").prop('disabled', true);
            $("input[name=set3_hoste]").prop('disabled', true);
            $("input[name=set3_domaci]").val(0);
            $("input[name=set3_hoste]").val(0);
        }
    }


    function checkIfTb1HasToBeSet() {
        if (
                ($("input[name=set1_domaci]").val() == 6 && $("input[name=set1_hoste]").val() == 7) ||
                ($("input[name=set1_domaci]").val() == 7 && $("input[name=set1_hoste]").val() == 6)
                ) {
            return true;
        }
        return false;
    }

    function checkIfTb2HasToBeSet() {
        if (
                ($("input[name=set2_domaci]").val() == 6 && $("input[name=set2_hoste]").val() == 7) ||
                ($("input[name=set2_domaci]").val() == 7 && $("input[name=set2_hoste]").val() == 6)
                ) {
            return true;
        }
        return false;
    }

    function checkIfTb3HasToBeSet() {
        if (
                ($("input[name=set3_domaci]").val() == 6 && $("input[name=set3_hoste]").val() == 7) ||
                ($("input[name=set3_domaci]").val() == 7 && $("input[name=set3_hoste]").val() == 6)
                ) {
            return true;
        }
        return false;
    }

    function checkIfTbsAreSetCorrectly() {
        if (checkIfTb1HasToBeSet() || (checkRetirement() && $("input[name=set1_domaci]").val() == 6 && $("input[name=set1_hoste]").val() == 6)) {
            $("input[name=tb1_domaci]").prop('disabled', false);
            $("input[name=tb1_hoste]").prop('disabled', false);
        } else
        {
            $("input[name=tb1_domaci]").prop('disabled', true);
            $("input[name=tb1_hoste]").prop('disabled', true);
            $("input[name=tb1_domaci]").val(0);
            $("input[name=tb1_hoste]").val(0);
        }
        if (checkIfTb2HasToBeSet() || (checkRetirement() && $("input[name=set2_domaci]").val() == 6 && $("input[name=set2_hoste]").val() == 6)) {
            $("input[name=tb2_domaci]").prop('disabled', false);
            $("input[name=tb2_hoste]").prop('disabled', false);
        } else
        {
            $("input[name=tb2_domaci]").prop('disabled', true);
            $("input[name=tb2_hoste]").prop('disabled', true);
            $("input[name=tb2_domaci]").val(0);
            $("input[name=tb2_hoste]").val(0);
        }
        if (checkIfTb3HasToBeSet() || (checkRetirement() && $("input[name=set3_domaci]").val() == 6 && $("input[name=set3_hoste]").val() == 6)) {
            $("input[name=tb3_domaci]").prop('disabled', false);
            $("input[name=tb3_hoste]").prop('disabled', false);
        } else
        {
            $("input[name=tb3_domaci]").prop('disabled', true);
            $("input[name=tb3_hoste]").prop('disabled', true);
            $("input[name=tb3_domaci]").val(0);
            $("input[name=tb3_hoste]").val(0);
        }
    }

    function checkRetirement() {
        if ($("input[name=skrec_hoste]").is(':checked') || $("input[name=skrec_domaci]").is(':checked')) {
            return true;
        }
        return false;
    }

    function setSetsAndTbs(set1Home, set1Visitors, set2Home, set2Visitors, set3Home, set3Visitors, tb1Home, tb1Visitors, tb2Home, tb2Visitors, tb3Home, tb3Visitors) {
        $("input[name=set1_domaci]").val(set1Home);
        $("input[name=set1_hoste]").val(set1Visitors);
        $("input[name=set2_domaci]").val(set2Home);
        $("input[name=set2_hoste]").val(set2Visitors);
        $("input[name=set3_domaci]").val(set3Home);
        $("input[name=set3_hoste]").val(set3Visitors);
        $("input[name=tb1_domaci]").val(tb1Home);
        $("input[name=tb1_hoste]").val(tb1Visitors);
        $("input[name=tb2_domaci]").val(tb2Home);
        $("input[name=tb2_hoste]").val(tb2Visitors);
        $("input[name=tb3_domaci]").val(tb3Home);
        $("input[name=tb3_hoste]").val(tb3Visitors);
    }

    function LossByDefaultSettings(element) {
        if (element.is(':checked'))
        {
            if (element.attr("name") == "zapas_kontumace_domaci") {
                setSetsAndTbs(0, 6, 0, 6, 0, 0, 0, 0, 0, 0, 0, 0);
            } else {
                setSetsAndTbs(6, 0, 6, 0, 0, 0, 0, 0, 0, 0, 0, 0);
            }
            $('#hraci_domaci').hide();
            $('#hraci_hoste').hide();
            selectNullPlayers(true);
            //$("select[name*='hrac']").prop('selectedIndex', 1);
            //$('select[name=hrac1_hoste]').prop('selectedIndex', 1);
        } else {
            setSetsAndTbs(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
            $('#hraci_domaci').show();
            $('#hraci_hoste').show();
            selectNullPlayers(false);
            $("select[name*='hrac']").prop('selectedIndex', 0);
            //$("select[name*='hrac']").prop('selectedIndex', 0);
            //$('select[name=hrac1_hoste]').prop('selectedIndex', 0);
        }
    }

    function checkLossByDefaultStartingSettings() {
        if ($("input[name*='zapas_kontumace']").is(':checked')) {
            $('#hraci_domaci').hide();
            $('#hraci_hoste').hide();
            selectNullPlayers(true);
//$("select[name*='hrac']").prop('selectedIndex', 1);
            //$('select[name=hrac1_hoste]').prop('selectedIndex', 1);
        } else {
            selectNullPlayers(false);
        }
    }

    function selectNullPlayers(state) {
        playersCount = $("select[name*='hrac']").length;
        if (state === true) {
            for (i = 1; i < playersCount; i++) {
                if ($('select[name=hrac' + i + '_domaci] option[value=null]').length == 0 && $('select[name=hrac' + i + 'hoste] option[value=null]').length == 0) {
                    $('select[name=hrac' + i + '_domaci]').append('<option value="null">null</option>');
                    $('select[name=hrac' + i + '_hoste]').append('<option value="null">null</option>');
                }
                $('select[name=hrac' + i + '_domaci]').val('null');
                $('select[name=hrac' + i + '_hoste]').val('null');
                if (i === 2)
                    break;
            }
        } else {
            for (i = 1; i < playersCount; i++) {
                $('select[name=hrac' + i + '_domaci] option[value="null"]').remove();
                $('select[name=hrac' + i + '_hoste] option[value="null"]').remove();
                if (i === 2)
                    break;
            }
        }
    }

});
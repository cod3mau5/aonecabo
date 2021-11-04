<?php
    global $wpdb;

    $resorts = $wpdb->get_results('SELECT * FROM resorts ORDER BY name ASC');

    $resort_options = '';
    foreach ($resorts as $row) {
        $resort_options .= '<option value="'.$row->id.'">'.$row->name.'</option>';
    }
?>
<div class="container no-padding" style="position: initial;">
    <div class="row no-gutter">
        <!-- booking form -->
        <form class="form-horizontal booking"
              method="get"
              name="booking-home"
              action="reserve/"
              id="booking-home"
        >
            <legend>Book Your Transportation</legend>
            <div class="items">
                <div class="form-group">
                    <select id="trip" name="trip" class="form-control" required="">
                        <option value="" disabled="" selected="selected" style="display:none">
                            Trip Type
                        </option>
                        <option value="o">One Way</option>
                        <option value="r">Roundtrip</option>
                    </select>
                </div>
                <div class="form-group">
                    <input id="arrival_date" name="arrival" placeholder="Arrival Date" class="form-control date" type="text">
                    <input id="departure_date" name="departure" placeholder="Departure Date" class="form-control date" type="text">
                </div>
                <div class="form-group">
                    <select id="start_location" name="start_location" class="form-control" required="">
                        <option value="" disabled="" selected="selected" style="display:none">
                            Start Location
                        </option>
                        <option value="0">Los Cabos Int. Airport</option>
                        <?php echo $resort_options; ?>
                    </select>
                    <select id="end_location" name="end_location" class="form-control" required="">
                        <option value="" disabled="" selected="selected" style="display:none">
                            End Location
                        </option>
                        <option value="0">Los Cabos Int. Airport</option>
                        <?php echo $resort_options; ?>
                    </select>
                </div>
                <div class="form-group">
                    <input type="number" id="passengers" name="passengers" placeholder="#Passengers" class="form-control" min="1" required="">
                </div>
                <div class="form-group">
                    <input type="hidden" name="step" value="1">
                    <button type="submit" class="btn-booking btn-block">BOOK NOW</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    jQuery(document).ready(function($) {
        $('#arrival_date').datetimepicker({
            format: 'MM/DD/YYYY',
        });

        $('#departure_date').datetimepicker({
            format: 'MM/DD/YYYY',
            useCurrent: false //Important! See issue #1075
        });

        $("#arrival_date").on("dp.change", function (e) {
            if ($('#departure_date').length) {
                $('#departure_date').data("DateTimePicker").minDate(e.date);
            }
        });

        $("#departure_date").on("dp.change", function (e) {
            $('#arrival_date').data("DateTimePicker").maxDate(e.date);
        });

        $("#trip").on('change', function() {
            if ($(this).val() == 'o') {
                $('#booking-home .date').css('width', '100%');
                $('#departure_date').hide();
            } else {
                $('#booking-home .date').css('width', '49%');
                $('#start_location').css('width', '49%');
                $('#end_location').show();
                $('#departure_date').show();
            }
        });

        $('#start_location').on('change', function() {
            if ($(this).val() == 0) {
                $('#end_location').html('<?=$resort_options?>');
            } else {
                $('#end_location').html('<option value="0">Los Cabos Int. Airport</option>');
            }
            $('.from').html( $('#start_location option:selected').text() );
            $('.to').html( $('#end_location option:selected').text() );
        });

    });
</script>
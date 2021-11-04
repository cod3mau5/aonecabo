<div id="accordion" role="tablist" aria-multiselectable="true">
    <div class="card">
        <div class="card-header" role="tab" id="sjd">
            <h5 class="mb-0">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                    San José del Cabo
                </a>
            </h5>
        </div>
        <div id="collapseOne" class="collapse show" role="tabpanel" aria-labelledby="sjd">
            <div class="card-block">
                <div class="row">
                    <div class="mx-auto col-md-6">
                        <div class="form-group text-center">
                            <label for="exampleInputEmail1">Vehicle</label>
                            <select name="" id="vehicle_zone_1" class="form-control">
                                <?php foreach ($units as $unit): ?>
                                <option value="<?=$unit->id?>"><?=$unit->name?></option>
                                <?php endforeach; ?>
                            </select>
                            <b>Airport To</b>
                            <h2>San José del Cabo</h2>
                            <span class="starting">starting from</span>
                            <strong>$<span id="rate_zone_1"><?=$prices[1][1]['oneway'];?></span> <small>usd</small></strong>
                            <span id="rate_zone_1_rt">Roundtrip from $<?=$prices[1][1]['roundtrip'];?> usd</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header" role="tab" id="corridor">
            <h5 class="mb-0">
                <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo"  class="rates_header">
                    Corridor
                </a>
            </h5>
        </div>
        <div id="collapseTwo" class="collapse" role="tabpanel" aria-labelledby="corridor">
            <div class="card-block">
                <div class="row">
                    <div class="mx-auto col-md-6">
                        <div class="form-group text-center">
                            <label for="exampleInputEmail1">Vehicle</label>
                            <select name="" id="vehicle_zone_2" class="form-control">
                                <?php foreach ($units as $unit): ?>
                                <option value="<?=$unit->id?>"><?=$unit->name?></option>
                                <?php endforeach; ?>
                            </select>
                            <b>Airport To</b>
                            <h2>Corridor</h2>
                            <span class="starting">starting from</span>
                            <strong>$<span id="rate_zone_2"><?=$prices[1][2]['oneway'];?></span> <small>usd</small></strong>
                            <span id="rate_zone_2_rt">Roundtrip from $<?=$prices[1][2]['roundtrip'];?> usd</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header" role="tab" id="csl">
            <h5 class="mb-0">
                <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree"  class="rates_header">
                    Cabo San Lucas
                </a>
            </h5>
        </div>
        <div id="collapseThree" class="collapse" role="tabpanel" aria-labelledby="csl">
            <div class="card-block">
                <div class="row">
                    <div class="mx-auto col-md-6">
                        <div class="form-group text-center">
                            <label for="exampleInputEmail1">Vehicle</label>
                            <select name="" id="vehicle_zone_3" class="form-control">
                                <?php foreach ($units as $unit): ?>
                                <option value="<?=$unit->id?>"><?=$unit->name?></option>
                                <?php endforeach; ?>
                            </select>
                            <b>Airport To</b>
                            <h2>Cabo San Lucas</h2>
                            <span class="starting">starting from</span>
                            <strong>$<span id="rate_zone_3"><?=$prices[1][4]['oneway'];?></span> <small>usd</small></strong>
                            <span id="rate_zone_3_rt">Roundtrip from $<?=$prices[1][4]['roundtrip'];?> usd</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header" role="tab" id="lpz">
            <h5 class="mb-0">
                <a class="collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapseFour" aria-expanded="false" aria-controls="collapseFour"  class="rates_header">
                    La Paz
                </a>
            </h5>
        </div>
        <div id="collapseFour" class="collapse" role="tabpanel" aria-labelledby="lpz">
            <div class="card-block">
                <div class="row">
                    <div class="mx-auto col-md-6">
                        <div class="form-group text-center">
                            <label for="exampleInputEmail1">Vehicle</label>
                            <select name="" id="vehicle_zone_4" class="form-control">
                                <?php foreach ($units as $unit): ?>
                                <option value="<?=$unit->id?>"><?=$unit->name?></option>
                                <?php endforeach; ?>
                            </select>
                            <b>Airport To</b>
                            <h2>La Paz</h2>
                            <span class="starting">starting from</span>
                            <strong>$<span id="rate_zone_4"><?=$prices[1][8]['oneway'];?></span> <small>usd</small></strong>
                            <span id="rate_zone_4_rt">Roundtrip from $<?=$prices[1][8]['roundtrip'];?> usd</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {

        var rates = <?php echo json_encode($rates); ?>;

        $('#vehicle_zone_1').on('change', function() {
            var rate = searchRate($(this).val(), 1);
            $('#rate_zone_1').html(rate.oneway);
            $('#rate_zone_1_rt').html("Roundtrip from $" + Number(rate.roundtrip) + " usd");
        });

        $('#vehicle_zone_2').on('change', function() {
            var rate = searchRate($(this).val(), 2);
            $('#rate_zone_2').html(rate.oneway);
            $('#rate_zone_2_rt').html("Roundtrip from $" + Number(rate.roundtrip) + " usd");
        });

        $('#vehicle_zone_3').on('change', function() {
            var rate = searchRate($(this).val(), 4);
            $('#rate_zone_3').html(rate.oneway);
            $('#rate_zone_3_rt').html("Roundtrip from $" + Number(rate.roundtrip) + " usd");
        });

        $('#vehicle_zone_4').on('change', function() {
            var rate = searchRate($(this).val(), 8);
            $('#rate_zone_4').html(rate.oneway);
            $('#rate_zone_4_rt').html("Roundtrip from $" + Number(rate.roundtrip) + " usd");
        });

        function searchRate(vehicle, zone)
        {
            var rate = '';

            for (var i = 0; i<= rates.length; i++)
            {
                if (rates[i].unit_id == vehicle && rates[i].zone_id == zone) {
                    rate = rates[i];
                    break;
                }
            }
            return rate;
        }
    });
</script>
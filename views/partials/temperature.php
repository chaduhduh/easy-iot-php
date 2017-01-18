<div class="temperature-widget" data-weather-pane>
    <div class="row">
        <div class="columns">
            <div class="options">
                <a data-option-unit="c">C<span>&deg;</span></a>
                <a class="active" data-option-unit="f">F<span>&deg;</span></a>
            </div>
            <section class="weather-panel">
                <div class="temp">
                    <span data-weather-text>
                        <i class="fa fa-circle-o-notch fa-spin" aria-hidden="true"></i>
                    </span>
                    <sup>&deg;</sup>
                </div>
                <div class="location">
                    Current Temperature, Tucson - AZ
                </div>
            </section>
        </div>
    </div>
</div>
<script type="text/javascript">
    $( document ).ready(function() {
        var $scope = $("[data-weather-pane]");
        var $unitOption = $scope.find("[data-option-unit]");
        var delay = 30000;
        var running = true;
        var selectedUnit = 'f';
        //main

        setTimeout(getTemp, 1000, selectedUnit);
        setInterval(getTemp, delay, selectedUnit);
        // listeners

        $unitOption.on("click", function(){
            changeUnit($(this).attr('data-option-unit'));
        });
        // functions

        function getTemp(unit="f") {
            var req = $.get("/temp",{"unit":unit});
            req.done(function(data){
                if(data.success === false)
                    return;
                temp = parseFloat(data.temp).toFixed(2);
                showTemp(temp);
                setColor(temp);
            });
        }

        function showTemp(temp) {
            $scope.find("[data-weather-text]").html(temp||$scope.find("[data-weather-text]").html());
        }

        function changeUnit(unit) {
            if(selectedUnit !== unit){
                getTemp(unit);
            }
            selectedUnit = unit;
            $activeOption = $scope.find("[data-option-unit='"+unit+"']");
            if($activeOption){
                $activeOption.addClass("active");
                $activeOption.siblings().removeClass("active");
            }
        }

        function setColor(temp) {
            fTemp = getFarenheight(temp);
            getColorFromfTemp(fTemp);

            function getFarenheight(temp) {
                if (selectedUnit == 'f')
                    return temp;
                return parseFloat(((temp * 9) / 5) + 32);
            }
        }

        function getColorFromfTemp(temp){
            var ranges = [
                { "name" : "extreme-heat", "min" : 110, "max" : 2000, "value" : "rgb(199,6,23)" },
                { "name" : "heat", "min" : 95, "max" : 109, "value" : "rgb(186,16,36)" },
                { "name" : "hot", "min" : 85, "max" : 94, "value" : "rgb(169,30,54)" },
                { "name" : "warm", "min" : 75, "max" : 84, "value" : "rgb(144,50,79)" },
                { "name" : "perfect", "min" : 65, "max" : 74, "value" : "rgb(124,66,100)" },
                { "name" : "cool", "min" : 55, "max" : 64, "value" : "rgb(106,80,118)" },
                { "name" : "cold", "min" : 40, "max" : 54, "value" : "rgb(83,97,141)" },
                { "name" : "colder", "min" : 30, "max" : 39, "value" : "rgb(58,117,166)" },
                { "name" : "freeze", "min" : 10, "max" : 29, "value" : "rgb(43,129,182)" },
                { "name" : "coldest", "min" : -500, "max" : 9, "value" : "rgb(31,139,194)" }
            ];

            for(i=0; i<ranges.length; i++) {
                if (temp <= ranges[i].max && temp >= ranges[i].min){
                    $("body").css({ 
                        "background-color": ranges[i].value, 
                        "color" : ranges[i].value
                    });
                    $("body").addClass(ranges[i].name);
                }
            }
        }
    });
</script>
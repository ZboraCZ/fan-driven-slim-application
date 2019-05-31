let HUMIDITY_STARTING_FAN = 50; // DEFAULT BEFORE FIRST CALL - percents of humidity starting fans for cooling
const REFRESH_TIME = 5000;        //milliseconds to refresh values

$(document).ready(function() {
    getStartFanValues();
    refreshTableValues();
    evaluateCooling();
    createChart();
});

function getStartFanValues() {
    //GET pro vlhkost a teplotu startujici chlazeni
    $.ajax({
        url: "https://akela.mendelu.cz/~xzboril7/avtk_projekt/public/auth/vlhkost-teplota",
        type: 'GET',
        dataType: 'json', // added data type
        success: function(humidity) {
            HUMIDITY_STARTING_FAN = humidity.value;
            $("#actual-startfan-humidity-text").text('Vlhkost zahajující chlazení:');
            $("#actual-startfan-humidity-value").text(HUMIDITY_STARTING_FAN + '%');
        }
    });
}

function evaluateCooling(){
    //GET pro posledni zaznamenane hodnoty v jsonu
    $.ajax({
        url: "https://akela.mendelu.cz/~xzboril7/avtk_projekt/public/auth/hodnoty?posledni=true",
        type: 'GET',
        dataType: 'json', // added data type
        success: function(responseData) {
            let lastTemperature = responseData.hodnota.temperature;
            let lastHumidity = responseData.hodnota.humidity;

            //set Headers with actual info
            $("#actual-temperature").text(lastTemperature + ' °C');
            $("#actual-humidity").text(lastHumidity+ ' %');

            if (lastHumidity >= HUMIDITY_STARTING_FAN){ //vlhkost je nad mez, zacne chlazeni
                $(".humidity-header").css({ 'color': 'red' });
                setTimeout(function () {
                    $('.humidity-header').fadeOut(500);
                    $('.humidity-header').fadeIn(500);
                }, 1000);
                setTimeout(function () {
                    $('.humidity-header').fadeOut(500);
                    $('.humidity-header').fadeIn(500);
                }, 3000)

                $("#cooling-status").text('Probíhá chlazení');
                displaySpinningFan();
            }
            else {
                $("#cooling-status").text('Vlhkost je v pořádku');
                $(".humidity-header").css({ 'color': 'black'});
                displayStoppedFan();
            }

            setTimeout(function(){
                evaluateCooling();
                refreshTableValues();
            }, REFRESH_TIME);
        }
    });
}

function refreshTableValues() {
    $.ajax({
        url: "https://akela.mendelu.cz/~xzboril7/avtk_projekt/public/auth/hodnoty10",
        type: 'GET',
        dataType: 'json', // added data type
        success: function(responseData) { //vymazeme obsah tabulky a naplnime ji novymi daty
            ///////////REFRESHING TABLE///////////////////
            $("#table-values").fadeOut(500);

            $("#table-values").find("tr").remove();
            for (var i=0; i<responseData.length; i++){
                $("#table-values").append($('<tr>')
                        .append($('<td>'+responseData[i].temperature+'</td><td>'+responseData[i].humidity+'</td><td>'+ responseData[i].time.substring(0,19)+'</td>')
                            )
                        .append($('</tr>')
                        )
                    );
            }

            $("#table-values").fadeIn(500);
            ///////////GETTING VALUES FOR CHART///////////////////
            var humidity = [];
            for (var i=responseData.length-1; i>=0; i--) {
                humidity.push(Number(responseData[i].humidity));
            }
            var times = [];
            for (var i=responseData.length-1; i>=0; i--) {
                times.push(responseData[i].time.substring(5,19));
            }
            createChart(humidity, times);   //Passing arrays of values into chart
        }
    });
}

function displaySpinningFan() {
    $("#stopped-fan").hide();
    $("#spinning-fan").show();
}

function displayStoppedFan() {
    $("#spinning-fan").hide();
    $("#stopped-fan").show();
}

function createChart(dataYAxis, labelsXAxis){ //dataYAxis is an array of integers, labelsXAxis array of times
    var canvas = document.getElementById('myChart');
    var data = {
        labels: labelsXAxis,   //["January", "February", "March", "April", "May", "June", "July"],
        datasets: [
            {
                label: "Humidity timelapse",
                fill: true,
                lineTension: 0.5,
                backgroundColor: "rgba(75,192,192,0.4)",
                borderColor: "rgba(75,192,192,1)",
                borderCapStyle: 'butt',
                borderDash: [],
                borderDashOffset: 0.0,
                borderJoinStyle: 'miter',
                pointBorderColor: "rgba(75,192,192,1)",
                pointBackgroundColor: "#fff",
                pointBorderWidth: 1,
                pointHoverRadius: 3,
                pointHoverBackgroundColor: "rgba(75,192,192,1)",
                pointHoverBorderColor: "rgba(220,220,220,1)",
                pointHoverBorderWidth: 2,
                pointRadius: 3,
                pointHitRadius: 10,
                data: dataYAxis,     //[65, 59, 80, 0, 56, 55, 40],
            }
        ]
    };
    var option = {
        showLines: true
    };
    var myLineChart = Chart.Line(canvas,{
        data:data,
        options:option
    });
}



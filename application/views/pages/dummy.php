<html>
        <script type="text/javascript" src="http://[::1]/spotifyapp/assets/js/Chart.bundle.js"></script>
    <script type="text/javascript" src="http://[::1]/spotifyapp/assets/js/jquery-1.12.4.min.js"></script>
    <script type="text/javascript" src="http://[::1]/spotifyapp/assets/js/bootstrap.js"></script>
    <link rel="stylesheet" href="http://[::1]/spotifyapp/assets/css/bootstrap.css" />
    <head>
        <title>Spotify Playlist Analyzer</title>
    </head>
    <body>

        <style>
.jumbotron {
    padding-top: 12px;
}
</style>
<script type="text/javascript">
    function resize_graphs(){
        var h1 = $('#graph1').height();
        var h2 = $('#graph2').height();
        var h3 = $('#graph3').height();
        var height = Math.max(h1, h2, h3);

        for (var i = 1; i <= 3; i++) {
            $('#graph'+i).height(height);
        }
    }
    $(window).resize(resize_graphs);
    $(document).ready(resize_graphs);
</script>
<div class="container">
    <div class="jumbotron" style="text-align: center">


<h1><b>Real</b></h1><hr size=><h4><b>Hipster rating: 57 / 100</b></h4>The hipster rating is based on the popularity of the playlist's tracks on spotify.<hr>    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Genre</th><th>Count</th><th>Percentage</th>
            </tr>
        </thead>
        <tbody>
        <tr class="active"><td>Rock</td><td>433</td><td>0.31</td></tr><tr class="active"><td>Electronic</td><td>369</td><td>0.26</td></tr><tr class="active"><td>Pop</td><td>296</td><td>0.21</td></tr><tr class="active"><td>Folk</td><td>233</td><td>0.16</td></tr><tr class="active"><td>Jazz</td><td>39</td><td>0.03</td></tr><tr class="active"><td>Rap</td><td>20</td><td>0.01</td></tr><tr class="active"><td>No Genre Found</td><td>12</td><td>0.01</td></tr><tr class="active"><td>Metal</td><td>8</td><td>0.01</td></tr><tr class="active"><td>Country</td><td>5</td><td>0</td></tr>        </tbody>
    </table>
        <hr>
    <h2><b>Graphs</b></h2>
    These graphs show the distribution of what genre of songs you have been adding to the playlist each month.
    <ul class="nav nav-tabs">
        <li class="active">
            <a href="#graph1" data-toggle="tab" aria-expanded="true">Percentage Area Graph</a>
        </li>
        <li class="">
            <a href="#graph2" data-toggle="tab" aria-expanded="true">Total Area Graph</a>
        </li>
        <li class="">
            <a href="#graph3" data-toggle="tab" aria-expanded="true">Line Graph</a>
        </li>
    </ul>
    <div id="graphTabs" class="tab-content">
        <div class="tab-pane fade active in" id="graph1">
                <canvas id="myChart1" width="400" height="400"></canvas>
    <script>
        var ctx = document.getElementById("myChart1");
        var data = {
            labels: ["2013-8", "2013-9", "2013-10", "2013-11", "2013-12", "2014-1", "2014-2", "2014-3", "2014-4", "2014-5", "2014-6", "2014-7", "2014-8", "2014-9", "2014-10", "2014-11", "2014-12", "2015-1", "2015-2", "2015-3", "2015-4", "2015-5", "2015-6", "2015-7", "2015-8", "2015-9", "2015-10", "2015-11", "2015-12", "2016-1", "2016-2", "2016-3", "2016-4", "2016-5", "2016-6", ],
            datasets: [
    {
        label: 'Rock',
        backgroundColor : "rgba(202,9,250,1)",
        data :[0.56, 0.76, 0.83, 0, 1, 0.86, 0.79, 0.75, 0.5, 0.32, 0.29, 0.58, 0.55, 0.07, 0.27, 0.35, 0.21, 0.34, 0.44, 0.2, 0.16, 0.27, 0.35, 0.12, 0.2, 0.14, 0.25, 0.18, 0.36, 0.33, 0.4, 0.11, 0.07, 0.19, 0.14, ]
    },{
        label: 'Folk',
        backgroundColor : "rgba(46,129,253,1)",
        data :[0.89, 0.83, 0.83, 0, 1, 0.86, 0.84, 0.75, 0.81, 0.43, 0.42, 0.63, 0.65, 0.44, 0.34, 0.54, 0.49, 0.6, 0.59, 0.55, 0.3, 0.38, 0.5, 0.33, 0.43, 0.19, 0.42, 0.25, 0.47, 0.38, 0.53, 0.32, 0.32, 0.25, 0.36, ]
    },{
        label: 'Electronic',
        backgroundColor : "rgba(39,252,124,1)",
        data :[1, 0.86, 0.83, 0, 1, 0.86, 0.84, 0.75, 0.85, 0.86, 0.46, 0.89, 0.9, 0.78, 0.68, 0.74, 0.73, 0.84, 0.8, 0.78, 0.71, 0.54, 0.67, 0.64, 0.67, 0.53, 0.67, 0.78, 0.64, 0.81, 0.77, 0.61, 0.64, 0.72, 0.79, ]
    },{
        label: 'Pop',
        backgroundColor : "rgba(204,247,29,1)",
        data :[1, 1, 1, 1, 1, 0.97, 0.95, 0.75, 1, 1, 1, 1, 0.95, 0.89, 0.84, 0.93, 0.94, 0.98, 0.98, 0.95, 0.89, 0.85, 0.98, 0.96, 0.89, 0.95, 0.93, 1, 0.92, 0.94, 0.9, 0.98, 0.86, 0.94, 1, ]
    },{
        label: 'Other',
        backgroundColor : "rgba(246,35,35,1)",
        data :[1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, ]
    },            ]
        }

        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                scales: {
                    yAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'Number of Songs Added'
                            },
                            ticks: {
                                beginAtZero: true
                            }
                        }],
                    xAxes: [{
                            scaleLabel: {
                              display: true,
                              labelString: 'Month'
                            },
                    }]
                },
                tooltips: {
                    enabled: false
                }
            }
        });
    </script>
            </div>
        <div class="tab-pane fade in" id="graph2">
                <canvas id="myChart2" width="400" height="400"></canvas>
    <script>
        var ctx = document.getElementById("myChart2");
        var data = {
            labels: ["2013-8", "2013-9", "2013-10", "2013-11", "2013-12", "2014-1", "2014-2", "2014-3", "2014-4", "2014-5", "2014-6", "2014-7", "2014-8", "2014-9", "2014-10", "2014-11", "2014-12", "2015-1", "2015-2", "2015-3", "2015-4", "2015-5", "2015-6", "2015-7", "2015-8", "2015-9", "2015-10", "2015-11", "2015-12", "2016-1", "2016-2", "2016-3", "2016-4", "2016-5", "2016-6", ],
            datasets: [
    {
        label: 'Rock',
        backgroundColor : "rgba(209,59,246,1)",
        data :[5, 27, 37, 37, 48, 78, 93, 96, 109, 118, 125, 136, 147, 150, 162, 178, 201, 228, 263, 271, 286, 293, 309, 317, 326, 334, 349, 356, 375, 391, 416, 421, 424, 431, 433, ]
    },{
        label: 'Folk',
        backgroundColor : "rgba(50,132,255,1)",
        data :[8, 32, 42, 42, 53, 83, 99, 102, 123, 135, 145, 157, 170, 190, 205, 230, 283, 331, 378, 400, 427, 437, 460, 482, 502, 513, 538, 548, 573, 591, 624, 638, 652, 661, 666, ]
    },{
        label: 'Electronic',
        backgroundColor : "rgba(19,255,113,1)",
        data :[9, 34, 44, 44, 55, 85, 101, 104, 126, 150, 161, 178, 196, 231, 261, 295, 374, 441, 505, 536, 601, 615, 646, 689, 720, 751, 791, 822, 856, 895, 943, 970, 998, 1024, 1035, ]
    },{
        label: 'Pop',
        backgroundColor : "rgba(200,247,13,1)",
        data :[9, 38, 50, 51, 62, 96, 114, 117, 143, 171, 195, 214, 233, 273, 310, 353, 454, 532, 610, 648, 729, 751, 796, 860, 901, 956, 1012, 1052, 1101, 1146, 1202, 1245, 1283, 1317, 1331, ]
    },{
        label: 'Other',
        backgroundColor : "rgba(253,15,15,1)",
        data :[9, 38, 50, 51, 62, 97, 116, 120, 146, 174, 198, 217, 237, 282, 326, 372, 480, 560, 640, 680, 771, 797, 843, 910, 956, 1014, 1074, 1114, 1167, 1215, 1277, 1321, 1365, 1401, 1415, ]
    },            ]
        }

        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                scales: {
                    yAxes: [{
                            scaleLabel: {
                                display: true,
                                labelString: 'Number of Songs Added'
                            },
                            ticks: {
                                beginAtZero: true
                            }
                        }],
                    xAxes: [{
                            scaleLabel: {
                              display: true,
                              labelString: 'Month'
                            },
                    }]
                },
                tooltips: {
                    enabled: false
                }
            }
        });
    </script>
            </div>
        <div class="tab-pane fade in" id="graph3">
                <canvas id="myChart3" width="400" height="400"></canvas>
    <script>
        var ctx = document.getElementById("myChart3");
        var data = {
            labels: ["2013-8", "2013-9", "2013-10", "2013-11", "2013-12", "2014-1", "2014-2", "2014-3", "2014-4", "2014-5", "2014-6", "2014-7", "2014-8", "2014-9", "2014-10", "2014-11", "2014-12", "2015-1", "2015-2", "2015-3", "2015-4", "2015-5", "2015-6", "2015-7", "2015-8", "2015-9", "2015-10", "2015-11", "2015-12", "2016-1", "2016-2", "2016-3", "2016-4", "2016-5", "2016-6", ],
            datasets: [
    {
        label: 'Rock',
        backgroundColor : "rgba(204,41,245,1)",
        borderColor : "rgba(204,41,245,1)",
        fill : false,
        data :[5, 22, 10, 0, 11, 30, 15, 3, 13, 9, 7, 11, 11, 3, 12, 16, 23, 27, 35, 8, 15, 7, 16, 8, 9, 8, 15, 7, 19, 16, 25, 5, 3, 7, 2, ]
    },{
        label: 'Folk',
        backgroundColor : "rgba(23,113,248,1)",
        borderColor : "rgba(23,113,248,1)",
        fill : false,
        data :[3, 2, 0, 0, 0, 0, 1, 0, 8, 3, 3, 1, 2, 17, 3, 9, 30, 21, 12, 14, 12, 3, 7, 14, 11, 3, 10, 3, 6, 2, 8, 9, 11, 2, 3, ]
    },{
        label: 'Electronic',
        backgroundColor : "rgba(32,248,119,1)",
        borderColor : "rgba(32,248,119,1)",
        fill : false,
        data :[1, 1, 0, 0, 0, 0, 0, 0, 1, 12, 1, 5, 5, 15, 15, 9, 26, 19, 17, 9, 38, 4, 8, 21, 11, 20, 15, 21, 9, 21, 15, 13, 14, 17, 6, ]
    },{
        label: 'Pop',
        backgroundColor : "rgba(205,246,41,1)",
        borderColor : "rgba(205,246,41,1)",
        fill : false,
        data :[0, 4, 2, 1, 0, 4, 2, 0, 4, 4, 13, 2, 1, 5, 7, 9, 22, 11, 14, 7, 16, 8, 14, 21, 10, 24, 16, 9, 15, 6, 8, 16, 10, 8, 3, ]
    },{
        label: 'Other',
        backgroundColor : "rgba(250,25,25,1)",
        borderColor : "rgba(250,25,25,1)",
        fill : false,
        data :[0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 1, 5, 7, 3, 7, 2, 2, 2, 10, 4, 1, 3, 5, 3, 4, 0, 4, 3, 6, 1, 6, 2, 0, ]
    },            ]
        }

        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: data,
            options: {
                scales: {
                    yAxes: [{
                            scaleLabel: {
                              display: true,
                              labelString: 'Number of Songs Added'
                            },
                            ticks: {
                                beginAtZero: true
                            }
                        }],
                    xAxes: [{
                            scaleLabel: {
                              display: true,
                              labelString: 'Month'
                            },
                    }]
                },
                title: {
                    display: false,
                    text: 'Custom Chart Title'
                }
            }
        });
    </script>
            </div>
    </div>
    
    
    
    </div>
</div>
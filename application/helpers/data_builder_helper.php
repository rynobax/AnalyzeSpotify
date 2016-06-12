<?php

function generate_month_genre_stats($playlist, $artists) {
    $month_genre_stats = array();
    $earliest_month = 999999;
    $earliest_year = 99999999;
    $latest_month = -1000;
    $latest_year = -10000;

    foreach ($playlist as $track) {
        if ($track->added_at_year < $earliest_year) {
            $earliest_year = $track->added_at_year;
            $earliest_month = $track->added_at_month;
        }
        if ($track->added_at_year == $earliest_year) {
            if ($track->added_at_month < $earliest_month) {
                $earliest_year = $track->added_at_year;
                $earliest_month = $track->added_at_month;
            }
        }
        if ($track->added_at_year > $latest_year) {
            $latest_year = $track->added_at_year;
            $latest_month = $track->added_at_month;
        }
        if ($track->added_at_year == $latest_year) {
            if ($track->added_at_month > $latest_month) {
                $latest_year = $track->added_at_year;
                $latest_month = $track->added_at_month;
            }
        }
    }


    $current_month = $earliest_month;
    $current_year = $earliest_year;
    while (true) {
        $month_genre_stats[$current_year . "-" . $current_month] = array();
        $month_genre_stats[$current_year . "-" . $current_month]["TOTAL"] = 0;
        $current_month++;
        if ($current_month == 13) {
            $current_month = 1;
            $current_year++;
        }
        if (($current_month > $latest_month) AND ( $current_year >= $latest_year))
            break;
    }

    foreach ($playlist as $track) {
        $genre = $artists[$track->artists[0]->name]->genre;
        if (!isset($month_genre_stats[$track->added_at_year . "-" . $track->added_at_month][$genre])) {
            $month_genre_stats[$track->added_at_year . "-" . $track->added_at_month][$genre] = 1;
            $month_genre_stats[$track->added_at_year . "-" . $track->added_at_month]["TOTAL"] ++;
        } else {
            $month_genre_stats[$track->added_at_year . "-" . $track->added_at_month][$genre] ++;
            $month_genre_stats[$track->added_at_year . "-" . $track->added_at_month]["TOTAL"] ++;
        }
    }
    return $month_genre_stats;
}

function generate_genre_list(&$month_genre_stats, $count_cutoff, $genre_count) {
    $genre_list = array();
    foreach ($month_genre_stats as $date => &$stats) {
        foreach ($stats as $genre => $count) {
            if ($genre == "Other")
                continue;
            if ($genre != "NO_TAGS" AND $genre != "TOTAL" AND $genre_count[$genre] >= $count_cutoff) {
                $genre_list[$genre] = true;
            } else if ($genre != "TOTAL") {
                $genre_list["Other"] = true;
                if (!isset($stats["Other"]))
                    $stats["Other"] = 0;
                $stats["Other"] += $count;
            }
        }
    }
    return $genre_list;
}

function build_percentage_data($month_genre_stats, $genre_list) {
    $data = array();
    $genre_totals = array();
    foreach ($genre_list as $genre => $ignore) {
        $data[$genre] = array();
    }
    foreach ($month_genre_stats as $date => $count) { // For each date
        $date_sum = 0;
        foreach ($genre_list as $genre => $ignore) { // For each genre
            $current_count = 0;
            if (isset($count[$genre])) {
                $current_count = $count[$genre];
            }
            if ($count["TOTAL"] != 0) {
                $data[$genre][$date] = ($current_count / $count["TOTAL"]) + $date_sum;
            } else {
                $data[$genre][$date] = $date_sum;
            }
            $date_sum = $data[$genre][$date];
        }
    }
    return $data;
}

function build_cumulative_data($month_genre_stats, $genre_list) {
    $data = array();
    $genre_totals = array();
    foreach ($genre_list as $genre => $ignore) {
        $data[$genre] = array();
        $genre_totals[$genre] = 0;
    }
    foreach ($month_genre_stats as $date => $count) { // For each date
        $date_sum = 0;
        foreach ($genre_list as $genre => $ignore) { // For each genre
            $current_count = 0;
            if (isset($count[$genre])) {
                $current_count = $count[$genre];
            }
            $date_sum += $current_count;
            $data[$genre][$date] = $genre_totals[$genre] + $date_sum;
            $genre_totals[$genre] = $data[$genre][$date];
        }
    }
    return $data;
}

function build_line_data($month_genre_stats, $genre_list) {
    $data = array();
    foreach ($genre_list as $genre => $ignore) {
        $data[$genre] = array();
    }
    foreach ($month_genre_stats as $date => $count) { // For each date
        foreach ($genre_list as $genre => $ignore) { // For each genre
            $num;
            if (isset($count[$genre])) {
                $num = $count[$genre];
            } else {
                $num = 0;
            }
            $data[$genre][$date] = $num;
        }
    }
    return $data;
}

function generate_solid_line_graph_html($graph_num, $data, $genre_list) {
    $graph_name = "myChart" . $graph_num;
    $colors = generate_n_distinct_colors(count($data));
    ?>
    <canvas id="<?php echo $graph_name ?>" width="400" height="400"></canvas>
    <script>
        var ctx = document.getElementById("<?php echo $graph_name ?>");
        var data = {
            labels: [<?php
    foreach (end($data) as $date => $stats) {
        echo '"' . $date . '", ';
    }
    ?>],
            datasets: [
    <?php
    foreach ($genre_list as $genre => $ignore) {
        echo "{\n";
        echo "\t\tlabel: " . "'" . ucwords($genre) . "',\n";
        $color = array_pop($colors);
        echo "\t\tbackgroundColor : \"rgba(" . $color['r'] . "," . $color['g'] . "," . $color['b'] . ",1)\",\n";
        echo "\t\tdata :[";
        foreach ($data[$genre] as $date => $count) {
            echo round($count, 2) . ", ";
        }
        echo "]\n";
        echo "\t},";
    }
    ?>
            ]
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
    <?php
}

function generate_line_graph_html($graph_num, $data, $genre_list) {
    $graph_name = "myChart" . $graph_num;
    $colors = generate_n_distinct_colors(count($data));
    ?>
    <canvas id="<?php echo $graph_name ?>" width="400" height="400"></canvas>
    <script>
        var ctx = document.getElementById("<?php echo $graph_name ?>");
        var data = {
            labels: [<?php
    foreach (end($data) as $date => $stats) {
        echo '"' . $date . '", ';
    }
    ?>],
            datasets: [
    <?php
    foreach ($genre_list as $genre => $ignore) {
        echo "{\n";
        echo "\t\tlabel: " . "'" . ucwords($genre) . "',\n";
        $color = array_pop($colors);
        echo "\t\tbackgroundColor : \"rgba(" . $color['r'] . "," . $color['g'] . "," . $color['b'] . ",1)\",\n";
        echo "\t\tborderColor : \"rgba(" . $color['r'] . "," . $color['g'] . "," . $color['b'] . ",1)\",\n";
        echo "\t\tfill : false,\n";
        echo "\t\tdata :[";
        foreach ($data[$genre] as $date => $count) {
            echo round($count, 2) . ", ";
        }
        echo "]\n";
        echo "\t},";
    }
    ?>
            ]
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
                }
            }
        });
    </script>
    <?php
}
?>
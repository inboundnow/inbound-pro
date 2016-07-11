jQuery(document).ready(function ($) {

    if (typeof (window.data1) === "undefined" || window.data1 === null || window.data1 === "") {
        data1 = [];

    }
    if (typeof (window.data2) === "undefined" || window.data2 === null || window.data2 === "") {
        data2 = [];
    }

    var dataset = [
        {
            label: "Last Month",
            data: data2,
            xaxis: 2,
            color: "#D8D8D8",

            points: {fillColor: "#D8D8D8", show: true},
            lines: {show: true}
        },
        {
            label: "Current Month",
            data: data1,
            color: "#00769c",

            points: {fillColor: "#00769c", show: true},
            lines: {show: true, fill: true, fillColor: 'rgba(216, 216, 216, .3)'}
        }
    ];

    var dayOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thr", "Fri", "Sat"];

    var options = {
        series: {
            shadowSize: 5
        },
        crosshair: {
            mode: "x", color: "rgba(170, 0, 0, 0.80)",
            lineWidth: 1
        },
        xaxes: [{
            show: false,
            mode: "time",
            tickFormatter: function (val, axis) {
                return dayOfWeek[new Date(val).getDay()];
            },
            color: 'none',
            position: "top",
            axisLabel: "Day of week",
            axisLabelUseCanvas: false,
            axisLabel: "<span class='chart-header'>Lead Growth</span><span class='chart-label-one'>Current Month</span> vs. <span class='chart-label-two'>Last Month</span>",
            axisLabelFontSizePixels: 12,
            axisLabelFontFamily: 'Verdana, Arial',
            axisLabelPadding: 5
        },
            {
                mode: "time",
                timeformat: "%m/%d",
                tickSize: [2, "day"],
                color: "D8D8D8",
                axisLabel: false,
                axisLabelUseCanvas: false,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial',
                axisLabelPadding: 10,
                alignTicksWithAxis: 10
            }],
        yaxis: {
            color: "D8D8D8",
            tickDecimals: 0,
            axisLabel: "Lead Count",
            axisLabelUseCanvas: false,
            axisLabelFontSizePixels: 12,
            axisLabelFontFamily: 'Verdana, Arial',
            axisLabelPadding: 5
        },
        legend: {
            show: false,
            noColumns: 0,
            labelFormatter: function (label, series) {
                return "<font color=\"white\">" + label + "</font>";
            },
            backgroundColor: "#000",
            backgroundOpacity: 0.9,
            labelBoxBorderColor: "#000000",
            position: "se"
        },
        grid: {
            hoverable: true,
            autoHighlight: false,
            borderWidth: 2,
            mouseActiveRadius: 60,
            backgroundColor: {colors: ["#ffffff", "#EDF5FF"]},
            axisMargin: 20
        }
    };
    chartplot = jQuery.plot(jQuery("#flot-placeholder"), dataset, options);
    jQuery("#flot-placeholder").UseTooltip_leads();
    var the_data = chartplot.getData();

    var today = new Date();
    var dd = today.getDate();
    var date_exists = the_data[1].data[dd];
    if (typeof(date_exists) != "undefined" && date_exists !== null && date_exists !== "") {
        var getit = the_data[1].data[dd][0]; // gets the crosshair location
    }
});
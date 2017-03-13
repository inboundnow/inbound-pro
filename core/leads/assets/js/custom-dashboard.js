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
    
    if(jQuery("#flot-placeholder").length){ //only render the lead events grid if the div to render it exists
        chartplot = jQuery.plot(jQuery("#flot-placeholder"), dataset, options);
        jQuery("#flot-placeholder").UseTooltip_leads();
        var the_data = chartplot.getData();

        var today = new Date();
        var dd = today.getDate();
        var date_exists = the_data[1].data[dd];
        if (typeof(date_exists) != "undefined" && date_exists !== null && date_exists !== "") {
            var getit = the_data[1].data[dd][0]; // gets the crosshair location
        }
    }

    /**300 ms after page load, add some size parameters to list performance leads popup window**/
    setTimeout(function(){
        var width = window.innerWidth
        || document.documentElement.clientWidth
        || document.body.clientWidth;

        var height = window.innerHeight
        || document.documentElement.clientHeight
        || document.body.clientHeight;                    
        
        if(width < 772){
            var boxWidth = width - 30;
        }else{
            var boxWidth = 772;
        }

        jQuery('a.thickbox').map(function(value, index){ 
            if(index.href.indexOf('width') == -1){
                jQuery(index).attr('href', jQuery(index).attr('href') + '&width=' + boxWidth + '&height=' + (height - 60)); 
            }
        });
    }, 300);

    /**When the widget display settings change, store the change in the options**/
    var wait;
    var widgetLoadStatus = {};
    jQuery('.hide-postbox-tog').on('click', function(){
        var inputs = jQuery('.hide-postbox-tog');
        jQuery.each(inputs, function(input){
            widgetLoadStatus[inputs[input].value] = jQuery(inputs[input]).is(':checked');
        });

        clearTimeout(wait);
        wait = setTimeout(updateDashboardWidgetDisplayStatus, 600);
    }); 
    
    function updateDashboardWidgetDisplayStatus(){
        if(widgetLoadStatus && typeof widgetLoadStatus == 'object'){
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'dashboard_widget_load_status',
                    widget_data: widgetLoadStatus,
                },
                success: function(response){
                  //  console.log(JSON.parse(response));
                },
            
            });
        }
    }               
});

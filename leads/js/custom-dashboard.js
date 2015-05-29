jQuery(document).ready(function($) {
    var list_count = jQuery(".dashboard-lead-lists").length;
    if( list_count < 1) {
        $("#wp-lead-dashboard-list").hide();
    }
   var before_dash = jQuery("#lead-before-dashboard");
   var dashboard_view = $.cookie("dashboard-view-choice");
	jQuery(".wrap h2").after(before_dash);
	jQuery("#lead-before-dashboard").fadeIn(800);

	jQuery("body").on('hover', '.widget-block', function () {
		jQuery('.db-active').removeClass('db-active');
		jQuery(this).addClass('db-active');
    });
    jQuery("body").on('mouseleave', '.widget-block', function () {
		jQuery('.db-active').removeClass('db-active');
    });

    jQuery(".marketing-widget-header").on("click", function(event){

		var link = jQuery(this).find(".toggle-lead-list");
		var conversion_log = jQuery(this).parent().find("#lead-ul").toggle();

		      if (jQuery(conversion_log).is(":visible")) {
		                 link.text('-');
		                 $.cookie("dashboard-view-choice", "show_leads", { path: '/', expires: 7 });
		            } else {
		                 link.text('+');
		                 $.cookie("dashboard-view-choice", "hide_leads", { path: '/', expires: 7 });
		            }
		});
    if(dashboard_view === "hide_leads") {
    	jQuery("#lead-ul").hide();
    	jQuery(".toggle-lead-list").html('<span>(Click to View)</span> +');
    }
    jQuery( '#cd-dropdown' ).dropdown();

 });

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
        xaxis:2,
        color: "#D8D8D8",

        points: { fillColor: "#D8D8D8", show: true },
        lines: { show: true }
    },
    {
        label: "Current Month",
        data: data1,
        color: "#00769c",

        points: { fillColor: "#00769c", show: true },
        lines: { show: true, fill:true, fillColor:'rgba(216, 216, 216, .3)' }
    }
];

var dayOfWeek = ["Sun", "Mon", "Tue", "Wed", "Thr", "Fri", "Sat"];

var options = {
    series: {
        shadowSize: 5
    },
    crosshair: { mode: "x",  color: "rgba(170, 0, 0, 0.80)",
            lineWidth: 1 },
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
        backgroundColor: { colors: ["#ffffff", "#EDF5FF"] },
        axisMargin: 20
    }
};
   chartplot = jQuery.plot(jQuery("#flot-placeholder"), dataset, options);
    jQuery("#flot-placeholder").UseTooltip_leads();
    var the_data = chartplot.getData();

    //console.log(the_data);
    var today = new Date();
	var dd = today.getDate();
	var date_exists = the_data[1].data[dd];
    if(typeof(date_exists) != "undefined" && date_exists !== null && date_exists !== "") {
    var getit = the_data[1].data[dd][0]; // gets the crosshair location
    //console.log(getit);
   // var today = ture;
   // chartplot.lockCrosshair({ x: getit });
    }

    var tourlink = "";
      $("#flot-placeholder").bind("plothover",  function (event, pos, item)
    {
    	 if (item) {
          // Lock the crosshair to the data point being hovered
		// chartplot.lockCrosshair({ x: item.datapoint[0] });
        }
        else {
          // Return normal crosshair operation
        //  chartplot.unlockCrosshair();
        }
    	//plot.lockCrosshair({ x: item.dataIndex[5] });
        //chartplot.setCrosshair({x: pos.x})
       // console.log(item);
       // console.log(item.series.data[6][0]); // The X coordinate for line in 6th item. WORKS
        //console.log(pos.x);
    });
    //jQuery("#wp-lead-stats h3 span").text('test');
});
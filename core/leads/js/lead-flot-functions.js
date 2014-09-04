


function gd(year, month, day) {
    return new Date(year, month - 1, day).getTime();
}

var previousPoint = null, previousLabel = null;
var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

jQuery.fn.UseTooltip_leads = function () {
    jQuery(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                jQuery("#tooltip").remove();
                var which_month = item.series.label;
                if(which_month === "Last Month") {
                    var addition = 0;
                } else {
                    var addition = 1;
                }
                var x = item.datapoint[0];
                var y = item.datapoint[1];
                var date = new Date(x);
                var color = item.series.color;
                var z = item.series.data[previousPoint][2];
                var sign = "";
                var style_class = "";
                if ( z > -1) {
                    sign = "+";
                    var style_class = "lead-growth";
                }
                showTooltip_leads(item.pageX, item.pageY, color,
                             "<span>" + item.series.label + " on " + (date.getMonth() + addition) + "/" + date.getDate() + ":  <br><strong class='" +style_class +"'>" + sign + z + " Leads</strong><hr>Monthly Total <strong>" + y +"</strong> leads</span>" );
                jQuery("#vumtooltip").remove();
            }
        } else {
            jQuery("#tooltip").remove();
            previousPoint = null;
        }
    });
};

function showTooltip_leads(x, y, color, contents) {
    jQuery('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 40,
        left: x - 120,
        border: '2px solid ' + color,
        padding: '3px',
        'font-size': '9px',
        'border-radius': '5px',
        'background-color': '#fff',
        'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.9
    }).appendTo("body").fadeIn(200);
}
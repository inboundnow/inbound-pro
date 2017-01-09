var InboundRulesListingsJs = ( function() {

    var rule_id;

    var construct = {
        /**
         *  Initialize JS Class
         */
        init: function() {
            /* Load Listeners */
            InboundRulesListingsJs.listeners();
        },

        /**
         *  Loads listeners for toggling navigation
         */
        listeners: function() {
            /* Toggles rule status */
            jQuery('body').on( 'click' , '.toggle-rule-status' , function() {
                var status = 'off';
                if (jQuery(this).attr('checked') == 'checked') {
                    status='on';
                }
                /* Run Ajax Call */
                InboundRulesListingsJs.run_ajax({
                    'action' : 'automation_rule_toggle_status',
                    'rule_id' : jQuery(this).data('rule-id'),
                    'status' : status
                } , 'json' );
            });

            /* Clears queued tasks by rule */
            jQuery('body').on( 'click' , '.clear-queued-tasks' , function() {
                InboundRulesListingsJs.rule_id = jQuery(this).data('rule-id')
                swal({
                    title: "Are you sure?",
                    text: "Are you sure you want to delete queued tasks related to this item?",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes, clear them!",
                    closeOnConfirm: false
                }, function(){
                    /* Run Ajax Call */
                    InboundRulesListingsJs.run_ajax({
                        'action' : 'automation_rule_remove_taks',
                        'rule_id' : InboundRulesListingsJs.rule_id,
                    } , 'json' );
                    swal( "Removed!", "Tasks related to this rule have been removed from the queue.", "success");
                });

            });

        },
        /**
         *  Runs AJAX
         */
        run_ajax: function( data, dataType , callback ) {

            jQuery.ajax({
                type: "GET",
                url: ajaxurl,
                dataType: dataType,
                async:true,
                data : data,
                success: function( result ) {
                    if (callback) {
                        InboundRulesListingsJs[callback](result);
                    }
                }
            });
        },
    }


    return construct;

})();


/**
 *  Once dom has been loaded load listeners and initialize components
 */
jQuery(document).ready(function() {

    InboundRulesListingsJs.init();

});
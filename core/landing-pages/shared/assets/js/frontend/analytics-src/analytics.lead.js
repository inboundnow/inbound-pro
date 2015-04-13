/**
 * Leads API functions
 * @param  Object _inbound - Main JS object
 * @return Object - include event triggers
 */
var _inboundLeadsAPI = (function(_inbound) {
    var httpRequest;
    _inbound.LeadsAPI = {
        init: function() {

            var utils = _inbound.Utils,
                wp_lead_uid = utils.readCookie("wp_lead_uid"),
                wp_lead_id = utils.readCookie("wp_lead_id"),
                expire_check = utils.readCookie("lead_session_expire"); // check for session

            if (!expire_check) {
                _inbound.deBugger('leads', 'expired vistor. Run Processes');
                //var data_to_lookup = global-localized-vars;
                if (wp_lead_id) {
                    /* Get InboundLeadData */
                    _inbound.LeadsAPI.getAllLeadData();
                }
            }
        },
        setGlobalLeadData: function(data) {
            InboundLeadData = data;
        },
        getAllLeadData: function(expire_check) {
            var wp_lead_id = _inbound.Utils.readCookie("wp_lead_id"),
                leadData = _inbound.totalStorage('inbound_lead_data'),
                leadDataExpire = _inbound.Utils.readCookie("lead_data_expire");
            data = {
                action: 'inbound_get_all_lead_data',
                wp_lead_id: wp_lead_id
            },
            success = function(returnData) {
                var leadData = JSON.parse(returnData);
                _inbound.LeadsAPI.setGlobalLeadData(leadData);
                _inbound.totalStorage('inbound_lead_data', leadData); // store lead data

                /* Set 3 day timeout for checking DB for new lead data for Lead_Global var */
                var d = new Date();
                d.setTime(d.getTime() + 30 * 60 * 1000);
                var expire = _inbound.Utils.addDays(d, 3);
                _inbound.Utils.createCookie("lead_data_expire", true, expire);

            };

            if (!leadData) {
                // Get New Lead Data from DB
                _inbound.Utils.ajaxPost(inbound_settings.admin_url, data, success);

            } else {
                // set global lead var with localstorage data
                _inbound.LeadsAPI.setGlobalLeadData(leadData);
                _inbound.deBugger('lead', 'Set Global Lead Data from Localstorage');

                if (!leadDataExpire) {
                    _inbound.Utils.ajaxPost(inbound_settings.admin_url, data, success);
                    //console.log('Set Global Lead Data from Localstorage');
                     _inbound.deBugger('lead', 'localized data old. Pull new from DB');
                    //console.log('localized data old. Pull new from DB');
                }
            }

        },
        getLeadLists: function() {
            var wp_lead_id = _inbound.Utils.readCookie("wp_lead_id");
            var data = {
                action: 'wpl_check_lists',
                wp_lead_id: wp_lead_id
            };
            var success = function(user_id) {
                _inbound.Utils.createCookie("lead_session_list_check", true, {
                    path: '/',
                    expires: 1
                });
                _inbound.deBugger('lead', "Lists checked");
                //console.log("Lists checked");
            };
            //_inbound.Utils.doAjax(data, success);
            _inbound.Utils.ajaxPost(inbound_settings.admin_url, data, success);
        }
    };

    return _inbound;

})(_inbound || {});
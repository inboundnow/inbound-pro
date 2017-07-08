/**
 * # Start
 *
 * Runs init functions
 *
 * @author David Wells <david@inboundnow.com>
 * @author Hudson Atwell <hudson@inboundnow.com>
 * @version 0.0.2
 */


/* Initialize _inbound */
 _inbound.init();

/* Set Global Lead Data */
InboundLeadData = _inbound.totalStorage('inbound_lead_data') || null;


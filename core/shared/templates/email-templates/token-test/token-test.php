<?php

$inbound_email_templates['token-test'] = '

<h2>'. __( 'Core Tokens', 'inbound-pro' ) .'</h2>
<p>'. __( 'Admin Email Address' , INBOUNDNOW_TEXT_DOMAIN ) .':{{admin-email-address}}</p>
<p>'. __( 'Site Name' , INBOUNDNOW_TEXT_DOMAIN ) .':{{site-name}}</p>
<p>'. __( 'Site Url' , INBOUNDNOW_TEXT_DOMAIN ) .':{{site-url}}</p>
<p>'. __( 'Date-time' , INBOUNDNOW_TEXT_DOMAIN ) .': {{date-time}}</p>
<p>'. __( 'Leads URL Path' , INBOUNDNOW_TEXT_DOMAIN ) .': {{leads-urlpath}}</p>
<p>'. __( 'Landing Pages URL Path' , INBOUNDNOW_TEXT_DOMAIN ) .': {{landingpages-urlpath}}</p>

<h2>'. __( 'Lead Tokens' , INBOUNDNOW_TEXT_DOMAIN ) .'</h2>
<p>'. __( 'First Name' , INBOUNDNOW_TEXT_DOMAIN ) .': {{lead-first-name}}</p>
<p>'. __( 'Last Name' , INBOUNDNOW_TEXT_DOMAIN ) .':{{lead-last-name}}</p>
<p>'. __( 'Email' , INBOUNDNOW_TEXT_DOMAIN ) .': {{lead-email-address}}</p>
<p>'. __( 'Company Name' , INBOUNDNOW_TEXT_DOMAIN ) .': {{lead-company-name}}</p>
<p>'. __( 'Address Line 1' , INBOUNDNOW_TEXT_DOMAIN ) .': {{lead-address-line-1}}</p>
<p>'. __( 'Address Line 2' , INBOUNDNOW_TEXT_DOMAIN ) .': {{lead-address-line-2}}</p>
<p>'. __( 'City' , INBOUNDNOW_TEXT_DOMAIN ) .': {{lead-city}}</p>
<p>'. __( 'State/Region' , INBOUNDNOW_TEXT_DOMAIN ) .': {{lead-region}}</p>
<p>'. __( 'Form Name' , INBOUNDNOW_TEXT_DOMAIN ) .':{{form-name}}</p>
<p>'. __( 'Converted Page URL' , INBOUNDNOW_TEXT_DOMAIN ) .': {{source}}</p>

<h2>'. __( 'WP User Tokens' , INBOUNDNOW_TEXT_DOMAIN ) .'</h2>
<p>'. __( 'WordPress User ID' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-user-id}}</p>
<p>'. __( 'WordPress User Username' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-user-username}}</p>
<p>'. __( 'WordPress User First Name' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-user-first-name}}</p>
<p>'. __( 'WordPress User Last Name' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-user-last-name}}</p>
<p>'. __( 'WordPress User Password' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-user-password}}</p>
<p>'. __( 'WordPress User Nicename' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-user-nicename}}</p>
<p>'. __( 'WordPress User Display Name' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-user-displayname}}</p>
<p>'. __( 'WordPress User Gravatar URL' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-user-gravatar-url}}</p>


<h2>'. __( 'WP Post Tokens' , INBOUNDNOW_TEXT_DOMAIN ) .'</h2>
<p>'. __( 'WordPress Post ID' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-post-id}}</p>
<p>'. __( 'WordPress Post Title' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-post-title}}</p>
<p>'. __( 'WordPress Post URL' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-post-url}}</p>
<p>'. __( 'WordPress Post Content' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-post-content}}</p>
<p>'. __( 'WordPress Post Excerpt' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-post-excerpt}}</p>


<h2>'. __( 'WP Comment Tokens' , INBOUNDNOW_TEXT_DOMAIN ) .'</h2>
<p>'. __( 'WordPress Comment ID' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-comment-id}}</p>
<p>'. __( 'WordPress Comment URL' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-comment-url}}</p>
<p>'. __( 'WordPress Comment Author' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-comment-author}}</p>
<p>'. __( 'WordPress Comment Author Email' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-comment-author-email}}</p>
<p>'. __( 'WordPress Comment Author IP' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-comment-author-ip}}</p>
<p>'. __( 'WordPress Comment Content' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-comment-content}}</p>
<p>'. __( 'WordPress Comment Date' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-comment-date}}</p>
<p>'. __( 'WordPress Comment Karma' , INBOUNDNOW_TEXT_DOMAIN ) .': {{wp-comment-karma}}</p>


';
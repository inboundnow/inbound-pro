<?php

$inbound_email_templates['token-test'] = '

<h2>'. __( 'Core Tokens', 'inbound-pro' ) .'</h2>
<p>'. __( 'Admin Email Address' , 'inbound-pro' ) .':{{admin-email-address}}</p>
<p>'. __( 'Site Name' , 'inbound-pro' ) .':{{site-name}}</p>
<p>'. __( 'Site Url' , 'inbound-pro' ) .':{{site-url}}</p>
<p>'. __( 'Date-time' , 'inbound-pro' ) .': {{date-time}}</p>
<p>'. __( 'Leads URL Path' , 'inbound-pro' ) .': {{leads-urlpath}}</p>
<p>'. __( 'Landing Pages URL Path' , 'inbound-pro' ) .': {{landingpages-urlpath}}</p>

<h2>'. __( 'Lead Tokens' , 'inbound-pro' ) .'</h2>
<p>'. __( 'First Name' , 'inbound-pro' ) .': {{lead-first-name}}</p>
<p>'. __( 'Last Name' , 'inbound-pro' ) .':{{lead-last-name}}</p>
<p>'. __( 'Email' , 'inbound-pro' ) .': {{lead-email-address}}</p>
<p>'. __( 'Company Name' , 'inbound-pro' ) .': {{lead-company-name}}</p>
<p>'. __( 'Address Line 1' , 'inbound-pro' ) .': {{lead-address-line-1}}</p>
<p>'. __( 'Address Line 2' , 'inbound-pro' ) .': {{lead-address-line-2}}</p>
<p>'. __( 'City' , 'inbound-pro' ) .': {{lead-city}}</p>
<p>'. __( 'State/Region' , 'inbound-pro' ) .': {{lead-region}}</p>
<p>'. __( 'Form Name' , 'inbound-pro' ) .':{{form-name}}</p>
<p>'. __( 'Converted Page URL' , 'inbound-pro' ) .': {{source}}</p>

<h2>'. __( 'WP User Tokens' , 'inbound-pro' ) .'</h2>
<p>'. __( 'WordPress User ID' , 'inbound-pro' ) .': {{wp-user-id}}</p>
<p>'. __( 'WordPress User Username' , 'inbound-pro' ) .': {{wp-user-username}}</p>
<p>'. __( 'WordPress User First Name' , 'inbound-pro' ) .': {{wp-user-first-name}}</p>
<p>'. __( 'WordPress User Last Name' , 'inbound-pro' ) .': {{wp-user-last-name}}</p>
<p>'. __( 'WordPress User Password' , 'inbound-pro' ) .': {{wp-user-password}}</p>
<p>'. __( 'WordPress User Nicename' , 'inbound-pro' ) .': {{wp-user-nicename}}</p>
<p>'. __( 'WordPress User Display Name' , 'inbound-pro' ) .': {{wp-user-displayname}}</p>
<p>'. __( 'WordPress User Gravatar URL' , 'inbound-pro' ) .': {{wp-user-gravatar-url}}</p>


<h2>'. __( 'WP Post Tokens' , 'inbound-pro' ) .'</h2>
<p>'. __( 'WordPress Post ID' , 'inbound-pro' ) .': {{wp-post-id}}</p>
<p>'. __( 'WordPress Post Title' , 'inbound-pro' ) .': {{wp-post-title}}</p>
<p>'. __( 'WordPress Post URL' , 'inbound-pro' ) .': {{wp-post-url}}</p>
<p>'. __( 'WordPress Post Content' , 'inbound-pro' ) .': {{wp-post-content}}</p>
<p>'. __( 'WordPress Post Excerpt' , 'inbound-pro' ) .': {{wp-post-excerpt}}</p>


<h2>'. __( 'WP Comment Tokens' , 'inbound-pro' ) .'</h2>
<p>'. __( 'WordPress Comment ID' , 'inbound-pro' ) .': {{wp-comment-id}}</p>
<p>'. __( 'WordPress Comment URL' , 'inbound-pro' ) .': {{wp-comment-url}}</p>
<p>'. __( 'WordPress Comment Author' , 'inbound-pro' ) .': {{wp-comment-author}}</p>
<p>'. __( 'WordPress Comment Author Email' , 'inbound-pro' ) .': {{wp-comment-author-email}}</p>
<p>'. __( 'WordPress Comment Author IP' , 'inbound-pro' ) .': {{wp-comment-author-ip}}</p>
<p>'. __( 'WordPress Comment Content' , 'inbound-pro' ) .': {{wp-comment-content}}</p>
<p>'. __( 'WordPress Comment Date' , 'inbound-pro' ) .': {{wp-comment-date}}</p>
<p>'. __( 'WordPress Comment Karma' , 'inbound-pro' ) .': {{wp-comment-karma}}</p>


';
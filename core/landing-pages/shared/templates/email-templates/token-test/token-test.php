<?php

$inbound_email_templates['token-test'] = '

<h2>'. __( 'Core Tokens', 'ma' ) .'</h2>
<p>'. __( 'Admin Email Address' , 'ma' ) .':{{admin-email-address}}</p>
<p>'. __( 'Site Name' , 'ma' ) .':{{site-name}}</p>
<p>'. __( 'Site Url' , 'ma' ) .':{{site-url}}</p>
<p>'. __( 'Date-time' , 'ma' ) .': {{date-time}}</p>
<p>'. __( 'Leads URL Path' , 'ma' ) .': {{leads-urlpath}}</p>
<p>'. __( 'Landing Pages URL Path' , 'ma' ) .': {{landingpages-urlpath}}</p>

<h2>'. __( 'Lead Tokens' , 'ma' ) .'</h2>
<p>'. __( 'First Name' , 'ma' ) .': {{lead-first-name}}</p>
<p>'. __( 'Last Name' , 'ma' ) .':{{lead-last-name}}</p>
<p>'. __( 'Email' , 'ma' ) .': {{lead-email-address}}</p>
<p>'. __( 'Company Name' , 'ma' ) .': {{lead-company-name}}</p>
<p>'. __( 'Address Line 1' , 'ma' ) .': {{lead-address-line-1}}</p>
<p>'. __( 'Address Line 2' , 'ma' ) .': {{lead-address-line-2}}</p>
<p>'. __( 'City' , 'ma' ) .': {{lead-city}}</p>
<p>'. __( 'Region/State' , 'ma' ) .': {{lead-region}}</p>
<p>'. __( 'Form Name' , 'ma' ) .':{{form-name}}</p>
<p>'. __( 'Converted Page URL' , 'ma' ) .': {{source}}</p>

<h2>'. __( 'WP User Tokens' , 'ma' ) .'</h2>
<p>'. __( 'WordPress User ID' , 'ma' ) .': {{wp-user-id}}</p>
<p>'. __( 'WordPress User Username' , 'ma' ) .': {{wp-user-username}}</p>
<p>'. __( 'WordPress User First Name' , 'ma' ) .': {{wp-user-first-name}}</p>
<p>'. __( 'WordPress User Last Name' , 'ma' ) .': {{wp-user-last-name}}</p>
<p>'. __( 'WordPress User Password' , 'ma' ) .': {{wp-user-password}}</p>
<p>'. __( 'WordPress User Nicename' , 'ma' ) .': {{wp-user-nicename}}</p>
<p>'. __( 'WordPress User Display Name' , 'ma' ) .': {{wp-user-displayname}}</p>
<p>'. __( 'WordPress User Gravatar URL' , 'ma' ) .': {{wp-user-gravatar-url}}</p>


<h2>'. __( 'WP Post Tokens' , 'ma' ) .'</h2>
<p>'. __( 'WordPress Post ID' , 'ma' ) .': {{wp-post-id}}</p>
<p>'. __( 'WordPress Post Title' , 'ma' ) .': {{wp-post-title}}</p>
<p>'. __( 'WordPress Post URL' , 'ma' ) .': {{wp-post-url}}</p>
<p>'. __( 'WordPress Post Content' , 'ma' ) .': {{wp-post-content}}</p>
<p>'. __( 'WordPress Post Excerpt' , 'ma' ) .': {{wp-post-excerpt}}</p>


<h2>'. __( 'WP Comment Tokens' , 'ma' ) .'</h2>
<p>'. __( 'WordPress Comment ID' , 'ma' ) .': {{wp-comment-id}}</p>
<p>'. __( 'WordPress Comment URL' , 'ma' ) .': {{wp-comment-url}}</p>
<p>'. __( 'WordPress Comment Author' , 'ma' ) .': {{wp-comment-author}}</p>
<p>'. __( 'WordPress Comment Author Email' , 'ma' ) .': {{wp-comment-author-email}}</p>
<p>'. __( 'WordPress Comment Author IP' , 'ma' ) .': {{wp-comment-author-ip}}</p>
<p>'. __( 'WordPress Comment Content' , 'ma' ) .': {{wp-comment-content}}</p>
<p>'. __( 'WordPress Comment Date' , 'ma' ) .': {{wp-comment-date}}</p>
<p>'. __( 'WordPress Comment Karma' , 'ma' ) .': {{wp-comment-karma}}</p>


';
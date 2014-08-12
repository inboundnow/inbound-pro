<style type="text/css">
.wp_cta_container {
  text-align: center;
  font-family: 'proxima_nova_regular', arial, sans-serif;

}
.wp_cta_container #cta-link {

}
.wp_cta_container .button {
  display: block;
  cursor: pointer;
  width: 200px;
  font-size: 22px;
  margin: auto;
  margin-top: 15px;
  margin-bottom: 15px;
  height: 50px;
  line-height: 50px;
  text-transform: uppercase;
  background: #db3d3d;
  border-bottom: 3px solid #c12424;
  color: #ffffff;
  text-decoration: none;
  border-radius: 5px;
  transition: all 0.4s ease-in-out;
}

.wp_cta_container  .button:hover {
  background: #c12424;
  border-bottom: 3px solid #db3d3d;
}

.wp_cta_container  .clicked {
  transform: rotateY(-80deg);
}

#cta_container {
	background-color: #{{content-background-color}};
	padding-top:28px;
	padding-bottom:30px;
	padding-left:20px;
	padding-right:20px;
	text-align: center;
	overflow: hidden;
}

#cta_container #main-headline {
	color:#{{headline-text-color}};
	font-size: 20px;
	margin-bottom: 20px;
}
.cta_content {
	padding-bottom: 5px;
}
.cta_button, #cta_container input[type="button"], #cta_container button[type="submit"], #cta_container input[type="submit"] {
	background: #{{submit-button-color}};
	color: #{{submit-button-text-color}};

	text-decoration: none;
	font-size: 15px;
	line-height: 40px;
	padding: 0 24px;
	background: transparent;
	border: solid 2px #{{submit-button-text-color}};
	border-radius: 70px;
	font-weight: bold;
	font-family: 'proxima_nova_regular', arial, sans-serif;
	display:inline-block;
}
#cta_container form input[type="button"], #cta_container form button[type="submit"], #cta_container form input[type="submit"] {
	margin: auto;
	width: 91%;
	display: block;
	font-size: 1.3em;
}
.cta_button:hover {
	border: 2px;
	border-style: solid;
	border-color: #{{submit-button-text-color|brightness(30)}};
}
.wp_cta_container  h1#main-headline {
	color: #{{headline-text-color}};
	margin-top: 0px;
	padding-top: 10px;
	line-height: 36px;
	margin-bottom: 10px;

}
#cta_container a {
	text-decoration: none;
}
.cta_content input[type=text], .cta_content input[type=url], .cta_content input[type=email], .cta_content input[type=tel], .cta_content input[type=number], .cta_content input[type=password] {
	width:90%;
}
form  {
	max-width: 330px;
	margin: auto;
}
#cta_content_left, #cta_content_right {
	float: left;
	display: inline-block;
	width: 50%;
}
</style>

<div id='cta_container'>
  <div id="cta_content_left">
	  <h1 id='main-headline'>{{header-text}}</h1>
		<a id='cta-link' href='{{submit-button-link}}'>
			<span class='cta_button'>
			{{submit-button-text}}
			</span>
		</a>
	</div>
	<div id="cta_content_right">
		<a href='{{submit-button-link}}'><img src="{{image-url}}"></a>
	</div>
</div>
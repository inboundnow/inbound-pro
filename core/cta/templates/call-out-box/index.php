<style>

#cta_container #content 
{
	background: transparent;
}
#cta_container {
	margin:auto; 

}
#cta_container #content {
	width: 400px;
	background: #222;
	padding-bottom: 15px;
}

#cta_container p {

	text-align: {{content-alignment}};
	color:#fff;
}

#cta_container p:first-child {
	margin-top: 0px;
	padding-top: 0px;
}
#cta_container p:last-child {
	margin-bottom: 0px;
	padding-bottom: 0px;
}


/*@import url(https://fonts.googleapis.com/css?family={{font}}:300,400,600); */ 
#cta_container {
  text-align: {{content-alignment}};
  font-family: Calibri, Helvetica, Arial, sans-serif;
  font-weight: 300;
}

#cta_container .the_content {
	font-family: Calibri, Helvetica, Arial, sans-serif;
	padding-left: 10px;
	padding-right: 10px;
	padding: 10px;
	display: block;
	width: 80%;
	margin: auto;
}
#cta_container #cta-link {
  text-decoration: none;
}
#cta_container .button {
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

#cta_container  .button:hover {
  background: #c12424;
  border-bottom: 3px solid #db3d3d;
}

#cta_container  .clicked {
  transform: rotateY(-80deg);
}

#cta_container {
	background-color: #{{content-background-color}};
	padding-top:28px;
	padding-bottom:30px;
	padding-left:20px;
	padding-right:20px;
	color: #{{content-text-color}};
	text-align: {{content-alignment}};
}
.cta_content h1,.cta_content h2,.cta_content h3,.cta_content h4,.cta_content h5,.cta_content h6 {
	color: #{{content-text-color}};
}
#cta_container #main-headline {
	color:#{{headline-text-color}};
}
.cta_content {
	padding-bottom: 5px;
}
.cta_button, #cta_container input[type="button"], #cta_container button[type="submit"], #cta_container input[type="submit"] {
	text-align:center;
	background: #{{submit-button-color}};
	border-bottom: 3px solid {{submit-button-color|brightness(55)}};
	color: #{{submit-button-text-color}};
	padding-left:20px;
	padding-right:20px;
	padding-top:7px;
	padding-bottom:7px;
	text-decoration: none;
	border-radius: 5px;
	transition: all 0.4s ease-in-out;
	margin-top: 10px;
	display: block;
	font-size: 1.3em;
}
#cta_container form input[type="button"], #cta_container form button[type="submit"], #cta_container form input[type="submit"] {
	margin: auto;
	width: 91%;
	display: block;
	font-size: 1.3em;
}
.cta_button:hover, #cta_container input[type="button"]:hover, #cta_container button[type="submit"]:hover, #cta_container input[type="submit"]:hover {
	background: {{submit-button-color|brightness(55)}};
	border-bottom: 3px solid #{{submit-button-color}};
}
#cta_container  h1#main-headline {
	color: #{{headline-text-color}};
	margin-top: 0px;
	padding-top: 10px;
	line-height: 36px;
	margin-bottom: 10px;
	font-weight: 300;
	font-size:20px;
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
</style>

<div id='cta_container'>
  <h1 id='main-headline'>{{header-text}}</h1>

    <div class='cta_content'>
	{{content-text}}
    </div>
{% if {{ show-button }} == true %}
		<a id='cta-link' href='{{submit-button-link}}'>
			<span class='cta_button'>
			{{submit-button-text}}
			</span>
		</a>
{% endif %}
</div>
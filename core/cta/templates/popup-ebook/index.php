<style type="text/css">
#inbound-wrapper {
  width: {{width}};

}
#inbound-hero {
  width: 35%;
float: left;
margin-left: 20px;
position: relative;
}
#inbound-content {
width: 50%;
float: left;
position: relative;
padding:20px;
}
.inbound-hero-img {
  width: 100%;
}
#inbound-wrapper h1 {
font-size: 23px;
margin-top: 5px;
margin-bottom: 15px;
padding: 20px;
padding-bottom: 0px;
}
#inbound-wrapper p, #inbound-wrapper li {
font-size: 14px;
line-height: 1.4;
margin: 0 0 1.4em;
}
#inbound-wrapper li {
  margin-bottom: 0px;
}
.divider_line {
  clear: both;
}
.inbound-horizontal input {
margin-right: 10px;
padding: 3px;
padding-top: 2px;

}
.inbound-horizontal {
display: inline-block;
vertical-align: middle;
}
.inbound-horizontal label {
margin-right: 5px;
font-size: 20px;
vertical-align: middle;
}
#inbound-form-wrapper input[type="submit"] {
background: #E14D4D;
border: none;
border-radius: 5px;
color: #FFF;
font-size: 23px;
font-weight: bold;
padding: 0px;
padding-left: 10px;
text-align: center;
vertical-align: top;
padding-right: 10px;
margin-bottom: 4px;
}
#inbound-form-wrapper input[type="submit"]:hover {
  background: #f15958;
}
#inbound-wrapper { background-color: {{content-color|color}}; }

#inbound-content { color: #{{content-text-color}}; }
#content, #content-wrapper p { color: #{{content-text-color}}; }
.inbound-horizontal label { color: #{{content-text-color}}; }

#inbound-wrapper h1 {color:#{{headline-text-color}}; }
.button {background: #{{submit-button-color}}; border-bottom: 3px solid {{submit-button-color|brightness(90)}}; }
.button:hover {background: {{submit-button-color|brightness(90)}}; border-bottom: 3px solid #{{submit-button-color}}; }

.button {color: {{submit-button-text-color}}; }
#inbound-form-wrapper {
  max-width: 100%;
}
#inbound-form-wrapper form {
  text-align: center;

}
h1#main-headline {
  color: #{{content-text-color}};
  margin-top: 0px;
  padding-top: 10px;
  line-height: 36px;
  margin-bottom: 10px;
}
</style>

<div id="inbound-wrapper">
  <h1>{{header-text}}</h1>
  <div id="inbound-content">
  {{main_content}}
  </div>
  <div id="inbound-hero">
  <img class='inbound-hero-img' src="{{hero}}">
  </div>

    <div class="divider_line"></div>

    <div id="inbound-form-wrapper">
     {{form_content}}
    </div>

</div>
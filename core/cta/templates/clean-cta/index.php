<style type="text/css">
#inbound-wrapper-clean h1, #inbound-wrapper-clean p {
  margin-bottom: 20px;
  color: #333;
}
#inbound-wrapper-clean h1 {
  margin-top: 2.5rem;
  margin-bottom: 0;
  text-align: center;
}
#inbound-wrapper-clean p {
  margin: 0 auto 0.5rem;
  text-align: center;
}
#inbound-wrapper-clean.card {

 -webkit-border-radius: 4px;
 -moz-border-radius: 4px;
  border-radius: 4px;
  box-shadow: 0 2px 0 rgba(0, 0, 0, 0.03);
}
.button-list {
  text-align: center;
  list-style: none;
  line-height: 2.5rem;
  padding-bottom: 10px;
  padding-top: 5px;
}
.button-item {
  display: inline-block;
  margin: 0 0.25em;
  height: 2.5rem;
}
.button {
  display: inline-block;
  height: inherit;
}
.cover {
  overflow: hidden;
  -webkit-border-radius: 4px 4px 0px 0px;
  -moz-border-radius: 4px 4px 0px 0px;
  border-radius: 4px 4px 0px 0px;
  padding: 0 0 1.5rem;
  background: #666 url('{{header-image}}') center center no-repeat;
  background-size: cover;
  background-position: top;
  height: {{header-height}};
}
#inbound-wrapper-clean .cover h1 {
  color: {{header-text-color|color}};
  text-shadow: 1px 1px 0px rgba(150, 150, 150, 0.59);
}
.cover #clean-sub-head-text, #clean-sub-head-text h1,#clean-sub-head-text h2,#clean-sub-head-text h3,#clean-sub-head-text h4,#clean-sub-head-text h5,#clean-sub-head-text h6 {
  color: {{sub-header-text-color|color}};
}
.clean-button {
  display:block;
  margin: auto;
  text-align: center;
  border-radius:4px;
  text-decoration: none;
  font-family:'Lucida Grande',helvetica;
  background: {{submit-button-color|color}};
  -webkit-box-shadow: 0 4px 0 0 {{submit-button-color|brightness(70)}};
  -moz-box-shadow: 0 4px 0 0 {{submit-button-color|brightness(70)}};
  box-shadow: 0 4px 0 0 {{submit-button-color|brightness(70)}};
  font-size: 20px !important;
  padding: 15px 20px;
  width: 250px;
  color: {{submit-button-text-color|color}};
}
#cta-button.clean-button:hover { background: {{submit-button-color|brightness(80)}}; }
#inbound-wrapper-clean {background-color: {{content-color|color}};}
#inbound-wrapper-clean .btn { border: 3px solid {{submit-button-color|color}}; color: {{submit-button-color|color}};}
#inbound-wrapper-clean .btn:hover, .inbound-wrapper-clean .btn:active { color: #{{content-color}}; background: {{submit-button-color|color}};}
#inbound-content p {color:{{content-text-color|color}};}
#clean-bottom-text {
  padding: 10px;
  font-size: 14px;
  text-align: left;
  color: #{{bottom-text-color}};
}
#clean-sub-head-text {
  text-align: center;
  padding: 10px;
  padding-left: 30px;
  padding-right: 30px;
}

#cta-button {
  border: none;
}

</style>
<div id="inbound-wrapper-clean" class="card">
  <div class="cover">

    <h1>{{ header-text }}</h1>

    <div id="clean-sub-head-text">{{sub-header-text}}</div>
  </div>
  <div class="button-list">
    <a href="{{link_url}}" id="cta-button" class="clean-button" href="#">{{submit-button-text}}</a>
  </div>
</div>
<div id="clean-bottom-text">{{bottom-text}}</div>

<style type="text/css">
#calling-template-variables, #dynamic-template-tokens{
  padding: 10px;
  font-size: 14px;
  text-align: left;
  color: #{{color_picker_id}};
}
#demo-cta-content-wrapper {
  background: #FCFCFC;
  border: 5px solid;
  padding: 10px;
  padding-top: 0px;
}
#demo-cta-content-wrapper h2 {
  font-size: 17px;
  margin-bottom: 0px;
  text-decoration: underline;

}
#dynamic-template-tokens {
  background: {{color_picker_id|brightness(80)}} /* Darken color function */
}
#dynamic-template-tokens h2 {
  color:{{color_picker_id|brightness(20)}}; /* lighten color function */
}
#dynamic-template-color-tokens div {
  width: 100%;
  padding-left: 5px;
}
#conditional-template-tokens span {
font-size: 12px;
}
</style>

<div id="demo-cta-content-wrapper">
  <div id="content">

      <div id="calling-template-variables">
        <h2>Calling Normal Template Tokens</h2>
        <!-- Text Field Label: Text field Description. Defined in config.php on line 44 -->
        Text Box content: <strong>{{text_box_id}}</strong><br>
        
        <!-- Textarea Label: Text field Description. Defined in config.php on line 50 -->
        Textarea content: <strong>{{textarea_id}}</strong><br>
        
        <!-- Template body color: Text field Description. Defined in config.php on line 56 -->
        Color Picker Hex: <strong>{{color_picker_id}}</strong><br>
        
        <!--  Radio Label: Text field Description. Defined in config.php on line 62 -->
        Radio Value: <strong>{{radio_id_here}}</strong><br>
        
       <!-- Example Checkbox Label: Text field Description. Defined in config.php on line 70 -->
        Checkbox Value: <strong>{{checkbox_id_here}}</strong><br>
        
        <!--  Dropdown Label: Text field Description. Defined in config.php on line 78 -->
        Dropdown Value: <strong>{{dropdown_id_here}}</strong><br>
        
        <!--  Date Picker Label: Text field Description. Defined in config.php on line 85 -->
        Date Picker Value: <strong>{{date_picker}}</strong><br>
        
        <!--  Main Content Box 2: Text field Description. Defined in config.php on line 91 -->
        WYSIWYG editor content: <strong>{{wysiwyg_id}}</strong><br>
        
        <!--  File/Image Upload Label: Text field Description. Defined in config.php on line 97 -->
        Media upload path: <strong>{{media_id}}</strong><br>
      </div>

      <div id="conditional-template-tokens">

       <h2>Conditional If template Example</h2>
        <span>Using the option named 'boolean' from config. This conditionally shows/hides template data</span>
          {% if {{ boolean }} == false %}
          <p>This is a conditional. The boolean is currently false.</p>
          {% endif %}

      </div>

      <div id="dynamic-template-tokens">
        <h2>Dynamic Template Tokens</h2>
        {%php $test = {{ boolean }}; if ($test) { return 'Boolean set to true'; } else { return 'Boolean set to false'; } %} <br>
        {%php return cta_example_template_function(); %}
      </div>
      <div id="dynamic-template-color-tokens">
        <h2>Dynamic Color Tokens</h2>
        <pre>{{color_picker_id|brightness(5)}}</pre>
        <div style="background: {{color_picker_id|brightness(5)}};">Brightness 5%</div>
        <div style="background: {{color_picker_id|brightness(10)}};">Brightness 10%</div>
        <div style="background: {{color_picker_id|brightness(15)}};">Brightness 15%</div>
        <div style="background: {{color_picker_id|brightness(20)}};">Brightness 20%</div>
        <div style="background: {{color_picker_id|brightness(25)}};">Brightness 25%</div>
        <div style="background: {{color_picker_id|brightness(30)}};">Brightness 30%</div>
        <div style="background: {{color_picker_id|brightness(35)}};">Brightness 35%</div>
        <div style="background: {{color_picker_id|brightness(40)}};">Brightness 40%</div>
        <div style="background: {{color_picker_id|brightness(45)}};">Brightness 45%</div>
        <div style="background: {{color_picker_id|brightness(50)}};"><span style="color: {{color_picker_id|brightness(5)}};">Orignal Color #{{color_picker_id}}</span></div>
        <div style="background: {{color_picker_id|brightness(55)}};">Brightness 55%</div>
        <div style="background: {{color_picker_id|brightness(60)}};">Brightness 60%</div>
        <div style="background: {{color_picker_id|brightness(65)}};">Brightness 65%</div>
        <div style="background: {{color_picker_id|brightness(70)}};">Brightness 70%</div>
        <div style="background: {{color_picker_id|brightness(75)}};">Brightness 75%</div>
        <div style="background: {{color_picker_id|brightness(80)}};">Brightness 80%</div>
        <div style="background: {{color_picker_id|brightness(85)}};">Brightness 85%</div>
        <div style="background: {{color_picker_id|brightness(90)}};">Brightness 90%</div>
        <div style="background: {{color_picker_id|brightness(95)}};">Brightness 95%</div>
        <div style="background: {{color_picker_id|brightness(100)}};">Brightness 100%</div>

      </div>
      <div id="misc-tokens">
        Template URL path {{template-urlpath}}
      </div>
    </div><!-- end #content -->


</div> <!-- end #content-wrapper -->

</body>
</html>

# How to Create Landing Page Templates Using ACF
Author: Giulio Daprela <giulio.daprela@gmail.com>

Version: 0.0.1 
[in]: http://www.inboundnow.com/

Support:
[docs.inboundnow.com](http://docs.inboundnow.com/)

##Intro
In the latest versions of the InboundNow suite of plugins we changed our templating system, and switched from a proprietary engine to the Advanced Custom Fields plugin. There are several advantages coming from this switch:

* possibility of using advanced fields such as repeater fields and flexible fields
* use of a well kown, reputable and stable technology
* lower learning curve for developers that want to build their own templates.

Thanks to the new templating engine it's possible for the end user to create complex, beautiful and professional landing pages, call to actions and emails without writing a single line of code.

This guide has been created for developers, and assumes that you know how to use ACF as a developer. Therefore a lot of information of how to get the fields has been omitted. If you are new to the ACF plugin, the ACF website has extensive documentation about the API, and guides and tutorials for developers. Please go to the [resources](http://www.advancedcustomfields.com/resources/) page on the ACF website to get access to all the documentation. You can also find guides and tutorials everywhere on the web. Here we list a few

* https://code.tutsplus.com/tutorials/getting-started-with-advanced-custom-fields--cms-22126
* http://thestizmedia.com/front-end-posting-with-acf-pro/
* http://thestizmedia.com/acf-pro-simple-local-avatars/
* http://eastbaywp.com/2015/04/april-2015-acf-pro-links/ (a collection of resources)
* http://www.sitepoint.com/getting-started-with-advanced-custom-fields/
* https://www.stormconsultancy.co.uk/blog/development/dev-tutorials/creating-flexible-content-with-advanced-custom-fields/
* http://www.elegantthemes.com/blog/tips-tricks/how-to-create-wordpress-custom-fields
* https://code.tutsplus.com/tutorials/create-a-simple-crm-in-wordpress-advanced-custom-fields--cms-20049 (multi part guide)

##Planning the Template
In order to build a dynamic template for InboundNow you need to have first the static html template already developed. You can do it yourself with one of the many free and paid editors available, or buy it. At this point you need to decide what you want to be able to configure in your template. Tha main things that can be customized in a template are all the text and titles, any colors from text colors to background, borders etc., images and videos. Developing a dynamic template can take anything from one day to a few days, depending on the complexity of the template and your experience.

If you plan to develop a lot of templates we suggest to create a skeleton folder with all the files and the basic code inside, so that every time that you start a new template you can just paste the folder and change its name to have the basic structure already in place.

**Disclaimer**: The following guide assumes that you have your copy ACF Pro installed on your website or that you use InboundNow Pro that comes with ACF Pro included. Making powerful dynamic templates require the use of repeater fields and flexible fields which are available only with ACF Pro.

##Template Config
The must-have files in a dynamic template are `config.php` which holds all the ACF fields, `index.php` that holds all the html and dynamic styling and `thumbnail.png` that is, as the name say, the thumbnail that shows up in the admin page.
You must create all your custom templates under the directory `wp-content -> uploads`. For example, for landing pages the basic structure of the template will be the following:
```
wp-content
    |_uploads
        |_landing-pages
            |_my-template
                |_config.php
                |_index.php
                |_thumbnail.png
```

###config.php
The file config.php contains all the fields that you create with the ACF Pro plugin. For the sake of this explanation, suppose that you have already created a group of fields with ACF Pro that you want to use for your dynamic template. At this point in the WP Dashboard you must go to `Custom Fields -> Tools`, select the group of fields that you've saved and click on 'generate export code'. The resulting code must be pasted in the config.php file after the comment that says `/* Load ACF definitions */`. For the template to work, the last part of the array must be replaced with the following code for the case of landing pages

```
	'location' => array (
		array (
			array (
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'landing-page',
			),
			array (
				'param' => 'template_id',
				'operator' => '==',
				'value' => $key,
			)
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'top',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => 1,
	'description' => '',
	'options' => array(),
));

endif;
```
Please look at one of the existing templates included with the plugins to see how to configure the file config.php for the other plugins.

###index.php
In order to build a dynamic template you need to have a static html template already built and tested. It will be a lot easier adding dynamic blocks and styling to an already tested codebase. Most of the dynamic css must be inline or enclosed in a `<style>` tag in the head section. We suggest to use a separate `style.css` file only for the static styles that won't be changed because that file cannot contain any code.

An inline style looks like this:

```html
<div style="background-color: #FFFFFF">
```

In our dynamic template the color value will be replaced by a php variable, like this:

```html
<div style="background-color: <?php echo $hero_bg_color ?>">
```

After you have exported the fields in the file `config.php` you can get the basic code to use these fields in your dynamic template. You can generate the code by going to `Landing Pages -> Developer Tools` , and choosing the fields that you saved. Copy and paste the code into the `index.php` file and move the html code in the appropriate places. The code generator will save you hours as it generates the right code and loops to get and use the fields.

##Using ACF
Advance Custom Fields is a popular WordPress plugin that allows to add custom fields to posts, pages and custom posts. These fields can be used to add any kind of additional content besides the normal wysiwyg editor. This additional content can then be used to perform additional operations in the backend, or can be showed in the frontend to enrich the user experience. Go to the [description page](http://www.advancedcustomfields.com/resources/what-is-acf/) for a more detailed description of the plugin. The free version of the plugin offers a lot of fields to begin with but the full power of ACF can be exploited with the premium version that adds special fields such as the Repeater Field and the Flexible Field (go here for a description of all the advanced fields http://www.advancedcustomfields.com/pro/).

###Field Types
There is a number of field types available in ACF, for a description of all the fields you can visit this page http://www.advancedcustomfields.com/resources/ . Here we are going to describe the main fields used in our templates, and how they are used. Remember that nothing prevents you from using any other field available in your template, it's just a matter of necessity and, sometimes, of personal preference.

We strongly suggest to not make any of the fields mandatory because that would create problems to the Visual Editor.

####Text
This field is a simple text input, and is used to store single string values. In our templates the text field is used to store all the titles, subtitles, names (for the testimonial areas), any short string value, and the URLs for all the links. It may seem strange using this field for URLs when ther is already a URL field available. The reason for our choice is that every single field in our template has a default neutral value but the URL field has trouble accepting '#' as default value.

####Textarea
The textarea field creates a text area and can store paragraphs or longer text. We use the textarea field to store all the content that shows up below titles, testimonials text etc.

####Color Picker
This is the most used field in each one of our templates. The color picker creates a jquery color selection popup. Through these field we allow users to change the color of anything in the template, like background, borders, text color, button colors, and anything that can have a color. Remember that if you allow people to change a background you should also allow them to change the text color to create the right contrast and make the text readable. To give maximum flexibility normally we give two separate color options for titles and text so that people can create more contrast.

####Select
We use the select field in many templates to allow users to position media and text. For example, in areas where there is an image besides a text area, we will let the user choose whether the image should be to the left or right side of the area. If the user repeats this area many times in his/her template it's possible to create an attractive wave effect by alternating right and left alignments.

Another use of the select field is to allow the user to choose whether to use an image or a video in an area. Image and video are two different field types, and we use conditional logic to show the appropriate field depending on the choice of the user.

####Checkbox and true/false
These are two variations of the same field type. The true/false is a checkbox with one option only. We use it to show or hide certain parts of the template.

####Number
We use the number field typically when we allow the user to choose the size of the element to add.

####Fontawesome
Fonteawesome is a special field added through a free ACF extension available for download in the WordPress plugin repository (https://wordpress.org/plugins/advanced-custom-fields-font-awesome/). This field allows to add fontawesome icons in the template. They can be given any size and color, like any fonts. However, for this field to work you'll have to add the font files and css to your template. Our suggested solution for this is to create a folder `assets` under your template folder, and then create two subfolders under this named `css` and `fonts`. You will add the fonts and the css under the appropriate folders.

####Image
The image field, as you may guess, is used to load images from the media library. Usually in a template there is an ideal size of the image that fits perfectly in the layout and creates an effect of harmony with the rest. It's important to inform the user of the ideal size of the image in the field description but you should never assume that the user will load an image of that exact size. A user could load a much bigger hi-res image, or a smaller image. You must consider these situations, and add the right css rules. The solution that we have found works best is to add the css rules `max-width` and `max-height`. Supposing to have an image ideal size of 500px x 300px a good solution could be this:

```php
echo '<img style="max-width:100%; max-height:300px;" alt="" src="'. $image .'">'
```

With these rules, small images won't be enlarged, which would worse their quality, while bigger images will be resized without losing their proportions.

####Video (oEmbed field)
A video is added through the oEmbed field. Generally speaking the oEmbed field can be used for adding a number of multimedia content including images, tweets, audio etc. Due to the large number of oEmbed providers we cannot prevent the user from adding something different than a video, so it's important to let the users know what kind of content you expect them to add in the description field. Since the video will be in an iframe, it's hard to control its size. To overcome this problem in our templates we allow the user to add width and height of the video, and we have developed a jQuery function to add these values to the iframe to force them

```javascript
jQuery(document).ready(function($) {
    $('.header').find('iframe').attr('height', '<?php echo $video_height; ?>px');
    $('.header').find('iframe').attr('width', '<?php echo $video_width; ?>px');
    $('.header').find('iframe').addClass('vertical-center');
});
```

> There are many other fields available, feel free to explore them in the [ACF documentation page](http://www.advancedcustomfields.com/resources/) to see how you can use them in your templates.

###Using Repeater Fields
The repeater field allows you to create a set of sub fields which can be repeated again and again whilst editing content!
Any type of field can be added as a sub field which allows you to create and manage very customized data with ease!

A typical situation where we use the repeater field is when creating a testimonials section. A testimonials section can have any number of testimonials. A repeater field will typically include a picture of the testimonial, the name and the text, and often also color pickers for name and text.

Another use case for a repeater field could be a slideshow with any number of pictures, each with an overlaying text.

###Using Flexible Fields
The flexible content field acts as a blank canvas to which you can add an unlimited number of layouts with full control over the order. Each layout can contain 1 or more sub fields allowing you to create simple or complex flexible content layouts.
The difference between a flexible field and a repeater field is subtle but important. A repeater field is the same field group repeated again and again a number of times. A flexible field is a number of field groups that can be combined in any order, any number of times. Thanks to the power of the flexible field the various sections of a template can be combined in any order and number, allowing the user to create a short landing page or a very long sales page with the same template.
Typically, the sections that you don't want to be part of a flexible field are the header (if there is one) and the footer because they have a fixed position.

###Vertical Align Of Images And Videos
When there's an area with an image and a text next to each other, sometimes it's a good idea to have them vertically centered in order to create a better sense of harmony. Vertically centering elements is challenging also for experienced CSS developers. The following lines of code reach the scope if the browser support CSS 3. The code must be added inside the `<style>` tags at the top of the page, or inside the separate CSS file if you choose to use one. 

```css
.vertical-center {
    position: relative;
    top: 50%;
    -webkit-transform: translateY(-50%);
    -ms-transform: translateY(-50%);
    transform: translateY(-50%);
}
```
Vertical aligning elements makes sense only if there are two elements next to each other to align. In small screens where elements are stacked the vertical align can produce weird effects, so it's better to include this code in a media query that triggers at the point where the elements are stacked on each other, something similar to this:

```css
@media (min-width: 992px) {
    .vertical-center {
        position: relative;
        top: 50%;
        -webkit-transform: translateY(-50%);
        -ms-transform: translateY(-50%);
        transform: translateY(-50%);
    }
}
```

The jQuery code suggested for the video also adds the `vertical-center` class the the iframe, therefore causing the vertical alignment. The class `vertical-center` should also be given to the image to make sure that no matter the size it will always be aligned to the text besides it.

###Default Values for Fields
It's important to give a default value to each and every field in your template. The default value is the initial value that the field has if the user doesn't change it. The reason for this is that thanks to the default values a user will be able to quickly test different layouts of the template and see how it looks like without having each time to fill up all the blanks. This is a huge time saver.

Here at InboundNow we start from a finished static html file that could be taken 'as is' and used as a landing page. Then we use the colors as default colors for text, backgrounds and buttons.

For titles and text areas we use a [lorem ipsum generator](http://lipsum.com/). For testimonials you can make up names or use character names from books or movies that you love (can you guess what we've used in our templates?)

Images are more complicated because unfortunately the image field does not allow a default value. To overcome this problem we suggest to add an `img` folder under the `assets` folder that you already created for the Fontawesome field. Here, add all the images that you will use as defaults in the various sections of the template. A great source of free to use high quality images for us is the website [unsplash.com](https://unsplash.com/). If you use other sources, check the license before using the image. Once you have all your default images saved, this code will allow you to use the default image in case the use hasn't added one

```php
if ( ! $my_image ) {
    echo '<img width="300" alt="" src="' . $urlpath . 'assets/img/default-image.jpg">';
    } else {
    echo '<img style="max-width: 100%; max-height:300px;" alt="" src="'. $my_image .'">';
}									}
```
The variable `$urlpath` is the url path to the template folder, and it's calculated at the top of the file index.php with this line of code (check out one of our templates to better understand)
```php
$key = basename(dirname(__FILE__));
$urlpath = LANDINGPAGES_UPLOADS_URLPATH . "$key/";
```
###Adding Scripts and Styles to the Template

In order to add scripts and styles to the template, we suggest to use the WordPress way of enqueuing them. This must be done at the top of the file `index.php`. The following is an example function to use in a template that uses the Bootstrap framework.

```php
function my_template_enqueue_scripts() {
	$key = basename(dirname(__FILE__));
	$urlpath = LANDINGPAGES_UPLOADS_URLPATH . "$key/";

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'my_template-bootstrap-js', $urlpath . 'assets/js/bootstrap.min.js', '','', true );

	wp_enqueue_style( 'my_template-bootstrap-css', $urlpath . 'assets/css/bootstrap.css' );
	wp_enqueue_style( 'my_template-css', $urlpath . 'assets/css/style.css' );
	wp_enqueue_style( 'my_template-fontawesome', $urlpath . 'assets/css/font-awesome.min.css' );
}

add_action('wp_head', 'my_template_enqueue_scripts');
```
Those that are WordPress developers will probably be surprised to see that we use the `wp-head` hook instead of `enqueue-scripts`. Unfortunately, with the `enqueue-scripts` hook we've found that js files are not enqueued while css files are. The reason for this strange behaviour is still unknown but we have found that the `wp-head` hook works correctly for all kind of scripts.

## Licensing & Terms of Use

###  Licensing
Inbound Pro Plugin contains many different assets holding different licenses. We'll do our best to illustrate these relationships below. Please use the key below understand license meanings:

#### Key Map

Mixed = (Directory contains mixed licenses)
APACHE2 = Apache License 2.0 [more info](https://www.apache.org/licenses/LICENSE-2.0)
IBN = Inbound Now Licensing Policy
GPL = GNU General Public License [more info](https://www.gnu.org/licenses/gpl-3.0.en.html)
GPL+ = GNU General Public License AND (MIT OR BSD)
BSD = Berkeley Software Distribution [more info](http://www.linfo.org/bsdlicense.html)
BSD23 = Berkeley Software Distribution with 2-Clause and 3-Clause[more info](https://github.com/d3/d3/blob/master/LICENSE)
MIT = Massachusetts Institute of Technology [more info](http://opensource.org/licenses/MIT)
+os = Contains included GPL, BSD, and MIT assets


#### IBN - Inbound Now Licensing Policy

Structural propperty cannot be used or modified by a 3rd party without permission or issued license from InboundWP LLC. 

#### License Application

Path | License
 --- | ---
 /inbound-pro/| IBN +os
 /inbound-pro/assets/ | IBN +os
 /inbound-pro/assets/css | IBN
 /inbound-pro/assets/images | IBN
 /inbound-pro/assets/js | IBN
 /inbound-pro/assets/libraries | +os
 /inbound-pro/assets/libraries/echarts | BSD
 /inbound-pro/assets/libraries/Ink | MIT
 /inbound-pro/assets/libraries/MiniColors | MIT
 /inbound-pro/assets/libraries/Shuffle | MIT
 /inbound-pro/classes/ | IBN 
 /inbound-pro/core/ | IBN +os 
 /inbound-pro/core/cta | GPL+
 /inbound-pro/core/landing-pages | GPL+
 /inbound-pro/core/leads | GPL+
 /inbound-pro/core/inbound-mailer | IBN +os
 /inbound-pro/core/inbound-mailer/assets/libraries/d3 | BSD23
 /inbound-pro/core/inbound-mailer/assets/libraries/jpicker | MIT
 /inbound-pro/core/inbound-mailer/assets/libraries/jquery-datepicker | MIT
 /inbound-pro/core/inbound-mailer/assets/libraries/jquery-tablesorter | MIT
 /inbound-pro/core/inbound-mailer/assets/libraries/ladda | MIT
 /inbound-pro/core/inbound-mailer/assets/libraries/popModal | MIT
 /inbound-pro/core/inbound-mailer/assets/libraries/snap | APACHE2
 /inbound-pro/core/inbound-mailer/assets/libraries/SweetAlert | MIT
 /inbound-pro/core/inbound-mailer/classes | IBN
 /inbound-pro/core/inbound-mailer/modules | IBN
 /inbound-pro/core/inbound-automation | IBN +0s
 /inbound-pro/core/inbound-automation/assets/libraries/isoloading | MIT
 /inbound-pro/core/inbound-automation/assets/libraries/jrumble | MIT
 /inbound-pro/core/inbound-automation/assets/libraries/ladda | MIT
 /inbound-pro/core/inbound-automation/assets/libraries/SweetAlert | MIT
 /inbound-pro/core/inbound-automation/classes/ | IBN
 /inbound-pro/core/inbound-automation/definitions/ | IBN
 /inbound-pro/core/shared | GPL+


#Legacy Readme

The files have been streamlined and a build process is in place.

The build process syncs live plugins from your current /plugins dir, and adds the latest files into core of pro. During this process the /shared folder is removed from the core plugins and moved into /core/shared. This dramatically shrank the size of pro

`npm install` #deps

## Dev Instructions

Run the below command to sync local copies of all /core plugins
`sudo gulp sync`

Run the below command for a production build
`sudo gulp build`
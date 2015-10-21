Build process for PRO

The files have been streamlined and a build process is in place.

The build process syncs live plugins from your current /plugins dir, and adds the latest files into core of pro. During this process the /shared folder is removed from the core plugins and moved into /core/shared. This dramatically shrank the size of pro

`npm install` #deps

# Dev Instructions

Run the below command to sync local copies of all /core plugins
`sudo gulp sync`

Run the below command for a production build
`sudo gulp build`
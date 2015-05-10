Build process for PRO

The files have been streamlined and a build process is in place.

The build process syncs live plugins from your current /plugins dir, and adds the latest files into core of pro. During this process the /shared folder is removed from the core plugins and moved into /core/shared. This dramatically shrank the size of pro

`npm install` #deps

Sync live plugin repos with pro and move shared to single /core folder

`sudo gulp sync`
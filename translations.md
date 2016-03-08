# Synopsis

This document seeks to create an optimal user-sourced translation system for all Inbound Now Assets.

The inbound now product line contains many assets, some are loaded from the WordPress plugins directory, others are loaded from the WordPress uploads folder. 

We have 3 plugins that are hosted in public github repos and are already attached to Transifex, though we think this system is too complex. 

The three plugins we host publicly are:

* Landing Pages
* Calls to Action
* Leads.

Other assets are hosted in private github and bitbucket repos. 

## Transifex

Transifex currently powers our translations for our three public repositories. 

## Transifex and WordPress

We use text domains in each of our plugins to help WordPress read the .mo files transifex provides for each plugin separately. 

## Our desire. 

What we would like to do is use a script to locally scan all of our tools in our dev environment for translatable strings and have one master .po file for all our tools. We would like all our plugins and non-plugin extensions to have the same text-domain name. We have too many assets to have each asset have it's own text domain and transifex integration. If we could combine all our strings then we could filter out repeat strings and have one central location for all our language strings that is sourced by all of our assets. 

We want to serve our master .mo files inside our:

* Inbound Pro Component (contains Landing Pages, Leads, and Calls to Action
* Individually inside each stand alone component, so their strings are available as they are used as stand alone plugins. 

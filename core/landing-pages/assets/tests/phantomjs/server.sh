#!/bin/sh
export DISPLAY=:0
phantomjs --ignore-ssl-errors=true server.js  2>&1
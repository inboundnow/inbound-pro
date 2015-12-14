#!/bin/bash
#rsync -a ../cta/* ./core/cta
cp -rf ../cta ./core
cp -rf ./core/cta/shared ./core
rm -rf ./core/cta/shared
echo "cta synced"
cp -rf ../leads ./core && rm -rf ./core/leads/shared
echo "leads synced"
cp -rf ../landing-pages ./core && rm -rf ./core/landing-pages/shared
echo "landing pages synced"
cp -rf ../inbound-mailer ./core
echo "mailer synced"
cp -rf ../inbound-automation ./core
echo "automation synced"
echo "sync done"
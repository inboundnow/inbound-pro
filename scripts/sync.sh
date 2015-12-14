cp -rf ../cta core/cta && rm -rf core/cta/shared
cp -rf core/cta/shared core/shared
rm -rf core/cta/shared
cp -rf ../landing-pages core/landing-pages && rm -rf core/landing-pages/shared
cp -rf ../leads core/leads && rm -rf core/leads/shared
cp -rf ../inbound-mailer core/inbound-mailer
cp -rf ../inbound-automation core/inbound-automation
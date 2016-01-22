#! /bin/bash
# A modification of Dean Clatworthy's deploy script as found here: https://github.com/deanc/wordpress-plugin-git-svn
# The difference is that this script lives in the plugin's git repo & doesn't require an existing SVN repo.
 
#Instructions
# Update 'NEWVERSION1' with correct version number
#1. Open up command line utility
#2. cd into the directory that this script is located in
#3. deploy this script 
# ./deploy.sh
#3. Enter github password twice and magic happens
 
# main config
CURRENTDIR=`pwd`
MAINFILE="inbound-pro.php" # this should be the name of your main php file in the wordpress plugin
 
# git config
GITPATH="$CURRENTDIR" # this file should be in the base of your git repository


# Tell git who we are
git remote add deployorigin https://github.com/inboundnow/inbound-pro.git

# tell it to cache passwords:
git config --global credential.helper wincred


# Merge Develop Into Master
git checkout master
echo "Checkout master"
git pull deployorigin master
echo "pull master"
git merge develop
echo "merge develop"
git push deployorigin master
sleep 5


CURRENTVERSION=`grep "^Version:" $GITPATH/$MAINFILE | awk -F' ' '{print $NF}'`
echo "$MAINFILE version: $CURRENTVERSION"
 

if git show-ref --tags --quiet --verify -- "refs/tags/$CURRENTVERSION"
    then
        echo "Version $CURRENTVERSION already exists as git tag.";

    else
        echo "Git Tag for this version does not exist. Let's proceed..."
		
		echo "Tagging new version in git"
		git tag -a "$CURRENTVERSION" -m "Tagging version $CURRENTVERSION"
		sleep 5
fi
 


# Push to Origin
if git remote | grep deployorigin > /dev/null; then
	echo "Pushing latest commit to deployorigin 'deployorigin', with tags"
	git push deployorigin master --tags
fi

# Switch back to develop
git checkout develop


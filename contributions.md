# Contribute To Inbound WP

When contributing please ensure you follow the guidelines below so that we can keep on top of things.

__Please Note:__ GitHub is for bug reports and contributions only - if you have a support question or a request for a customization don't post here, go to our [Support page](https://support.inboundnow.com) instead.

## Getting Started

  * __Do not report potential security vulnerabilities here. Email them privately to our security team at [hudson@inboundnow.com](mailto:hudson@inboundnow.com)__
  * Before submitting a ticket, please be sure to replicate the behavior with no other plugins active and on a base theme like Twenty Seventeen.
  * Submit a ticket for your issue, assuming one does not already exist.
  * Raise it on our [Issue Tracker](https://github.com/inboundnow/inbound-pro/issues)
  * Clearly describe the issue including steps to reproduce the bug and screenshots of the bug itself.
  * Make sure you fill in the earliest version that you know has the issue as well as the version of WordPress you're using.

## Making Changes

* Fork the 'develop' branch from the inbound-pro repository on GitHub
* Make the changes to your forked repository
* Ensure you stick to the [WordPress Coding Standards](https://codex.wordpress.org/WordPress_Coding_Standards)
* When __('') language strings are added use the 'inbound-pro' text domain when setting up [i18n](https://codex.wordpress.org/I18n_for_WordPress_Developers). See example: `_e('Custom Setting' , 'inbound-pro');`
* When committing, reference your issue (if present) and include a note about the fix
* Push the changes to your fork and submit a pull request to the 'develop' branch of the inbound-pro repository

## Code Documentation

* Do not use doubleslashes(//) to comment code. Use [doc blocks](https://phpdoc.org/docs/latest/getting-started/your-first-set-of-documentation.html) and inline doc blocks (/* this is an inline doc block */).
* Please make sure that every function is documented so that when we update our API Documentation things don't go awry!
* Finally, please use spaces and not tabs. 1Tab = 4 spaces.

At this point you're waiting on us to merge your pull request. We'll review all pull requests, and make suggestions and changes if necessary.


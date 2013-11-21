#WP Salt n Pepper
Contributors: tnash

Tags: Password, Hashing, Security

Requires at least: 3.6

Tested up to: 3.7.1

License URI: http://www.gnu.org/licenses/gpl-2.0.html

##Description
Adds a user specific hash to the WordPress users passwords, this unique hash is stored in usermeta. It manages versioning of passwords, and seamlessly migrates users next time they change their password. In effect introducing Salt and Pepper support for WordPress.

##Installation

1. Upload 'wp-salt-n-pepper.php' to the '/wp-content/plugins/' directory,
2. Activate the plugin through the 'Plugins' menu in WordPress.

Done, note users are not automatically updated to using the new hash, instead this is done on user change password.

##Frequently asked questions

###Is this any more secure?
Define secure? It has a modicum of benefits, in that a brute force attack which cracks one password, can't compare hashes against other users passwords. As the user specific salt is held in the same DB as the password all be in the usermeta you can argue only limited additional protection.

###Do I still need to define salts in wp-config?
YES! This is an additional layer to the salting process you still need to set the unique hash in wp-config and keep it secure.

###Is this adding Salt or Pepper?
Technically it's adding Salt, the default global hashing, WordPress sone is the pepper.

###Is there a reason not to use this?
Yes it could screw up your entire user database. Of course it probably won't but it's benefits are limited.

###If the user password hash is not immediately updated how does it know which hashing method to use?
When the users has a unique salt generated the version used is also stored with the user. IF the user doesn't have a password version then default is assumed.

###Why did you write it then?
For a specific site who's security requirements required both salt and pepper by their auditors.
=== WP-Yadis ===
Contributors: wnorris
Tags: openid, yadis, xrds, delegation
Requires at least: 2.2
Tested up to: 2.3.1
Stable tag: 1.2.1

Use your wordpress blog URL as an OpenID by delegating to a third party provider.

== Description ==

**The functionality of this plugin as been included in the [WordPress OpenID
plugin][].  This plugin is no longer supported and will not be updated in the
future.**

[WordPress OpenID plugin]: http://wordpress.org/extend/plugins/openid/

OpenID is an [open standard][] that lets you sign in to other sites on the Web
using little more than your blog URL. This means less usernames and passwords
to remember and less time spent signing up for new sites.  WordPress.com
recently [added support][] for OpenID, and this plugin allows you to mimick
that functionality on your own installation of WordPress by delegating to a
third party OpenID Provider.

More information about OpenID delegation can be found at Sam Ruby's excellent
[blog entry][].  Of course this can all be done manually, but using a plugin
provides the benefit of not messing with the code and more importantly
persistence through theme changes.

[open standard]: http://openid.net/
[added support]: http://wordpress.com/blog/2007/03/06/openid/
[blog entry]: http://www.intertwingly.net/blog/2007/01/03/OpenID-for-non-SuperUsers

== Installation ==

This plugin follows the standard WordPress installation method:

1. Upload `yadis.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Start adding delegates through the 'Yadis' section of the 'Options' menu

== Frequently Asked Questions ==

= Does this plugin provide a full OpenID provider? =

No, this plugin delegates to a third party provider.  You can run your own
provider using [phpMyID][] or something similar, or you can use a full-service
OpenID provider such as [MyOpenID][] or [one of these][].

[phpMyID]: http://siege.org/projects/phpMyID/
[MyOpenID]: http://www.myopenid.com/
[one of these]: http://openid.net/wiki/index.php/OpenIDServers

= How do I get help if I have a problem? =

Please direct support questions to the "Plugins and Hacks" section of the
[WordPress.org Support Forum][].  Just make sure and include the tag 'yadis',
so that I'll see your post.

[WordPress.org Support Forum]: http://wordpress.org/support/

== Screenshots ==

1. Add a new provider by selecting from one of the pre-defined list ...
2. ... or add a new provider by specifying the OpenID Server and delegate manually.
3. Sort multiple providers by priority by dragging them.


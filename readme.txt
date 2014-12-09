=== MushRaider Bridge ===
Contributors: Mush
Donate link: http://mushraider.com/
Tags: mushraider, raid planner, raid, mmorpg, bridge, planner, calendrier, calendar, tool
Requires at least: 3.0
Tested up to: 4.0
Stable tag: 1.0.3
License: Creative Commons (CC BY-NC-SA 4.0)

MushRaider Bridge is a plugin to integrate MushRaider raid planner into wordpress

== Description ==

MushRaider Bridge allows you to integrate MushRaider into wordpress. MushRaider is a powerful raid planner mainly designed for MMORPG players and guilds.

= Features list =

* Connect to MushRaider using wordpress login
* Configurable roles mapping
* Widget displaying incoming events
* Shortcode to display your roster (using [mushraider_roster game="{optional game id from MushRaider}"])

= Widget =

In the Appearance -> Widgets you'll find the MushRaider bridge widget. After adding it to your sidebar you can enter a title for the Widget, select a game (optional) and a period for the incoming events to display.

= Shortcode =

Display your roster in your pages or posts with this shortcode

`[mushraider_roster]`

Which is the simplest option, and uses all default and optional settings. If you want to display the roster for a specific game you can add the option "game" with the game_id. Example:

`[mushraider_roster  game="1"]`

= Related Links =

* [Official website](http://mushraider.com/
  "Learn more about MushRaider raid planner")
* [Support Forum](http://forum.raidhead.com/
  "Use this for support and feature requests")
* [GitHub MushRaider](https://github.com/st3ph/mushraider
  "Get access to the source code")

== Installation ==

1. Upload directory `mushraider-bridge` to the `/wp-content/plugins/` directory
2. In MushRaider's admin panel head to Settings => API
3. Check 'Enable API calls', generate the private key and save.
4. In Wordpress's admin panel activate the plugin through the 'Plugins' menu
5. In MushRaider plugin settings you can now add your API key, MushRaider's url and map the user's groups
6. Add the "Login url" provided in the plugin's settings to your MushRaider's admin panel (Settings => API section) and save.
7. Save. You can login in MushRaider with your Wordpress's login

== Frequently Asked Questions ==

= MushRaider ? =

MushRaider is a powerful raid planner (free) mainly designed for MMORPG players and guilds. To learn more head to http://mushraider.com

= How can I map the custom roles I create in MushRaider ? =

You have to set the private key AND the API url in the settings to let the plugin get the roles list in your MushRaider install

= I can't connect in MushRaider anymore =

If you encounter difficulties to connect in MushRaider after enabling the bridge system, you can delete in the MySQL database the row named "bridge" in the "settings" table

== Screenshots ==

1. bridge settings
2. roles mapping
3. MushRaider's side settings

== Changelog ==

= 1.0.3 =
* Fix game's logo display (again)

= 1.0.2 =
* Fix game's logo display
* Add custom CSS form in the plugin's settings

= 1.0.1 =
* Update readme

= 1.0.0 =
* First version

== Upgrade Notice ==

= 1.0.0 =
Enjoy

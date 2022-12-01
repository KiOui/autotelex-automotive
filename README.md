# Autotelex Automotive
A plugin for synchronizing [Autotelex](https://autotelex.nl) data to a WordPress website running the 
[Automotive](https://themeforest.net/item/automotive-car-dealership-business-wordpress-theme/9210971) WordPress theme 
via the incremental update option.

## Installation

1. Make a zip of the `autotelex-automotive` folder in this directory.
2. On your WordPress installation, go to the administration dashboard.
3. Go to `Plugins` -> `Add new`.
4. Click on `Upload plugin`.
5. Upload the zip file you just created and click on `Install now`.
6. Activate the new `Autotelex Automotive` plugin.

### Setting web credentials
After activating the plugin there will be an `Autotelex Automotive` settings section. You can go here and set the
credentials for enabling the API endpoint. The API endpoint uses normal HTTP authentication.

## Inner workings
This plugin adds an API endpoint at `/wp-json/autotelex-automotive/v1/manage` which accepts (parts of) the Autotelex
API data. Not all fields are accepted yet but this is easily extendable. If you need a different field you can add it
via the normal `register_rest_route` function call.

Autotelex sends request parameters in XML and WordPress does not support XML natively on their REST API. The
`aa_convert_xml_request_to_json_request` function converts the XML within the requests that Autotelex sends to JSON.
The API endpoint also still works with normal JSON REST parameters. It only converts to JSON when necessary.
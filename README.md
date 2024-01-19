# Sportlink - KNVB

This Wordpress plugin is used to connect to the Sportlink API. It is used to show the fixtures, results and standings of a club, or show details of a match.

## Description

This Wordpress plugin is used to connect to the Sportlink API.

- [A complete reference of the Sportlink API can be found here](https://dexels.github.io/navajofeeds-json-parser/).
- [Support for the Sportlink API can be found here](https://sportlinkservices.freshdesk.com/nl/support/solutions/9000107516).

## Getting Started

### Installation

This Wordpress plugin is currently not available in the Wordpress plugin repository. To install it, download the zip file and upload it to your Wordpress installation.

### Configuration

After installing the plugin, you need to configure it. Go to the settings page of the plugin (under Settings -> Sportlink - KNVB) and fill in the following fields:

- API sleutel: The API key you received from Sportlink
- Cache-tijd (in minuten): The time in minutes the data is cached. This is to prevent too many requests to the Sportlink API. The default is 30 minutes.
- SSL-beveiliging overschrijven: In local environments, you might not have a valid SSL certificate. If you want to use the plugin in a local environment and run into problems with a certificate, you can enable this option. This will disable the SSL check. Note: this is not recommended for production environments.

### Shortcodes

The plugin uses shortcodes to display the data. The following shortcodes are available:

- `[sportlink type="programma"]`: Shows the fixtures of the club
- `[sportlink type="uitslagen"]`: Shows the results of the club
- `[sportlink type="stand" poule="1234"]`: Shows the standings of the club
- `[sportlink type="wedstrijd"]`: Shows the details of a match. The Match ID is grabbed from the URL: `$_GET['wedstrijd']`

## Templates

The plugin uses templates to display the data. The templates are located in the `templates` folder of the plugin. You can override the templates by copying them to your theme folder.

### Default templates

| Shortcode                               | Template                  | Available data                                                                                                                                                                                                                                                                                                                                                                                     |
| --------------------------------------- | ------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `[sportlink type="programma"]`          | `templates/fixtures.php`  | [`$data->fixtures`](https://dexels.github.io/navajofeeds-json-parser/article/?programma)                                                                                                                                                                                                                                                                                                           |
| `[sportlink type="uitslagen"]`          | `templates/results.php`   | [`$data->results`](https://dexels.github.io/navajofeeds-json-parser/article/?uitslagen)                                                                                                                                                                                                                                                                                                            |
| `[sportlink type="stand" poule="1234"]` | `templates/standings.php` | [`$data->standings`](https://dexels.github.io/navajofeeds-json-parser/article/?poulestand)                                                                                                                                                                                                                                                                                                         |
| `[sportlink type="wedstrijd"]`          | `templates/match.php`     | `$data->match`: [ [`wedstrijd-informatie`](https://dexels.github.io/navajofeeds-json-parser/article/?wedstrijd-informatie), [`history`](https://dexels.github.io/navajofeeds-json-parser/article/?wedstrijd-historische-resultaten), [`poule`](https://dexels.github.io/navajofeeds-json-parser/article/?poulestand), [`teams`](https://dexels.github.io/navajofeeds-json-parser/article/?teams) ] |

## Custom templates

The default templates are very basic and should be used as examples. You can override them by copying the default templates from the `templates` folder of the plugin to your (child) theme in a folder called `sportlink-knvb`.

### Templates in shortcodes

In addition to overriding the default templates, you can also modify the template from the shortcode. This is done by adding the `template` attribute to the shortcode. For example:

```
[sportlink type="programma" template="small"]
```

This will use the `templates/fixtures-small.php` template.

### PHP

The templates are written in PHP. This means you can use PHP functions and variables in the templates. The `$data` variable contains the data from the API. You can use this data to display the information you want.

An example of a template with a bit more logic can be found in the `templates/match.php` template.

### Available data

The data from the API is available in the `$data` variable. The available data can be found in the 'Available data' column in the table above

#### Team logo

There is no team logo available in the API. You can use the following code to get the team logo from the voetbal.nl logo API (replace `{clubcode}` with the correct club code):

```
<img src="https://logoapi.voetbal.nl/logo.php?clubcode={clubcode}">
```

## Shortcode attributes

The following attributes are available for the shortcodes:

| Attribute          | Description                                                                       | Default                     |
| ------------------ | --------------------------------------------------------------------------------- | --------------------------- |
| type               | The type of data to show. Can be `programma`, `uitslagen`, `stand` or `wedstrijd` | `programma`                 |
| template           | The template to use. See the 'Templates' section for more information             |                             |
| team               | The teamcode of the team to show. Only used for the `programma` and `uitslagen`   |                             |
| aantaldagen        | The number of days to show. Only used for the `programma` and `uitslagen`         | Fixtures: `13`, Team: `365` |
| aantalwekenvooruit | The week offset. Only used for the `programma` and `uitslagen`                    | `0`                         |
| poule              | The poule ID. Only used for the `stand`                                           |                             |

## Help

This plugin is not actively maintained and is provided as-is.

Support for this plugin is very limited. If you have any questions, please file an issue on [Github](https://github.com/RichardVanDerMeer/wordpress-sportlink.club.dataservices/issues).

## Authors

[Richard van der Meer](https://richardvandermeer.nl)

## Version History

- 2024-01-19
  - Added README.md
  - Add Docker configuration for local development

- 2017-03-03
  - Initial release

- [Gamajo-Template-Loader](https://github.com/GaryJones/Gamajo-Template-Loader)

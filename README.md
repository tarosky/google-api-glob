# Google API Glob

This WordPress plugin enables fetch Google Analytics data to single CSV.

We make this plugin to get **GROUP BY**ed result from Google Analytics.

## Requirements.

- PHP 5.4 and over.
- Composer.
- WordPress with [WP-CLI](http://wp-cli.org).
- Google Analytics account.

## How to Use

First of all, download or fork this repo and run composer.

```
cd your/wordpress/wp-content/plugins
git clone git@github.com:tarosky/google-api-glob.git
cd google-api-glob
composer install
```

Then, install and activate plugin. 

If you just need analytics data, consider building virtual WordPress environment with [VCCW](http://vccw.cc) because this plugin needs callback endpoint.

**NOTICE:** VCCW's defaul host name is wordpress.dev, but you should [change it](http://vccw.cc/#h2-4) to `local.example.jp` and so on. It's because Google API's restriction.

### Connect with Google Analytics account

After activation, you have to connect with your Google API. See detail at [GAPIWP library's page](https://github.com/hametuha/gapiwp).

### Define JSON directory

Define `GAG_SRC_DIR` constant which should be writable directory's path.  
Best practice is define it on `wp-config.php`.

```php
// wp-config.php
define( 'GAG_SRC_DIR', 'whereever/you/like');
```

### Install JSON

Write JSON and save it to JSON directory above. You can find valid example in sample directory of this repo.

```JS
{
  "description": "Get recent page views.", // Not require, but needed.
  "from": "2015-10-01", // Required and DATE format(YYYY-MM-DD)
  "to": "2015-10-31", // Required and DATE format(YYYY-MM-DD)
  "metrics": "ga:pageviews", // Requied.
  "params": {
    "max-results": 200, // Not requied. Default 2000
    "dimensions": "ga:pagePath", // Required.
    "filters": "ga:dimension1==post", // Optional
    "sort": "-ga:pageviews" // Optional
  }
}
```

Here's more detaild description about each params.

- `metrics` : Comma separated list of metrics(e.g. ga:sessions,ga:pageviews).
  You can find possible values at [Dimensions & Metrics Explorer](https://developers.google.com/analytics/devguides/reporting/core/dimsmets).
- `params.dimensions` : Comma separated list of dimensions(e.g. ga:city,ga:browser).
- `sort` : Comma separated list of metrics or dimensions. The value prefixed with `-` means descending.
- `filters` : Comma or semi-colon separated list of filter. Commma means *OR*, semi-colon means *AND*. Each filter consists of metrics or dimensions, operand and value. See examples at [Core Reporting API Reference guide](https://developers.google.com/analytics/devguides/reporting/core/v3/reference#filters).


## Command API

Use `wp help glob-ga` to see manual.

The core command is `dump`.

```
wp glob-ga dump path/to/result.csv --header
```

## Contribution

Feel free to send pull request.

1. Fork this repo.
2. Make branch wich meaningful name.
3. Commit change and push to github.
4. Make pull request.

## License

Licensed under [The MIT License](https://opensource.org/licenses/MIT). See LICENSE file.
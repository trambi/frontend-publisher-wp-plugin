This is frontend-publisher-wp-plugin WordPress plugin. This plugin aims to allow to members to post blog post from a short code in frontend of the website instead of accessing admin pages. This plugin aims to help website administration in the association Tisseurs de Chimères 🕸️.


## How to install this plugin

To install, you must :

1. Go the [Releases](https://github.com/trambi/frontend-publisher-wp-plugin/releases) page,
1. Download the file `tisseurs-frontend-publisher-<version>.zip` where `<version>` is the version of interest,
1. Go the administration page of your WordPress site,
1. Go in the menu `Plugins > Add Plugin`,
1. Click on the button `Upload Plugin`,
1. Select the file downloaded in step 2,
1. Cliquer sur le bouton `Install Now`.

*Et voilà*, the plugin is installed.



## How to use this plugin

This plugin create a shortcode `frontend_publisher`. This shortcode create a form to post blogpost for user without passing by the admin pages. 
The shortcode has attributes:

- `title` the name of the section of the form;
- `description` the text below to describe the form;
- `allowed_roles` the list of comma separated roles allowed to see the form and post the blogpost - by default editor, author and contributor';
- `post_status` the status of the blogpost after posting: draft, pending or published - by default draft;
- `show_categories` the fact to display categories in form - by default to yes;
- `show_tags` the fact to display tags in form - by default to yes;
- `show_featured_image` the fact to use feature image in form - by default to yes.
- `show_featured_image` the fact to use feature image in form - by default to yes.


## How to modify this extension

### File structure

This repository is organized as follow :

 - `src` contains source of the plugin:
   - `tisseurs-event-scheduler.php` is the main file of the plugin,
 - `build.bash` is a build bash script to create an zip archive of the source files.
 - `composer.json` contains the configuration of `composer`;
 - `composer.lock` contains the versions of installed composer packages.

### To work on the project

To work on the project, you will need [PHP](https://www.php.net) and [composer](https://getcomposer.org/).

### Install the dependancies 🚚

You will need to run the command `composer install`.

```bash
composer install
```

After that you have to configure PHP autoload with the command `composer -d . dump-autoload`.

```bash
composer -d . dump-autoload
```

### Build the plugin 🛠️

In order to build the plugin as a zip archive installable in WordPress, you have to run the command `composer build`.

```bash
composer build
```

### Unit test the plugin 🧪

**Not yet**
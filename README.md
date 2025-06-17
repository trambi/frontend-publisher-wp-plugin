This is frontend-publisher-wp-plugin WordPress plugin. This plugin aims to allow to members to post blog post from a short code in frontend of the website instead of accessing admin pages. This plugin aims to help website administration in the association Tisseurs de Chimères 🕸️.


## File structure

This repository is organized as follow :

 - `src` contains source of the plugin:
   - `tisseurs-event-scheduler.php` is the main file of the plugin,
 - `build.bash` is a build bash script to create an zip archive of the source files.
 - `composer.json` contains the configuration of `composer`;
 - `composer.lock` contains the versions of installed composer packages.

## To work on the project

To work on the project, you will need [composer](https://getcomposer.org/)

### Install the package 🚚

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
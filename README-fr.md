Ceci est le dépôt de l'extension WordPress frontend-publisher-wp-plugin. Cette extension vise à permettre aux membres des Tisseurs de Chimères de poster un billet de blog à partir d'un shortcode dans le site plutôt que de passer par la page d'administration. Cette extension vise à faciliter l'administration du site de l'association Tisseurs de Chimères 🕸️.

## Comment installer cette extension

Pour installer cette extension, vous devez :

1. Aller dans la page [Releases](https://github.com/trambi/frontend-publisher-wp-plugin/releases),
1. Télécharger le fichier `tisseurs-frontend-publisher-<version>.zip` où `<version>` est la version qui vous intéresse,
1. Aller dans la page d'administration de votre site WordPress,
1. Aller dans le menu `Extensions > Ajouter une extension`,
1. Cliquer sur le bouton `Téléverser une extension`,
1. Sélectionner le fichier téléchargé à l'étape 2,
1. Cliquer sur le bouton `Installer maintenant`.

Et voilà, c'est installé.

## Comment utiliser cette extension

Cette extension crée un shortcode `frontend_publisher`. Ce shortcode permet d'afficher un formulaire to poster un billet de blog sans passer par la page d'administration. 
Le shortcode a les paramètres suivants:

- `title` le nom de la section du formulaire ;
- `description` le texte sur le nom de section du formulaire qui décrit le formulaire ;
- `allowed_roles` la liste séparée par une virgule des rôles autorisés à voir et poster des billets de blog - par défaut éditeur, auteur et contributeur;
- `post_status` l'état du billet de blog après l'envoi du billet de blog : `pending` pour en attente ou `published` pour publié - par défaut à `published` ;
- `show_categories` le fait d'afficher les catégories dans le formulaire `yes` pour l'afficher ou `no` pour ne pas l'afficher - par défaut à `yes`;
- `show_tags` le fait d'afficher les tags dans le formulaire `yes` pour l'afficher ou `no` pour ne pas l'afficher - par défaut à `yes`;
- `show_featured_image`  le fait d'afficher l'image d'illustration du billet dans le formulaire `yes` pour l'afficher ou `no` pour ne pas l'afficher - par défaut à `yes`.

## Comment modifier cette extension

### Arborescence de fichier

Ce dépôt est organisé comme il suit :

 - `src` contient les sources de l'extension :
   - `tisseurs-event-scheduler.php` est le fichier principal de l'extension,
   - le répertoire `assets` contient le fichier JavaScript et le fichier de style de l'extension,
   - le répertoire `languages` contient les fichiers liés à l'internationalisation ;
 - `build.bash` est un script bash pour créer l'archive Zip permettant d'installer l'extension ;
 - `composer.json` contient la configuration du paquet composer ;
 - `composer.lock` contient les versions des paquets composer installés.

### Travailler sur le projet

Pour travailler sur le projet, vous aurez besoin de [PHP](https://www.php.net), de [composer](https://getcomposer.org/) et de [gettext](https://www.gnu.org/software/gettext/).

#### Installer les dépendances 🚚

Il faut utiliser la commande `composer install`.

```bash
composer install
```

Et ensuite configurer PHP autoload avec la commande `composer -d . dump-autoload`.

```bash
composer -d . dump-autoload
```

#### Construire l'extension 🛠️

Pour construire l'extension comme archive installable dans WordPress, il faut utiliser la commande `composer build`.

```bash
composer build
```

#### Test unitaire de l'extension 🧪

**Pas encore**
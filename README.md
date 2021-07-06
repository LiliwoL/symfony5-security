# Sécurité dans Symfony 5


## Sécurisation 


### Organisation des controllers et des routes

On dispose ici de 3 controllers.
* AdminController
    * Route: /admin
* UserController
    * Route: /user
* PublicController
    * Route: /public

Pour organiser l'application, on crée la structure suivante dans le dossier *src/Controller* pour avoir dans un dossier *Admin* les controllers d'administration:
```
src\
    Controller\
        Admin\
            AdminController.php
```

Les controllers d'administration devront donc avoir le *namespace* suivant:

```
namespace App\Controller\Admin;
```

### Activation de la sécurisation

Nous allons ensuite protéger l'accès à toutes les routes d'administration (commençant par /admin) en modifiant le fichier *config/packages/security.yaml*:

```
    # Easy way to control access for large sections of your site
    # Note: Only the *first* access control that matches will be used
    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/user, roles: ROLE_USER }
```

* Seuls les utilisateurs ayant le "ROLE_ADMIN" accèderont aux routes commencant par */admin*.
* Seuls les utilisateurs ayant le "ROLE_USER" accèderont aux routes commencant par */user*.
* Les utilisateurs non connectés peuvent accéder aux routes commencant par */public*.

## Classe des Users

Que les informations de l'utilisateur soient stockées en base ou non, on crée une classe *User*:

```
symfony console make:user
```
Le fichier *src\Entity\Users* est alors créé.

Par défaut on utilisera l'*email* comme identifiant.

Pour appliquer les changements dans la base:
```
symfony console make:migration
symfony console doctrine:migrations:migrate
```

## User provider

Il faut ensuite spécifier au compsant sécurité la *source* des utilisateurs.
Dans le fichier *config/packages/security.yml*:

```
# config/packages/security.yaml
security:
    # ...

    providers:
        # used to reload user from session & other features (e.g. switch_user)
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email
```

Si la *source* n'était pas une entité classique, il faudrait alors créer une classe **UserProvider** qui définirait la marche à suivre pour aller chercher les utilisateurs:

https://symfony.com/doc/current/security/user_provider.html

## Hashage des mots de passe

Les mots de passe sont stockés en base hashés.
On doit alors indiquer au composant sécurité comment ces mots de passe sont hashés.

> Avant Symfony 5, on ne parlait pas de **hash** mais de **encoders**

```
# config/packages/security.yaml
security:
    # ...

    password_hashers:
        # use your user class name here
        App\Entity\User:
            # Use native password hasher, which auto-selects the best
            # possible hashing algorithm (starting from Symfony 5.3 this is "bcrypt")
            algorithm: auto
```

### Utilisation de UserPasswordHasherInterface

Dans une fixture, pour nous créer 2 utilisateurs par exemple, on peut utiliser *UserPasswordHasherInterface*.

```
php bin/console make:fixtures

The class name of the fixtures to create (e.g. AppFixtures):
> UserFixtures
```

Dans le fichier *src/DataFixtures/UserFixtures.php*:

```
<?php
namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encode;
 
    public function __construct(UserPasswordHasherInterface $encoder) {
        $this->encode = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $user = new User();
        $user->setEmail('user@user.fr');
        
        // Encodage du mot de passe
        $password = $this->encode->hashPassword($user, "user");

        $user->setPassword($password);
        $user->setRoles(['ROLE_USER']);
        
        $manager->persist($user);

        // #######################

        $admin = new User();
        $admin->setEmail('admin@admin.fr');
        
        // Encodage du mot de passe
        $password = $this->encode->hashPassword($admin, "admin");

        $admin->setPassword($password);
        $admin->setRoles(['ROLE_ADMIN']);
        
        $manager->persist($admin);
        $manager->flush();
    }
}
```

On peut aussi encoder manuellement un mot de passe:
```
symfony console security:hash-password
```

## Authentification

```
symfony console make:auth
```

Cette commande de la console permet de créer simplement un *authenticator* de type FormLogin.

On peut créer des *authenticator* personnalisés:
https://symfony.com/doc/current/security/custom_authentication_provider.html


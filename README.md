
# Projet 7 BileMeo

Créez un web service exposant une API



## Les pré-requis techniques

- Symfony 6.4
- Composer 2.5.8
- willdurand/hateoas-bundle
- lexik/jwt-authentication-bundle
- nelmio/api-doc-bundle
- XAMPPServer: ->control panel:3.33.0 ->Apache: 2.4.5 ->Mysql: 5.2.1 ->PHP: 8.1




## Installation

1.Clonez ou téléchargez le repository GitHub dans le dossier voulu :

```bash
git clone https://github.com/alleidda/P7BileMo.git
```
2.Editez le fichier situé à la racine intitulé .env.local qui devra être crée à la racine du projet en réalisant une copie du fichier .env afin de remplacer les valeurs de paramétrage de la base de données:

```bash
//Exemple : mysql://root:@127.0.0.1:3306/API_projet7
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"

```
3.Installez les dépendances back-end du projet avec Composer:

```bash
composer install

```
4.Créez la base de données, taper la commande ci-dessous en vous plaçant dans le répertoire du projet:

```bash
symfony console doctrine:database:create

```
5.Créez les différentes tables de la base de données en appliquant les migrations:

```bash
symfony console doctrine:migrations:migrate

```
6.Après avoir créer votre base de données, vous pouvez également injecter un jeu de données en effectuant la commande suivante:

```bash
symfony console doctrine:fixtures:load

```
7.Créer le dossier Jwt pour stocke les clés SSL pour le token

```bash
mkdir -p config/jwt

```
8.Générer des clés SSL pour le token JWT:

```bash
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096

```

```bash
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout

```
9.Remplacer votre paraphrase par celui présent dans .env.local (JWT_PASSPHRASE)

10.Lancez votre serveur

```bash
symfony server:start

```

11.Test :

```bash
Allez sur http://127.0.0.1:8000/api/doc et suivez les instructions
(Ou le lien  génerer lors du lancement de votre serveur)

```


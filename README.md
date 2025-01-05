# WordPress starter project

Starts a WordPress project from scratch using [Lando](https://lando.dev/), [bedrock](https://roots.io/bedrock/), [sage](https://roots.io/sage/) and [Deployer](https://deployer.org/).

## What does it do?

This project uses deployer to set up a WordPress environment using:

- Lando for the local environment.
    - It uses [Lando WordPress Starter Project](https://github.com/raramente/lando-start) to add cool feature to the environment.
- Installs WordPress using [Bedrock](https://roots.io/bedrock/) WordPress boilerplate.
- Install a new theme using [Sage](https://roots.io/sage/) Advanced WordPress starter theme.
- It sets up Deployer for easy deploy, files and database management.
    - It uses [Deployer WordPress starter project](https://github.com/raramente/deployer-start) to add some cool features.

## Set up

1. Download this repo to the folder where you want to set up your new WordPress project.
2. Edit `setup.yaml` with the variables for your project.
3. Run:

```bash
dep setup
```

## Under the hood

This project uses two other projects in order to set up, install and deploy WordPress websites:

- https://github.com/raramente/lando-start - A starter project to set up the local environment using Lando.
- https://github.com/raramente/deployer-start - A starter project to deploy WordPress websites using Deployer.

What this project does is to download and configure each one of the projects from github make them work toghether so all the tools are there for the developer.

## Tools available

```bash
lando composer require "wpackagist-plugin/contact-form-7":"6.0.2"
lando wp user list

dep deploy
dep files:download
dep files:upload
dep db:local:backup
dep db:backup
dep db:download
dep db:upload
```

Please read more on each of the projects.
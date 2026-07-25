Docker
======

This directory is only used to help the contributing developers. 
It creates a docker environment with PHP 

How to build image?
----------------------
To help maintainer, this library comes with a docker environment.
It builds an image of the minimum PHP version.
This version is compiled to contain all needed tools.

```shell
cd docker
docker compose run --rm spatial-writer php --version
```

How to load dependencies?
-------------------------
Composer is already installed in the image.

```shell
docker compose run --rm spatial-writer composer update
```

How to start test?
------------------
```shell
docker compose run --rm spatial-writer vendor/bin/phpunit
```

# Monica (multi-arch)

## Introduction

Monica is a well known personal CRM. You can read all about it [in their repository](https://github.com/monicahq/monica).

In this repository you will find a docker image for Monica that is multi-arch compatible, meaning that it will run on both Intel and ARM processors
such as Raspberry PI's.

## Installation

In this repository you'll find `monica.env` that's filled with default values. Edit the file, and save it somewhere local. Then, 
run the following command to start Monica HQ:

```bash
docker volume create monica_data
docker run --name=monica --env-file=monica.env -e FIRST_USER_EMAIL=your@email.com -p 80:80 -v monica_data:/var/www/storage -d jc5x/monicahq-multi-arch:develop
```

This should start a pretty basic (but empty) instance of Monica HQ, no matter on what architecture you're running.

### Detailed run command

Below you will find exactly what each part of the Docker command does. You can change the behaviour of the command if you want to.

#### Create a volume

This commands creates a volume for Monica to store its data.

```bash
docker volume create monica_data
```

#### Start the container

The name of the container:

```
--name=monica
```

The file that contains the configuration. If you change values in this file, restart the container to apply them:

```
--env-file=monica.env
```

If the database is empty, create new user with this email address

```
-e FIRST_USER=your@email.com
```

docker run    -p 80:80 -v monica_data:/var/www/storage -d jc5x/monicahq-multi-arch:develop



## Empty database?

This image can do data migrations (useful for upgrades). If the database does not exist, the image won't run. If the database exists it will create all tables
and create a default user with a random password.

* `admin@admin.com` with password `admin0`.
* `blank@blank.com` with password `blank0`.

Make *sure* you either delete or disable these accounts.  

## Weekly build

This script is built every week.
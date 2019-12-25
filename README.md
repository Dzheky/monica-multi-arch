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
docker run --name=monica --env-file=monica.env -e FIRST_USER=your@email.com -p 80:80 -v monica_data:/var/www/storage -d jc5x/monica-multi-arch
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

It will expose itself on port 80. Change the *first* 80 to something else, if you want to expose Monica on a different port.

```
-p 80:80
```

This makes sure Monica's data is persisted.

```
-v monica_data:/var/www/storage
```

And this is a reference to the actual image:

```
-d jc5x/monica-multi-arch
```


## Weekly build

This script is built every week.
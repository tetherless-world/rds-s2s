RDS Dataset OpenSearch Service
======================================

# Intro

A PHP implementation of the RDS Dataset OpenSearch service utilized by the RDS Dataset S2S Browser.

The service may be deployed using Docker.

# Requirements

|Name			|Version		|Comment										|
|:--------------|:-------------:|:----------------------------------------------|
|Docker			|>= 1.3 		|works with Boot2docker 1.3						|
|OS				|any	 		|as long as you can run Docker 1.3				|

# Usage

TODO

Customizations
==============

Currently several program options are hardcoded in the PHP code. Future updates will move these options into the rds.ini configuration file and also make these settings addressable via environment variables.

Docker
======

Build a docker image of RDS Dataset OpenSearch service by running this command at the project root.

```
$ sudo docker build -t="rds-dataset-opensearch" .
```

Now check that the image was successfully created and stored to the local docker repository

```
$ sudo docker images
```

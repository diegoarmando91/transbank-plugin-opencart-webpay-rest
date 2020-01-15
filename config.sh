#!/usr/bin/env bash

#Script for configure the plugin project
echo "Installing transbank SDK throughout composer"
cd src/upload/system/library/transbank
composer install --prefer-dist --no-dev

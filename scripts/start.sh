#!/usr/bin/env bash
pushd ..
php scripts/index.php license:list composer.lock.example
popd

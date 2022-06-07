#!/bin/sh

# This is script is  only meant to be run from a Github action

CURRENT_BRANCH="$(git branch --show-current)"

if [ $CURRENT_BRANCH != 'beta' ] && [ $CURRENT_BRANCH != 'alpha' ]; then
    ./vendor/bin/monorepo-builder release $1
fi

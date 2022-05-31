#!/bin/sh

CURRENT_BRANCH="$(git branch --show-current)"

if [ $CURRENT_BRANCH != 'beta' ] && [ $CURRENT_BRANCH != 'alpha' ]; then
    ./vendor/bin/monorepo-builder release $1
fi

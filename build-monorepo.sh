#!/bin/sh

CURRENT_BRANCH="$(git branch --show-current)"

[ $CURRENT_BRANCH != 'beta' ] && [ $CURRENT_BRANCH != 'alpha' ] && ./vendor/bin/monorepo-builder release $1

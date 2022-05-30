#!/bin/sh

CURRENT_BRANCH="$(git branch --show-current)"

[[ $CURRENT_BRANCH != 'beta' && $CURRENT != 'alpha' ]] && ./vendor/bin/monorepo-builder release $1

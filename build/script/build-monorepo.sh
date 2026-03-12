#!/bin/bash

# This is script is only meant to be run from a Github Action.

CURRENT_BRANCH="$(git branch --show-current)"

if [ "$CURRENT_BRANCH" != "beta" ] && [ "$CURRENT_BRANCH" != "alpha" ]; then
    php -d "error_reporting=E_ALL&~E_DEPRECATED&~E_STRICT" -f ./vendor/bin/monorepo-builder release "$1"
fi

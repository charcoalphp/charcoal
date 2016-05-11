# From https://github.com/ApiGen/ApiGen/wiki/Generate-API-to-Github-pages-via-Travis

# echo "TRAVIS_REPO_SLUG: $TRAVIS_REPO_SLUG"
# echo "TRAVIS_PHP_VERSION: $TRAVIS_PHP_VERSION"
# echo "TRAVIS_PULL_REQUEST: $TRAVIS_PULL_REQUEST"
# echo "TRAVIS_BRANCH: $TRAVIS_BRANCH"
# echo "TRAVIS_BUILD_NUMBER: $TRAVIS_BUILD_NUMBER"

if [ "$TRAVIS_REPO_SLUG" == "locomotivemtl/charcoal-config" ] && [ "$TRAVIS_PULL_REQUEST" == "false" ] && [ "$TRAVIS_PHP_VERSION" == "5.6" ]; then

    echo -e "Publishing ApiGen to Github Pages...\n";

    cd $HOME

    # Get apigen binary
    wget http://www.apigen.org/apigen.phar

    ## Initialisation et recuperation de la branche gh-pages du depot Git
    git config --global user.email "travis@travis-ci.org"
    git config --global user.name "travis-ci"
    git clone --quiet --branch=gh-pages https://${GH_TOKEN}@${GH_REPO} api-pages > /dev/null

    cd api-pages

    ## Suppression de l'ancienne version
    git rm -rf ./apigen/$TRAVIS_BRANCH

    ## Création des dossiers
    mkdir apigen
    cd apigen
    mkdir $TRAVIS_BRANCH

    # Generate Api
    php $HOME/apigen.phar generate -s src -d .

    git add -f .
    git commit -m "ApiGen (Travis Build : $TRAVIS_BUILD_NUMBER  - Branch : $TRAVIS_BRANCH)"
    git push -fq origin gh-pages > /dev/null

    echo -e "Published ApiGen to gh-pages.\n"
    echo -e ">>> http://locomotivemtl.github.io/charcoal-config/apigen/$TRAVIS_BRANCH/ \n"
fi

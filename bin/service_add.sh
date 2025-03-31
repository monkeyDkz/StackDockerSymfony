#! /bin/bash

#set -x

QUIT_CHOICE=5

if [[ $QUIET -ne 1 ]]; then
    QUIET=0
fi

docker compose exec php ls >/dev/null 2>/dev/null
[[ $? -eq 0 ]] && DOCKER_UP=1 || DOCKER_UP=0

function log() {
  [[ "$QUIET" -eq 0 ]] && echo "$1"
}

function displayMenu() {
  echo "1) Mailer"
  echo "2) Mercure"
  echo "3) Panther (E2E testing)"
  echo "4) Custom packages"
  echo "${QUIT_CHOICE}) Quitter"
}

function install_package() {
  echo "Installing package $1..."
  [[ "$DOCKER_UP" -eq 1 ]] && DOCKER_OPTION=exec || DOCKER_OPTION="run --rm"
  docker compose $DOCKER_OPTION php composer require $1
}

function install_service() {
  install_package "$1"
  [[ "$2" -eq 1 ]] && PACKAGE_INSTALLED=1
}

function custom_install() {
  read -p "Package à installer : " packages
  install_package "$packages"
}

function update_docker() {
  echo "New packages found that need docker installation to be updated"
  if [[ "$DOCKER_UP" -eq 1 ]]; then
    echo "  => Stopping docker"
    docker compose stop
  fi
  echo "  => Rebuilding docker"
  docker compose pull --ignore-buildable
  docker compose build --pull --no-cache
  if [[ "$DOCKER_UP" -eq 1 ]]; then
    echo "  => Restarting docker"
    docker compose up -d
  fi
}

choice=0
while [ "$choice" -ne "$QUIT_CHOICE" ];do
  displayMenu
  read -p "Votre choix : " choice
  case $choice in
    1) install_service symfony/mailer 1;;
    2) install_service symfony/mercure-bundle 1;;
    3) install_service symfony/panther 1;;
    4) custom_install;;
    ${QUIT_CHOICE}) ;;
    *) echo "Cette option n'est pas disponible"
    choice=0;;
  esac
done


if [[ "$PACKAGE_INSTALLED" -eq 1 ]]; then
  update_docker
fi

#!/usr/bin/env bash

set -o errexit
set -o nounset
set -o pipefail

readonly PHP_INI_DIR=${PHP_INI_DIR}
readonly PHP_ENV=${PHP_ENV}
readonly SYMFONY_ENABLED=${SYMFONY_ENABLED}
readonly SCRIPTS_FOLDER=${SCRIPTS_FOLDER}

setConfig() {
	echo "  ##--> Using proper php.ini settings";
	local php_ini_recommended;

	php_ini_recommended="${PHP_INI_DIR}/php.ini-production";
	echo "    |> ${PHP_ENV}";

	if [ 'prod' != "${PHP_ENV}" ]; then
		php_ini_recommended="${PHP_INI_DIR}/php.ini-development";
	fi
	sudo ln -sf "$php_ini_recommended" "${PHP_INI_DIR}/php.ini";

	echo "  ##--> Using proper symfony.ini settings if needed";
	local symfony_ini_recommended;
	if [ true = "${SYMFONY_ENABLED}" ]; then
		echo "    |> ${PHP_ENV}";

		symfony_ini_recommended="/var/www/conf.d/symfony.prod.ini";
		if [ 'prod' != "${PHP_ENV}" ]; then
			symfony_ini_recommended="/var/www/conf.d/symfony.dev.ini";
		fi

		sudo ln -sf "/var/www/conf.d/symfony.ini"   "${PHP_INI_DIR}/conf.d/za-symfony.ini";
		sudo ln -sf "${symfony_ini_recommended}"    "${PHP_INI_DIR}/conf.d/zb-symfony.ini";
	fi
}

execScripts() {
	find "${SCRIPTS_FOLDER}/" -name '*.sh' -type f -print0 | sort -z | while read -r -d $'\0' script
	do
		echo -e "\n    #####> Starting : ${script} <#####\n";
		$script;
		echo -e "\n    #####> Finished : ${script} <#####\n";
	done
}

setConfig
execScripts

exec docker-php-entrypoint "$@"
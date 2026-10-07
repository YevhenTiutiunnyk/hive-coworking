#!/usr/bin/env bash
# Builds installable zips of the plugin and theme into dist/.
# The plugin zip contains compiled blocks and translations, but no sources, tests or dev tooling.
set -euo pipefail
cd "$(dirname "$0")/.."

rm -rf dist
mkdir -p dist

npm run build --silent

(cd plugins && zip -qr ../dist/hive-core.zip hive-core \
	-x 'hive-core/blocks/*' 'hive-core/tests/*' 'hive-core/phpunit*.xml.dist' 'hive-core/*.pot' '*/.*' '*.DS_Store')
(cd themes && zip -qr ../dist/hive.zip hive -x 'hive/*.pot' '*/.*' '*.DS_Store')

ls -lh dist

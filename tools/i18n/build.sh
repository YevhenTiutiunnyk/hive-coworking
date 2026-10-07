#!/usr/bin/env bash
# Regenerates the POT files, fills the Russian translations and compiles them.
# Runs WP-CLI inside wp-env, so `npm run env:start` must be running.
set -euo pipefail
cd "$(dirname "$0")/../.."

npm run build --silent

wp() { npx wp-env run cli --env-cwd=wp-content wp "$@"; }

wp i18n make-pot plugins/hive-core plugins/hive-core/languages/hive-core.pot \
	--slug=hive-core --domain=hive-core --exclude=blocks,tests,node_modules,vendor
wp i18n make-pot themes/hive themes/hive/languages/hive.pot --slug=hive --domain=hive

python3 tools/i18n/build_po.py plugins/hive-core/languages/hive-core.pot plugins/hive-core/languages/hive-core-ru_RU.po PLUGIN tools/i18n/ru.py
python3 tools/i18n/build_po.py themes/hive/languages/hive.pot themes/hive/languages/ru_RU.po THEME tools/i18n/ru.py

for dir in plugins/hive-core/languages themes/hive/languages; do
	wp i18n make-mo "$dir"
	wp i18n make-php "$dir"
done

# JavaScript translations for the block editor scripts.
rm -f plugins/hive-core/languages/*.json
wp i18n make-json plugins/hive-core/languages --no-purge --pretty-print

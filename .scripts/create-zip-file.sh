# Creates a Plugin ZIP File of the current code, useful for installing on test sites
rm convertkit.zip
zip -r convertkit.zip . -x "*.git*" -x ".wordpress-org/*" -x "tests/*" -x "vendor/*" -x "*.distignore" -x "*.env.*" -x "*codeception.*" -x "composer.json" -x "composer.lock" -x "*.md" -x "log.txt" -x "package-lock.json" -x "package.json" -x "phpcs.xml" -x "*.DS_Store"
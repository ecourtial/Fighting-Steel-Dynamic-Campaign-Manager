phpcs:
	vendor/bin/phpcs -n

cleancode:
	vendor/bin/php-cs-fixer fix --allow-risky=yes --diff
	vendor/bin/phpcbf

phpstan:
	vendor/bin/phpstan clear-result-cache
	vendor/bin/phpstan analyse

phpcsfixer:
	vendor/bin/php-cs-fixer fix --dry-run --allow-risky=yes --diff

test:
	vendor/bin/phpunit --testdox --coverage-html var/phpunit/coverage-html
# If you want it to run locally on a specific version of PHP: /usr/bin/php7.4 vendor/bin/phpunit --testdox --coverage-html var/phpunit/coverage-html

infection:
	vendor/bin/infection --threads=4

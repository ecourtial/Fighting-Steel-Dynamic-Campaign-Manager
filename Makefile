phpcs:
	vendor/bin/phpcs

phpstan:
	vendor/bin/phpstan analyse

phpcsfixer:
	vendor/bin/php-cs-fixer fix --dry-run --allow-risky=yes

test:
	bin/phpunit --testdox

infection:
	vendor/bin/infection

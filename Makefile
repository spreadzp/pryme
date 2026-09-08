.PHONY: lint lint-fix stan check test

# PHP CodeSniffer — check WordPress coding standards
lint:
vendor/bin/phpcs --standard=.phpcs.xml.dist

# PHP CodeSniffer — auto-fix violations
lint-fix:
vendor/bin/phpcbf --standard=.phpcs.xml.dist

# PHP CS Fixer — auto-format code
fix:
vendor/bin/php-cs-fixer fix

# PHPStan — static analysis (level 5)
stan:
vendor/bin/phpstan analyse

# Run all checks
check: lint stan

# Run tests (when added)
test:
@echo "No tests yet"

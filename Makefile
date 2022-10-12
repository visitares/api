install-prod:
	composer self-update
	composer install -o --no-dev --no-interaction
	mkdir -p -m 0666 ./var/cache
	mkdir -p -m 0666 ./var/logs
	mkdir -p -m 0666 ./var/tmp
	mkdir -p -m 0666 ./var/user

update-prod:
	composer self-update
	composer update -o --no-dev --no-interaction

install-dev:
	composer self-update
	composer install --no-interaction
	mkdir -p -m 0666 ./var/cache
	mkdir -p -m 0666 ./var/logs
	mkdir -p -m 0666 ./var/temp
	mkdir -p -m 0666 ./var/user

update-dev:
	composer self-update
	composer update --no-interaction

clean:
	rm -r -f ./var

run:
	php -S localhost:1337 -t ./web
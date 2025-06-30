# Makefile for running k6 load tests via docker-compose

# service name as defined in docker-compose.yml
K6_SERVICE := k6

.PHONY: k6 clean

## bring the symfony app up
up:
	docker-compose stop app
	docker-compose build app
	docker-compose up -d prometheus grafana php-fpm-exporter
	docker-compose up app

exporter:
	docker-compose build php-fpm-exporter
	docker-compose up php-fpm-exporter

## loadtest: build & run the k6 service, then tear down containers
k6:
	k6 run ./k6/loadtest.js

## clean: stop & remove containers, networks, volumes
clean:
	docker-compose down -v --remove-orphans

## help: show available targets
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
	awk 'BEGIN {FS = ":.*?## "}; {printf "  %-15s %s\n", $$1, $$2}'

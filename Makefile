K6_SERVICE := k6

.PHONY: k6 clean

build:
	docker-compose build

up:
	docker-compose up -d app

down:
	docker-compose up -d down

up-franken:
	docker-compose up franken -d

down-franken:
	docker-compose stop franken

up-worker:
	docker-compose up franken-worker -d

down-worker:
	docker-compose stop franken-worker

up-grafana:
	docker-compose up prometheus grafana -d

down-grafana:
	docker-compose stop prometheus grafana

up-opcache-dashboard:
	docker-compose up -d opcache-dashboard

down-opcache-dashboard:
	docker-compose down opcache-dashboard

up-exporter:
	docker-compose up -d php-fpm-exporter

down-exporter:
	docker-compose down php-fpm-exporter

app-shell:
	docker-compose exec -it app bash

franken-shell:
	docker-compose exec -it franken bash

worker-shell:
	docker-compose exec -it franken bash

k6:
	k6 run ./k6/loadtest.js

clean:
	docker-compose down -v --remove-orphans

## help: show available targets
help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
	awk 'BEGIN {FS = ":.*?## "}; {printf "  %-15s %s\n", $$1, $$2}'

include .env.example
sshContainer=php
sshContainerName=${APP_NAME}-php

build: ## Builds docker-compose
	cp .env.example .env && make set-user-group  && cd .docker && docker-compose build --no-cache $(sshContainer)

set-user-group: ## Set user and group IDs in .env
	@sed -i.bak -e "s/^DOCKER_USER_ID=.*/DOCKER_USER_ID=$(shell id -u)/" .env && rm -f .env.bak
	@sed -i.bak -e "s/^DOCKER_GROUP_ID=.*/DOCKER_GROUP_ID=$(shell id -g)/" .env && rm -f .env.bak


install: ## First installation
	make restart && \
	docker-compose exec $(sshContainer) bash -c "composer install && composer dump-autoload"

start: ## Starts docker-compose
	docker-compose up -d $(serviceList)

restart: ## Stops and restarts docker-compose
	make stop && make start

ssh: ## SSH to docker container
	docker-compose exec $(sshContainer) bash

kill: ## Stops all docker containers
	docker stop $(shell docker ps -aq)

stop: ## Stops docker-compose
	docker-compose down

prune: ## Clear build cache
	sudo docker system prune -af

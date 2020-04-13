RUNTIME_TAG='polonaiz/bigquery-helper-cli-runtime'

build: \
	runtime-build \
	composer-install-using-runtime

runtime-build:
	docker build \
		--tag ${RUNTIME_TAG} \
		./env/docker

runtime-bash:
	docker run --rm -it \
 		${RUNTIME_TAG} bash

composer-install-using-runtime:
	docker run --rm -it \
		-v $(shell pwd):/opt/project \
		-v ~/.composer:/root/.composer \
 		${RUNTIME_TAG} composer -vvv install -d /opt/project

composer-update-using-runtime:
	docker run --rm -it \
		-v $(shell pwd):/opt/project \
		-v ~/.composer:/root/.composer \
 		${RUNTIME_TAG} composer -vvv update -d /opt/project

composer-clean:
	rm -rf ./vendor
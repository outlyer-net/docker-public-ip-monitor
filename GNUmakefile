# Official and semi-official architectures: https://github.com/docker-library/official-images#architectures-other-than-amd64
ARCHITECTURES=linux/amd64,linux/arm64,linux/386,linux/arm/v7,linux/arm/v6
IMAGE_NAME=outlyernet/public-ip-monitor

DOCKER_BUILDX=env DOCKER_CLI_EXPERIMENTAL=enabled docker buildx
# This makefile uses the builder only temporarily and destroys it when done
MULTIARCH_BUILDER_NAME=$(subst /,_,$(IMAGE_NAME))-multiarch-builder

# Build locally
build:
	docker build \
		--tag $(IMAGE_NAME) \
		--tag $(IMAGE_NAME):$(shell date +%Y%m%d_%H%M%S) \
		.

# Live test with router and services mounted from the host, allows
#  testing edits without a rebuild
test: build ip-history.txt
	docker run --rm -it -p 8000:80 \
		-v "$(PWD)/imgroot/data/services.txt:/data/services.txt:ro" \
		-v "$(PWD)/imgroot/www/router.php:/www/router.php:ro" \
		-v "$(PWD)/imgroot/update.sh:/update:ro" \
		-v "$(PWD)/ip-history.txt:/data/ip-history.txt:rw" \
		-v /etc/localtime:/etc/localtime:ro \
		$(IMAGE_NAME)

# Tests the update script with Alpine's shell (busybox)
test-update: build ip-history.txt
	docker run --rm -it \
		-v "$(PWD)/ip-history.txt":/data/ip-history.txt:rw \
		-v /etc/localtime:/etc/localtime:ro \
		--entrypoint /update \
		$(IMAGE_NAME)

###########
########### Rules below are for maintenance of the Docker Hub repository
###########  and require credentials
###########

# XXX: As of this writing (2020-03) running this with --load instead of --push (i.e. locally),
#      fails. Support for multi-arch in the daemon is pending, see https://github.com/docker/buildx/issues/59
push:
	-$(MAKE) multiarch-bootstrap
	$(DOCKER_BUILDX) build --platform $(ARCHITECTURES) \
		--tag $(IMAGE_NAME) \
		--push \
		.
	-$(MAKE) multiarch-unbootstrap
	$(MAKE) push-readme

inspect:
	$(DOCKER_BUILDX) imagetools inspect $(IMAGE_NAME)

# Enable the new enhanced multi-arch support (requires Docker 19.03
# and experimental mode)
# https://docs.docker.com/docker-for-mac/multi-arch/
multiarch-bootstrap:
	$(DOCKER_BUILDX) create --use --name $(MULTIARCH_BUILDER_NAME)

multiarch-unbootstrap:
	$(DOCKER_BUILDX) rm $(MULTIARCH_BUILDER_NAME)

# Since currently multi-arch images can't use Docker Hub's autobuilders,
# the README won't get updated automatically, this rule updates it.
push-readme:
	docker run --rm \
		-v $(PWD)/README.md:/data/README.md \
		-e DOCKERHUB_USERNAME=$(shell echo $(IMAGE_NAME) | cut -d/ -f1) \
		-e DOCKERHUB_REPO_PREFIX=$(shell echo $(IMAGE_NAME) | cut -d/ -f1) \
		-e DOCKERHUB_REPO_NAME=$(shell echo $(IMAGE_NAME) | cut -d/ -f2) \
		-e DOCKERHUB_PASSWORD=$(shell relevation hub.docker.com 2>/dev/null | sed -E -e '/^Password/!d' -e 's/^(\w|:)*\s//') \
		sheogorath/readme-to-dockerhub

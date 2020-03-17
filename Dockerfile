# Image to monitor your public IP address.
#
# This Dockerfile creates a multi-arch image leveraging the
# newer builtin support in Docker (see https://github.com/docker/buildx,
# https://docs.docker.com/docker-for-mac/multi-arch/)
#
# <https://github.com/outlyer-net/docker-public-ip-monitor>

# Stage 0: Prepare files for stage 1
FROM alpine:latest AS stage0
COPY imgroot /imgroot
RUN sed -i -e '/^#/d' -e '/^[[:space:]]*$/d' /imgroot/data/services.txt
RUN mv /imgroot/update.sh /imgroot/update && chmod 0755 /imgroot/update

# Stage 1: The actual image
FROM outlyernet/php-cli:latest
LABEL maintainer="Toni Corvera <outlyer@gmail.com>"

RUN apk --no-cache add curl

COPY --from=stage0 /imgroot/ /
ENV USE_SERVICE=0
ENV UPDATE_TIMEOUT=10 

VOLUME [ "/data" ]

EXPOSE 80/tcp

# run web server
ENTRYPOINT ["php","-S","0.0.0.0:80","-t","/www/","/www/router.php"]

HEALTHCHECK --interval=30s \
	--timeout=30s \
	--start-period=10s \
	--retries=3 \
	CMD [ "pidof", "php" ]

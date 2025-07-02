FROM node:16 AS node-build

ARG VERSION

RUN \
    git clone https://github.com/GoMetric/opcache-dashboard /build && \
    cd build && \
    git checkout tags/${VERSION} -b build  && \
    make assets-build-prod


FROM golang:1.24.2 AS go-build

COPY --from=node-build /build/ /build/

RUN cd /build && make binary


FROM alpine:3.21.3

COPY --from=go-build /build/bin/opcache-dashboard /usr/local/bin/

ENTRYPOINT ["/usr/local/bin/opcache-dashboard"]
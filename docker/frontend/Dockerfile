# syntax=docker/dockerfile:1

FROM node:24-alpine

ARG APP_UID=1000
ARG APP_GID=1000

USER root

RUN deluser node 2>/dev/null || true \
    && delgroup node 2>/dev/null || true \
    && addgroup -g "${APP_GID}" -S app \
    && adduser \
        -u "${APP_UID}" \
        -S \
        -D \
        -G app \
        -h /home/app \
        -s /bin/sh \
        app \
    && mkdir -p /workspace /home/app/.npm \
    && chown -R app:app /workspace /home/app

ENV HOME=/home/app
ENV NPM_CONFIG_CACHE=/home/app/.npm
ENV NPM_CONFIG_UPDATE_NOTIFIER=false
ENV NUXT_TELEMETRY_DISABLED=1

WORKDIR /workspace

USER app

CMD ["sh", "-lc", "tail -f /dev/null"]

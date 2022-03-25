ARG NODE_VERSION=16
FROM node:${NODE_VERSION}-alpine AS node

FROM node AS node_npm

RUN npm install -g npm@latest --force

FROM node_npm AS node_yarn

RUN npm install -g yarn@latest --force

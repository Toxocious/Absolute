FROM node:18-alpine

COPY /absolute/discord /discord
WORKDIR /discord

RUN yarn install
RUN yarn build

EXPOSE 3000 3306

CMD [ "yarn", "start:prod", "mysql:3306" ]

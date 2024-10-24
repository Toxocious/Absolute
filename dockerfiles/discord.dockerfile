FROM node:18-alpine

COPY /absolute/discord /discord
WORKDIR /discord

RUN npm install
RUN npm run build

EXPOSE 3000 3306

CMD [ "npm", "run", "start:dev", "mysql:3306" ]

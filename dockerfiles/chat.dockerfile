FROM node:18-alpine

COPY /absolute/chat /chat
WORKDIR /chat

RUN npm install
RUN npm run build

EXPOSE 8080 8080

CMD [ "npm", "run", "dev", "mysql:3306" ]

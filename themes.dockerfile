FROM node:18-alpine

RUN npm install -g sass

WORKDIR /app
COPY app/themes/sass ./themes/sass
# COPY app/themes/css ./themes/css

CMD [ "sass", "themes/sass:themes/css" ]



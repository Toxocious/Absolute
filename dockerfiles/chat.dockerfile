FROM node:18-alpine

COPY /absolute/chat /chat
WORKDIR /chat

# Expose the ports
EXPOSE 8080 8080

# Install app dependencies
RUN npm install && \
    npm run build

# Set the default command to run when a container starts
CMD [ "npm", "run", "dev", "mysql:3306" ]

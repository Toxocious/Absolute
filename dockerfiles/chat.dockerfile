# --------------------------
# ----    Build Stage   ----
# --------------------------
FROM node:18-alpine AS build

COPY /absolute/chat /chat
WORKDIR /chat

# Copy package.json and package-lock.json to leverage Docker cache
COPY package*.json ./

RUN npm install

# This layer is cached as long as package files don't change
RUN npm ci

RUN npm run build

# Remove development dependencies
RUN npm prune --production

# --------------------------
# ---- Production Stage ----
# --------------------------
FROM node:18-alpine

WORKDIR /chat

# Create a non-root user and group
RUN addgroup -S appgroup && adduser -S appuser -G appgroup
USER appuser

# Copy only the built application and production node_modules from the build stage
COPY --from=build --chown=appuser:appgroup /chat/node_modules ./node_modules
COPY --from=build --chown=appuser:appgroup /chat/package.json ./
COPY --from=build --chown=appuser:appgroup /chat/build ./build

# Expose the ports the app runs on
EXPOSE 8080

# The command to run the application in production
CMD [ "npm", "run", "start", "mysql:3306" ]

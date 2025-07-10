# ---- Build Stage ----
# Use a specific version of Node on Alpine Linux as the build environment
FROM node:18-alpine AS build

# Set the working directory
COPY /absolute/chat /chat
WORKDIR /chat

# Copy package.json and package-lock.json to leverage Docker cache
COPY package*.json ./

# Install all dependencies, including devDependencies needed for the build
RUN npm install

# Build the application
RUN npm run build

# Remove development dependencies
RUN npm prune --production

# ---- Production Stage ----
# Use a fresh, lightweight Node.js Alpine image for the final stage
FROM node:18-alpine

# Set the working directory
WORKDIR /chat

# Create a non-root user and group
RUN addgroup -S appgroup && adduser -S appuser -G appgroup
USER appuser

# Copy only the built application and production node_modules from the build stage
COPY --from=build --chown=appuser:appgroup /app/dist ./dist
COPY --from=build --chown=appuser:appgroup /app/node_modules ./node_modules
COPY --from=build --chown=appuser:appgroup /app/package.json .

# Expose the port the app runs on
EXPOSE 8080

# The command to run the application in production
CMD [ "npm", "run", "start", "mysql:3306" ]

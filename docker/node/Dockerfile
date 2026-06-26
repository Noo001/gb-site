FROM node:22-slim

WORKDIR /var/www/frontend

# Install basic tools
RUN apt-get update && apt-get install -y git && rm -rf /var/lib/apt/lists/*

EXPOSE 3000

CMD ["npm", "run", "dev"]

FROM node:22-slim

WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci

COPY frontend ./

ENV NEXT_PUBLIC_API_URL=/api
ENV API_URL=http://nginx-api

RUN npm run build

EXPOSE 3000

CMD ["npm", "start"]

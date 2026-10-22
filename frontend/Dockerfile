# ---- Tahap 1: build aplikasi dengan Node.js ----
FROM node:22.21-alpine AS build
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
# Alamat API untuk image ini bersifat relatif; Nginx meneruskannya ke service backend.
ARG VITE_API_BASE_URL=/api
RUN npm run build

# ---- Tahap 2: sajikan hasil build dengan Nginx (tanpa node_modules) ----
FROM nginx:1.28-alpine
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html
EXPOSE 80

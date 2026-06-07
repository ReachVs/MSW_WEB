FROM node:25-alpine

WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci

COPY frontend ./

COPY docker/node/entrypoint.sh /usr/local/bin/msw-frontend-entrypoint
RUN chmod +x /usr/local/bin/msw-frontend-entrypoint

ENTRYPOINT ["msw-frontend-entrypoint"]
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]

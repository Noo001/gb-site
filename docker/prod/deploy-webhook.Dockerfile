FROM python:3.12-alpine

RUN apk add --no-cache \
    docker-cli \
    docker-cli-compose \
    git \
    bash

WORKDIR /app
COPY deploy-webhook.py /app/deploy-webhook.py
RUN chmod +x /app/deploy-webhook.py

ENV PYTHONUNBUFFERED=1
EXPOSE 9000

CMD ["python3", "/app/deploy-webhook.py"]

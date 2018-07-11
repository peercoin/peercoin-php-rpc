## Docker

This lib includes docker environment with all extensions installed so you can try it. In order to use it run:
```bash
docker build -t peercoin/php-rpc -f .docker/Dockerfile .
docker-compose up -d

## after docker in up and running open the container go to /opt and run composer install/update
docker exec -it peercoin_rpc /bin/bash
cd /opt

```


FROM redis:6-bullseye

COPY redis/redis.conf /usr/local/etc/redis/redis.conf
COPY redis/users.acl /etc/redis/users.acl

COPY shared/assets/redis/entrypoint.sh /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD [ "redis-server", "/usr/local/etc/redis/redis.conf" ]

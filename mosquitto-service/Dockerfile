FROM eclipse-mosquitto:latest

# Crear directorio de configuración
RUN mkdir -p /mosquitto/config

# Crear script que genera configuración dinámica
RUN echo '#!/bin/sh' > /start.sh && \
    echo 'PORT=${PORT:-1883}' >> /start.sh && \
    echo 'echo "listener $PORT 0.0.0.0" > /mosquitto/config/mosquitto.conf' >> /start.sh && \
    echo 'echo "allow_anonymous true" >> /mosquitto/config/mosquitto.conf' >> /start.sh && \
    echo 'echo "persistence true" >> /mosquitto/config/mosquitto.conf' >> /start.sh && \
    echo 'echo "persistence_location /mosquitto/data/" >> /mosquitto/config/mosquitto.conf' >> /start.sh && \
    echo 'echo "log_dest stdout" >> /mosquitto/config/mosquitto.conf' >> /start.sh && \
    echo 'echo "Starting Mosquitto on port $PORT"' >> /start.sh && \
    echo 'exec /usr/sbin/mosquitto -c /mosquitto/config/mosquitto.conf' >> /start.sh && \
    chmod +x /start.sh

EXPOSE 1883

CMD ["/start.sh"]

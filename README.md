# TelegramBot

[![StyleCI](https://styleci.io/repos/75344733/shield?branch=master)](https://styleci.io/repos/75344733)
[![ScrutinizerCI](https://scrutinizer-ci.com/g/AlexR1712/TelegramBot/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/AlexR1712/TelegramBot/?branch=master)

Bot para el Canal de Telegram de la comunidad PHP Vzla

## Instrucciones

- Crear el bot en el [Botfather](https://telegram.me/BotFather) y agregar el token obtenido en la constante *BOT_TOKEN* del archivo _bot.php_
- Descargar [ngrok](https://ngrok.com), ejecutarlo en consola de la forma:  _ngrok http 80_, donde 80 es el puerto donde tu servidor HTTP este en ejecucion.
- Agregar la URL generada por el ngrok, en su version segura HTTPS, en la constante que lleva por nombre *WEBHOOK_URL* .
- Ejecutar por consola el archivo bot.php, para indicarle a Telegram, la URL del webhook donde seran enviadas las peticiones.
- Iniciar conversacion con el bot, o probarlo en un grupo.

## Solucion de Problemas

1.  **cURL error 60: SSL certificate problem: unable to get local issuer certificate**

> Para solventar este problema solo bastara con descargar el archivo [cacert.pem](http://curl.haxx.se/ca/cacert.pem), el cual debes colocar en el directorio de instalacion de php *extras\ssl*, para posteriormente con la ruta completa de ese fichero agregarlo en tu php.ini, bajo la clave *curl.cainfo = fullpathto\cacert.pem*, luego guardar dichos cambios y volver a ejecutar el bot.

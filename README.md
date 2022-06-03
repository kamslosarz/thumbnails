Wymagania:

- php >= 7.4
- composer

Instalacja:

``(php7.4) composer install --no-dev``

Testy:
````
(php7.4) composer install
php7.4 bin/console thumbnails:create
````

Użycie:

Tworzymy .env z configiem:

````
DEFAULT_FTP_HOSTNAME=<hostname dla serwera FTP>
DEFAULT_FTP_PORT=<port dla serwera FTP>
DEFAULT_FTP_USERNAME=<username dla serwera FTP>
DEFAULT_FTP_PASSWORD=<password dla serwera FTP>
DEFAULT_FTP_PATH=<katalog na serwerze FTP>
DEFAULT_ORIGINAL_IMAGES_PATH=<katalog z orginalnymi plikami>
DEFAULT_LOCAL_PATH=<lokalny katalog do zapisu obrazków>
DEFAULT_STORAGE=1/0 (serwer ftp / katalog lokalny)
````

Uruchamiamy w wierszu poleceń:

``(php7.4) bin/console app:create-thumbnails``


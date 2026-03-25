# On utilise une image PHP avec Apache
FROM php:8.2-apache

# On copie tout ton code dans le dossier du serveur
COPY . /var/www/html/

# On donne les permissions au serveur
RUN chown -R www-data:www-data /var/www/html/

# On expose le port 80
EXPOSE 80
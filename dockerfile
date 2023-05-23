# Definieer de basis van de image
FROM php:8.1-apache

# Installeer php extensies
# We hebben PDO nodig om gebruik te maken van de MySQL server
RUN docker-php-ext-install pdo pdo_mysql

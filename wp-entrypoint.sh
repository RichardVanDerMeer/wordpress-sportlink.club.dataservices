#!/bin/bash

# Check if WordPress is installed
if ! wp core is-installed --allow-root --path=/var/www/html/; then
	# Run your command here
	wp core install --allow-root --path=/var/www/html/ --url=$WP_URL --title=$WP_TITLE --admin_email=$WP_EMAIL --admin_user=$WP_USER --admin_password=$WP_PASSWORD
fi

# Start WP-CLI
wp --allow-root --path=/var/www/html/ --http=localhost

wp plugin activate sportlink.club.dataservices --allow-root --path=/var/www/html/

#!/bin/bash
set -e

# Wait for database to be ready
echo "Waiting for database to be ready..."
until wp db check --allow-root 2>/dev/null; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "Database is ready!"

# Check if WordPress is already installed
if ! wp core is-installed --allow-root 2>/dev/null; then
    echo "Installing WordPress..."
    wp core install \
        --url="${WP_SITE_URL}" \
        --title="${WP_SITE_TITLE}" \
        --admin_user="${WP_ADMIN_USER}" \
        --admin_password="${WP_ADMIN_PASSWORD}" \
        --admin_email="${WP_ADMIN_EMAIL}" \
        --skip-email \
        --allow-root

    echo "WordPress installed successfully!"
    echo "URL: ${WP_SITE_URL}"
    echo "Username: ${WP_ADMIN_USER}"
    echo "Password: ${WP_ADMIN_PASSWORD}"
else
    echo "WordPress is already installed."
fi

# Activate Sportlink plugin
echo "Activating Sportlink KNVB Club.Dataservices plugin..."
if wp plugin is-installed sportlink.club.dataservices --allow-root 2>/dev/null; then
    wp plugin activate sportlink.club.dataservices --allow-root
    echo "Sportlink plugin activated successfully!"
else
    echo "Warning: Sportlink plugin not found. Please check the plugin directory."
fi

# Configure Sportlink plugin settings
echo "Configuring Sportlink plugin settings..."
if [ -n "${SPORTLINK_API_KEY}" ]; then
    wp option update sportlink_club_dataservices_key "${SPORTLINK_API_KEY}" --allow-root
    echo "Sportlink API key configured!"
else
    echo "No Sportlink API key provided. You can configure it later in WordPress settings."
fi

# Set cache time (default to 30 if not specified)
CACHE_TIME=${SPORTLINK_CACHE_TIME:-30}
wp option update sportlink_club_dataservices_cachetime "${CACHE_TIME}" --allow-root
echo "Sportlink cache time set to ${CACHE_TIME} minutes."

# Create pages
echo "Creating WordPress pages..."

# Create Programma page if it doesn't exist
if ! wp post list --post_type=page --name=programma --format=count --allow-root | grep -q "^[1-9]"; then
    wp post create --post_type=page --post_title='Programma' --post_status=publish --post_name=programma --allow-root
    echo "Page 'Programma' created successfully!"
else
    echo "Page 'Programma' already exists."
fi

# Create Uitslagen page if it doesn't exist
if ! wp post list --post_type=page --name=uitslagen --format=count --allow-root | grep -q "^[1-9]"; then
    wp post create --post_type=page --post_title='Uitslagen' --post_status=publish --post_name=uitslagen --allow-root
    echo "Page 'Uitslagen' created successfully!"
else
    echo "Page 'Uitslagen' already exists."
fi

# Keep container running
exec "$@"

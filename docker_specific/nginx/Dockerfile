FROM nginx:alpine

RUN rm /etc/nginx/nginx.conf
# Copy custom Nginx configuration
# -----------------------------------------------------------
# Replace the default Nginx configuration with our custom one
# that is optimized for serving a Laravel application.
# -----------------------------------------------------------
COPY ./docker_specific/nginx/nginx.conf /etc/nginx/nginx.conf

# Copy Laravel public directory
COPY ./public /var/www/public

# Set the working directory to the public folder
WORKDIR /var/www/public

# Expose port 80 and start Nginx
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]
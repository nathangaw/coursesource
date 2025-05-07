# Setup
Run composer install and npm install to download dependencies.

# Frontend Tools
We use the @wordpress/scripts package to provide some frontend build tooling
https://www.npmjs.com/package/@wordpress/scripts

# Build process
.js and .css source files should be placed in /resources Then are compiled into /assets with

npm run build

If you need to add additional files, they should first be added to webpack.config.js so that they are included in the build process

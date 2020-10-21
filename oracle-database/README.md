# Oracle Database 18.4.0 XE

## Introduction
The project uses an Oracle database. Unfortunately Oracle doesn't provide a binary Docker image of their Express Edition
(community supported) version of their database. This is why you need to create your own image. 

## Development
Use the `build-dev.sh` to create the local Docker image.

## Testing
The CI environment uses a Docker image from the GitHub package registry. To create/update this image, the 
`build-and-push.sh` script should be used. However, before doing so, you first need to login:

    docker login docker.pkg.github.com -u <username> -p <access_token>

You can get an access token in your GitHub settings.

To build and push the Docker image upstream, you can simply use:

    ./build-and-push.sh

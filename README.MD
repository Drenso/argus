# ARGUS

Argus is a tool used to monitor your projects, based on incoming webhooks from external applications. It soly focusses
on GitLab projects (from a single instance).

Argus internally uses events internally, which means it is fully extendable for your usecase. It would be appreciated
to share your extensions with us, so we can share it with everybody!

# Run

You can run the application from source, or use the docker container

## From source

```
git clone
composer install
yarn install
yarn build
```

Make sure to configure the secrets in the `.secrets.json` and configure the relevant environment in `.env.local`.

Add a user with `bin/console argus:add-user`

## Docker

We offer a prebuild docker container, which contains a fully functional PHP FPM service with the application preloaded. 
You still need to provide a database and webserver in order for it to function properly. Also make sure to configure
a secrets file and the correct environment variables. 

You can use the docker compose example from the `docker` directory to get you started, just configure the required 
environment variables & secrets and run `docker-compose up -d`.

Add a user with: `docker exec -itu www-data docker_argus_1 bin/console argus:add-user` 

When using an external HTTPS termination, make sure the set the trusted proxies variable for the Argus container.
The example does this by default.

# Do you have questions?

Do you think the documentation is a bit simple, or are you missing features? Open a ticket or just contribute! 
Every input is very welcome!

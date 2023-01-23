# Nova Eloquent Imagery

## Installation & Usage

For the Nova 4 compatible components:

```console
composer require ziffmedia/nova-eloquent-imagery ^2.0
```

For the Nova 3 compatible components

```console
composer require ziffmedia/nova-eloquent-imagery ^1.0
```

### Using The Field

tbd.

### Field Options

tbd.

## Development

You will need an `auth.json` that has the `http-basic.nova.laravel.com` setup, see Nova documentation here:
https://nova.laravel.com/docs/4.0/installation.html#authenticating-nova-in-ci-environments

(Generally, if you have an `auth.json` setup locally, you can copy that file).

> **Note**
> The auth.json has to exist at `demo/auth.json`.
>
> If it exists locally, then `cp ~/.composer/auth.json ./demo/auth.json`
>
> If it does not exist locally, grab a key from nova.laravel.com, and run the `composer config` command from the docs

To bring up the demo app, do the following:

```console
docker compose up -d
docker compose exec web bash
artisan migrate:fresh --seed
```



Now you may browse to http://localhost:8000/nova

A development user with the following credentials was created:

```console
email: developer@example.com
password: password
```



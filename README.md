# Emmanuel Ministries website

A Laravel 12 church website with editable public pages and a private content dashboard. It uses Laravel Blade and SQLite locally; the Render deployment uses PostgreSQL so dashboard changes persist across deploys.

## Run locally

Requirements: PHP 8.2+ with SQLite enabled and Composer.

For a fresh checkout, run these commands in PowerShell:

```powershell
composer install
Copy-Item .env.example .env
New-Item -ItemType File database/database.sqlite -Force
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Open `http://127.0.0.1:8000`. In this workspace, the app key, SQLite database, and sample content are already set up; start it with `php artisan serve`.

## Dashboard

Open `/admin/login` and sign in with:

- Email: `admin@emmanuelministries.org`
- Password: `Welcome123!`

Change the email and password immediately under **Site settings**. The dashboard manages every standard page, ministries, sermons, events, daily devotionals, church details, giving links, and contact form messages. Content can be saved as a draft or published.

To restore the starter content in a fresh database, run `php artisan migrate:fresh --seed`. This erases existing content, so do not run it on a live site.

## Deploy to Render

The repository includes a Render Blueprint (`render.yaml`) and Docker setup. Once Render is connected to the GitHub repository, it deploys each commit to the linked branch automatically. The Blueprint creates the web service and asks for a Neon PostgreSQL connection URL. In the current Neon project (`Emmanueldb`), the provided URL targets the `neondb` database. The web service is configured on Render's free plan, which can sleep while idle.

1. Push this repository to GitHub.
2. In Render, choose **New > Blueprint**, connect the GitHub repository, and select `render.yaml`.
3. When prompted, enter the Neon connection URL, dashboard admin email, and a strong password. Render generates the Laravel app key.
4. Create the Blueprint. Render builds the Docker image, runs migrations, seeds the starter content once, and starts the site.
5. After the first deploy, open the service URL and sign in at `/admin/login` with the admin email and password you supplied.

Future pushes to the linked branch trigger automatic deployments. The startup seeder preserves dashboard edits after the first initialization. Do not run `migrate:fresh` against the live database.

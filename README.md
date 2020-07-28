## Features
- Add and remove Discord Roles based on character, corporation and alliance.
- Change Discord name to display character name, corporation ticker and alliance ticker.
## Required information
- Discord Bot Token:
    - Go to https://discord.com/developers/applications
    - Create an app or open existing app
    - Navigte to bot and locate the bot token
- Guild ID & Guild Owner ID
    - Open your discord and enable the `Developer Mode` under `Appearance`
    - Right click the Discord you want to manage and select `Copy ID`
    - In the Discord, select the Discord Owner and select `Copy ID`
- APP ID & APP Secret
    - Go to https://developers.eveonline.com/applications
    - Create a new application with connection type `Authentication & API Access` and enable the permission `publicData`
    - After creating the application you can find the ID and the Secret under `APPLICATION SETTINGS`
## Installation
1. Clone this project onto your server.
2. Create a `.env.local` file with the following content:
    ```
    DATABASE_URL=
    APP_ENV=prod
    BOT_TOKEN=
    GUILD_ID=
    GUILD_OWNER=
    APP_ID=
    APP_SECRET=
    ADMINS=
    ```
    |Field|Value|
    |---|---|
    |DATABASE_URL|https://symfony.com/doc/5.1/doctrine.html#configuring-the-database|
    |APP_ENV|prod|
    |BOT_TOKEN|Discord Bot Token|
    |GUILD_ID|Discord Guild ID|
    |GUILD_OWNER|Discord Guild Owner ID|
    |APP_ID|EVE Application ID|
    |APP_SECRET|EVE Application Secret|
    |ADMINS|Comma separated list of EVE Character IDs who are suppose to be admins
3. Run `composer install`
4. Run `bin/console doctrine:migrations:migrate`
5. Setup your webserver to point at
```
<project root>/public
```
## Usage
### Logging in
Navigate to your configured URL. Use the `Log In with EVE Online` button to log in. If you are an admin, you can append a /admin to your URL to access the admin backend.
### Pulling the data from Discord
Roles will be pulled directly from Discord. If you want to add a new group, create it in Discord and pull the data.

You can either manually pull the data by using the `Pull Roles` button in the admin backend or setup a cronjob to run the following command:
```
cd <project root> && bin/console app:discord:pull
```

### Assigning roles
To assign roles to characters, corporations and alliances navigate to the respective option in the admin backend. To assign a role edit the entity and select the roles to assign.

### Applying roles to Discord
Upon logging in and connecting to Discord all already assigned roles will be applied automatically.
If you want to apply changes afterwards, you can either use the `Push Roles` button in the admin backend or setup a cronjob like this:
```
cd <project root> && bin/console app:discord:push
```
## if create git clone laravel
## step 1 : npm i in terminal

  ## step 1.1 : composer update
    ```
     xampp\php\php.ini  (click to php.ini)

     CTRL+F (write gd enable to extension )
    and run compuser update cmd.

    if again error then check to in php.ini
    enable to (extension:zip) file
    ```
  ## step 1.2 create the .env file 
  ```
   copy to to all .env.example and past to .env file 
   -->run command to ( php artisan key:generate) 
  ```
  ## step 1.3  run migration
  ``` 
   php artisan migrate
   ```
  ## Visit Google Cloud Console
  ```
    1.	
    2.Create a project or select an existing one.
    3.Enable "People API" from APIs & Services > Library.
    4.Go to OAuth consent screen and configure it.
    5.Go to Credentials and:
    o	Create OAuth 2.0 Client ID
    o	Application type: Web Application
    o	Add redirect URI: http://yourapp.com/auth/google/callback
    o	Save the Client ID and Client Secret.

  ```
  ## step 1.4 set the .env 
   ```
   GOOGLE_CLIENT_ID='dfgj'
   GOOGLE_CLIENT_SECRET='your client_secret'
   GOOGLE_REDIRECT_URI='redirct url'    //copy to route or pase here and your google console.cloud
   ```
  ## Run cmp in terminal (composer run dev)
  ## if error the Token then goto the step 2
## step 2 : Generate Token 
```
->Click Profile image rightside
->go to setting
-> got to last and click (delevoper setting)
->go to Personal access tokens section  →
  ->Click Tokens (classic).
  ->➡️ Generate new token 
  → Generate new token (classic)
  ->Note/Description(example: "Git Clone Token").
  ->click Generate Token button.
  "You will get one token string — make sure to save it, because you won’t be able to see it again later."

```
## step 3: In terminal pasth token
```
user-myproject>composer config --global github-oauth.github.com YOUR_TOKEN_HERE

C:/Users/shaikh/AppData/Roaming/Composer/auth.json
auth.json
{
    "github-oauth": {
        "github.com": "YOUR_TOKEN_HERE"
    }
}
```

## Generate the application key
```
php artisan key:generate

it uatomatically generate in the .env file
```
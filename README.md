## if create git clone laravel
## step 1 : npm i in terminal
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
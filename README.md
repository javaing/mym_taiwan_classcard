MYM TAIWAN 課卡系統

1. install homebrew
   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)" 
2. install PHP
   brew install php@8.1
   export PATH="/opt/homebrew/opt/php@8.1/bin:$PATH"
3. install composer
   https://getcomposer.org/
4. install lavaral
   composer global require "laravel/installer"
5. 啟動
   php artisan

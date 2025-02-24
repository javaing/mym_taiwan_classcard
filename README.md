MYM TAIWAN 課卡系統

1. install homebrew
   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)" 
2. install PHP
   brew tap shivammathur/php
   brew install shivammathur/php/php@7.4
   export PATH="/opt/homebrew/opt/php@7.2/bin:$PATH" >> ~/.zshrc 
4. install composer
   https://getcomposer.org/
5. install lavaral
   composer global require "laravel/installer"
6. 啟動
   composer install
   php artisan

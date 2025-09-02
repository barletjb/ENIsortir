# ENIsortir

## Participants

- BARLET Jean-Baptiste
- COURGEAU Bertrand
- POGU Hélène
- BILLAUD Nathalie

## Stack

- **PHP** : >= 8.1
- **Symfony** : version LTS 6.4
- **WampServer**
- **MySQL**

## Installation

1. Clone Project : 
```powershell
git clone https://github.com/barletjb/ENIsortir.git
```

2. Install Dependencies :  
```powershell
symfony composer install
```

3. Create Database: 
```powershell
symfony console doctrine:database:create
```

4. Execute Migrations : 
```powershell
symfony console doctrine:migrations:migrate
```

5. Install reset Password :
```powershell
symfony composer require symfonycasts/reset-password-bundle 
```

6. Install Mailer :  
```powershell
symfony composer require symfony/mailer

php bin/console app:rappel-mail /* Commande pour tester l'envoi de mail */
```


7. Start Symfony Server :  
```powershell
symfony serve
```











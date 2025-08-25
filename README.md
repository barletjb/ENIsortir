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

5. Start Symfony Server :  
```powershell
symfony serve
```











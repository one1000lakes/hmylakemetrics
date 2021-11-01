# Hmylakemetrics

Simple scripts for sending Harmony ONE validator node and backup node performance data to web server and visualize data. For example smartstake.io shows performance data of validator in summary but it doesn't give information about how primary node is performing compared to backup node. With this tool it's easy to visualize both nodes separately and see if there is any problems like bad sign percentage or disk space running low.

 * Hmylakemetrics consists of two parts: Scripts for nodes for sending data and website to visualize data
 * Node sends data to web server by http/https-protocol using php-arguments and web server inserts received values to mysql-database for visualization
 * Support our work by staking to ONE Thousand Lakes: https://staking.harmony.one/validators/mainnet/one1ugvtdxau0mmd38wt42na9la9melp84alnfkyx4
 
 Requirements:
  * Validator node: php-cli - installation is covered in instructions
  * Web server (node can also act as web server but not recommended): web server (apache2 etc.), php (tested with 7.3 but other versions should also work), mysql or mariadb (tested with mysql 8.0.19)
  
## Installing scripts on validator node

Make installation directory where you prefer and copy files and folders from validator folder to that directory. In this example we're using folder /data/hmylakemetrics/.

After copy there should be two *.sh* files in that directory and *datasource* and *php* directories with files in them (example: /data/hmylakemetrics/php). Actually files in datasource dir aren't required because they are overwritten anyway but they are there for example output.


### Installing php

You can use Ubuntu 20.04 php repository (version 7.4) or install newer 8.0 from Ondřej Surý ppa repository. Scripts should work on both versions.

PHP 8.0 adding repository and installation:

```
sudo apt update
sudo apt upgrade
sudo apt install lsb-release ca-certificates apt-transport-https software-properties-common -y
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.0
```

OR

PHP 7.4 from Ubuntu repository:

```
sudo apt update
sudo apt upgrade
sudo apt install php
```

*** REST OF THE DOCUMENTATION WILL BE FINISHED SOON ***
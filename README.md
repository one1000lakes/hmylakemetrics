# Hmylakemetrics

Simple scripts for sending Harmony ONE validator node and backup node performance data to web server and visualize data. For example smartstake.io shows performance data of validator in summary but it doesn't give information about how primary node is performing compared to backup node. With this tool it's easy to visualize both nodes separately and see if there is any problems like bad sign percentage or disk space running low.

 * Hmylakemetrics consists of two parts: Scripts for nodes for sending data and website to visualize data
 * Node sends data to web server by http/https-protocol using php-arguments and web server inserts received values to mysql-database for visualization
 * Support our work by staking to ONE Thousand Lakes: https://staking.harmony.one/validators/mainnet/one1ugvtdxau0mmd38wt42na9la9melp84alnfkyx4
 
 Requirements:
  * Validator node: hmy-cli (https://docs.harmony.one/home/network/validators/node-setup/hmy-cli-download), php-cli (installation is covered in instructions)
  * Web server (node can also act as web server but not recommended): web server (apache2 etc.), php (tested with 7.3 but other versions should also work), mysql or mariadb (tested with mysql 8.0.19)
 
## Live example (our one1000lakes validator)

https://www.onethousandlakes.fi/hmylakemetrics/


## Project state

This project is in progress. Current release is fully working with two nodes signing same shard. Later we'll release roadmap for upcoming features (added metrics, multiple nodes, multiple shards etc.).


## Installing scripts on validator node

Scripts need to be installed on both nodes separately (primary and backup). One of the nodes is called node 1 and other is node 2. Scripts are installed same way on both of them but be careful in configuration between both nodes.


### Step 1: Copying scripts to node

Make installation directory where you do prefer and copy files and folders from *validator* folder to that directory. In this example we're using */data/hmylakemetrics/* but you may also for example make new directory to your user home directory and use that. If you have git installed to node you can just clone this repository and copy files from there or download repository as zip and copy files to node using WinSCP. Or you can just download premade validator.tar file from this repository's *releases* folder which contains latest versions of needed files.

Example of making *hmylakemetrics* folder in home directory of user named *example-user* and downloading *validator.tar* and extracting it to created folder:
```
cd /home/example-user
mkdir hmylakemetrics
cd hmylakemetrics
wget -c https://github.com/one1000lakes/hmylakemetrics/blob/main/releases/validator.tar?raw=true -O validator.tar
tar -xvf validator.tar
rm validator.tar
```

In rest of examples in this guide we're using */data/hmylakemetrics/* as installation directory and here is same example for that. It is otherwise same but we need to change owner of that directory to our user (*example-user* in this example) so that we don't need to sudo rest of the commands:
```
mkdir /data
mkdir /data/hmylakemetrics
sudo chown example-user:example-user /data/hmylakemetrics
cd /data/hmylakemetrics
wget -c https://github.com/one1000lakes/hmylakemetrics/blob/main/releases/validator.tar?raw=true -O validator.tar
tar -xvf validator.tar
rm validator.tar
```

After copy/extract there should be two *.sh* files in our installation directory and *datasource*, *php* and *log* directories with files in them (for example: /data/hmylakemetrics/php). Actually files in *datasource* dir aren't required because they are overwritten anyway but they are there for example output.


### Step 2: Installing php

You can use Ubuntu 20.04 default php repository (version 7.4) or install newer php 8.0 from Ondřej Surý's ppa repository. Scripts work on both versions. We need only php command line version. If you plan to run also web server on validator node for visualization then you can install with command *sudo apt install php* and then also apache2 web server will be installed but I would recommend to keep web server separated from node.

PHP 7.4 from Ubuntu repository:

```
sudo apt update
sudo apt upgrade
sudo apt install php7.4-cli php7.4-common
```

OR

PHP 8.0 adding repository and installation:

```
sudo apt update
sudo apt upgrade
sudo apt install lsb-release ca-certificates apt-transport-https software-properties-common -y
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.0-cli php8.0-common
```

After installation you can test php is working by this command showing installed php version:

```
php -v
```

It should output depending on version:
PHP 7.4.3 (cli) ...

### Step 3: Configuration of php variables

Configure settings related to sending data to web server by editing *config.php* in *php* directory. In this example file is located in */data/hmylakemetrics/php/config.php*. You can use vim/nano/winscp etc. for editing.

For example with nano:
```
nano /data/hmylakemetrics/php/config.php
```

These lines should be edited:
```
$server_post_url = 'https://my.own.webserver.example/hmylakemetrics/update_values.php';
$api_key = 'xXx123YYYzzz';
$sender_node = 1;
$basepath = '/data/hmylakemetrics';
$devname_or_mntname = '/data';
```

$server_post_url: Set this to your web server url where *update_values.php* is located (this file is included in web server files).

$api_key: Set this to your randomly generated strong key (=password). You need to set same key here and to your web server config. You can use for example https://www.random.org/passwords/ to generate strong random key. Length isn't fixed, but it's recommended to use at least 16 characters.

$sender_node: Set this 1 or 2 depending on which node you are installing. For example primary node can be 1 and backup node 2 but they need to be different because data sender is identified by this.

$basepath: Directory where you copied files and folders from *validator* folder. This directory should contain *php* and *datasource* folders.

$devname_or_mntname: Dev or mount where your harmony database is located. This is used to monitor disk space. You can run *df* command to list filesystems and mount names. Input here is used to search correct row to pick up used and free disk space on that mount. In this example harmony database is mounted on */data* on device */dev/devb* so we could set this to */data* or */dev/devb*.

### Step 4: Configuration of ping addresses

Configure settings related to node's network ping measurement by editing *run_metrics.sh*. In this example file is located in */data/hmylakemetrics/run_metrics.sh*. You can use vim/nano/winscp etc. for editing.

These lines should be edited:
```
ping1address="fi.hma.rocks"
ping2address="ca.us.hma.rocks"
```

ping1address: Local ping address, set this address to some server in same country your node is located to measure if your own network latency is normal.

ping2address: Remote ping address, set this address to some server in same country where harmony api server is located (US) to measure if marine cables and international networks are working normally.

You can also set those variables to empty (="") to disable ping measurement.

### Step 5: Setting .sh scripts executable

Scripts need to be chmoded to executable. In this example files are located */data/hmylakemetrics/* so replace path with your installation directory.

```
chmod +x /data/hmylakemetrics/run_metrics.sh
chmod +x /data/hmylakemetrics/metrics_wrapper.sh
```

### Step 6: Schedule script to run in crontab

Script *metrics_wrapper.sh* needs to be scheduled to run at 1 minute and 5 minute interval. We are using crontab to do that. Two lines need to be added to */etc/crontab*. You can use vim/nano/winscp etc. for editing crontab. There is also example crontab included in this repo at *crontab example* but you shouldn't overwrite your crontab with this file, just add two lines two your existing crontab.

```
Syntax for metrics_wrapper.sh:
metrics_wrapper.sh -t MINUTEINTERVAL -p BASEPATH -e HMYPATH -s SHARD_NODE_SIGNS -r REMOTENODEADDRESS -h NODEHASH

MINUTEINTERVAL = Specifies which actions are run by this call. 1 minute interval includes latest block headers from local node, remote node (backup) and from harmony api and also local metrics. 5 minute interval includes all 1 minute actions and additionally validator information, disk size information, shard number this node is signing and ping measurements.

BASEPATH = Directory where scripts are installed (datasource and php folders and also .sh scripts)

HMYPATH = Path to hmy-tool executable

SHARD_NODE_SIGNS = Shard number this node is signing

REMOTENODEADDRESS = Ip or domain of this nodes *backup node*. If scripts are installed now on node 1 then input is node 2 address and vice versa.

NODEHASH = Validator node address. Same that can be viewed at https://staking.harmony.one/validators when opening details of validator under "Validator address"
```

Editing crontab using nano:
```
sudo nano /etc/crontab
```

Example lines to be added to */etc/crontab*:
```
1-4,6-9,11-14,16-19,21-24,26-29,31-34,36-39,41-44,46-49,51-54,56-59  *    * * *   example-user /data/hmylakemetrics/metrics_wrapper.sh -t 1 -p /data/hmylakemetrics -e /home/example-user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz > /data/hmylakemetrics/log/cron_log 2>&1
*/5  *    * * *   example-user /data/hmylakemetrics/metrics_wrapper.sh -t 5 -p /data/hmylakemetrics -e /home/example-user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz > /data/hmylakemetrics/log/cron_log 2>&1
```

Explanation of example:
First line is executed at minutes 1-4, 6-9, etc. and second line is executed at every 5 minutes meaning at minute 0, 5, etc. So every minute metrics_wrapper.sh is executed with other parameters being same but only telling if script should do 1 minute or 5 minute actions (-t flag). You need also specify your user name in crontab to run command. In this case user name is example-user. Location for metrics.wrapper.sh is */data/hmylakemetrics/metrics_wrapper.sh* and basepath for scripts is */data/hmylakemetrics*, hmy-executable is in users home directory so path is */home/example-user/hmy*, node is signing shard 1, backup node's address is *example.remotenod3.com* (so if this node is node 1 then that is node 2's address and vice versa), validator address is *one123456789abcdefghijklmnopqrstuvwxyz*. At the end of the line is *> /data/hmylakemetrics/log/cron_log 2>&1* which means that possible output of command will be written to */data/hmylakemetrics/log/cron_log*. We don't use that output for anything but if output is not specified cronjob will try to send output by e-mail and if e-mail is not configured it will always write errors to syslog about missing email configuration. We could also redirect output to */dev/null* but it's better to write them to file if there actually is some error message so it can be read from file.

After adding those lines you don't have to restart cron or anything like that. After editing crontab, system recognizes it automatically and reloads it.


### Step 7: Making sure it's working

Every 1 minute there should be .txt files updating in your installation directory's /datasource folder. Some of them are updating at 1 minute interval and some at 5 minute interval. Now script is also trying to update values to web server. Although if web server is not set up yet then those tries will fail.

Now you can continue to set up this node's backup node same way but be careful setting node number and remote node address other way round.


## Installing web server

This guide assumes that there is already web server with apache2/nginx etc, php and mysql/mariadb database installed. If not, there should be plenty of online guides for installing those (for example this https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-20-04 ). For Windows servers there is also XAMPP alternative which contains all needed components.

### Step 1: Copying web server files

Make new directory for hmylakemetrics to your web server webpage directory (for apache2 default directory is */var/www*). For example *hmylakemetrics* and copy files and folders from *webserver* folder to that directory. In this example we're using */var/www/hmylakemetrics/*. If you have git installed to node you can just clone this repository and copy files from there or download repository as zip and copy files to node using WinSCP. Or you can just download premade *webserver.tar* file from this repository's *releases* folder which contains latest versions of needed files.

Example of making *hmylakemetrics* folder in */var/www/* directory and downloading *webserver.tar* and extracting it to created folder:
```
cd /var/www/
sudo mkdir hmylakemetrics
cd hmylakemetrics
sudo wget -c https://github.com/one1000lakes/hmylakemetrics/blob/main/releases/webserver.tar?raw=true -O webserver.tar
sudo tar -xvf webserver.tar
sudo rm webserver.tar
```


### Step 2: Import database and table structure

Download .sql files from *database_structure_dump* to home directory and import them to mysql database. This will create database named *harmonymetrics* and create two tables in it.

```
cd /home/example-user
wget -c https://raw.githubusercontent.com/one1000lakes/hmylakemetrics/main/database_structure_dump/hmylakemetrics_metrics_now.sql -O hmylakemetrics_metrics_now.sql
wget -c https://raw.githubusercontent.com/one1000lakes/hmylakemetrics/main/database_structure_dump/hmylakemetrics_metrics_history.sql -O hmylakemetrics_metrics_history.sql
sudo mysql < hmylakemetrics_metrics_now.sql 
sudo mysql < hmylakemetrics_metrics_history.sql 
```

If you get error "unknown collation utf8mb4..." your mysql version may be older than version 5.5 and you need to use utf8 instead. In that case utf8mb4_0900_ai_ci needs to be replaced with utf8_general_ci and CHARSET=utf8mb4 needs to be replaced with CHARSET=utf8.

You can replace charset with these commands after downloading .sql files if your mysql doesn't support utf8mb4 charset:
```
sed -i 's/utf8mb4_0900_ai_ci/utf8_general_ci/g' hmylakemetrics_metrics_now.sql
sed -i 's/CHARSET=utf8mb4/CHARSET=utf8/g' hmylakemetrics_metrics_now.sql

sed -i 's/utf8mb4_0900_ai_ci/utf8_general_ci/g' hmylakemetrics_metrics_history.sql  
sed -i 's/CHARSET=utf8mb4/CHARSET=utf8/g' hmylakemetrics_metrics_history.sql  
```

### Step 3: Create mysql user for hmylakemetrics database

Next we create user in mysql for connecting to database and grant rights to database *hmylakemetrics*. Change *exampleuser* and *password123* by your own preferences.

```
sudo mysql
mysql> CREATE USER 'exampleuser'@'%' IDENTIFIED WITH mysql_native_password BY 'password123';
mysql> GRANT ALL ON hmylakemetrics.* TO 'exampleuser'@'%';
mysql> exit
```

### Allowing outside connections to mysql (skip this if mysql is running on same server with web server)

If your mysql database is running on different server than your web server or you want to admin your database with external tool (like MySQL Workbench) you need to change mysql binding address or it won't be connectable anywhere else than localhost. You don't need to do this your mysql is running on same server with web server and you don't need to use tools which need connection from other computer. And if you don't need this then you shouldn't change binding because allowing only local connections is more secure.

Allowing connections from all addresses (don't do this if not needed):
```
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf

Change this line:
bind-address = 127.0.0.1

To this:
#bind-address = 127.0.0.1

sudo service mysql restart
```

### Step 4: Create mysql user for hmylakemetrics database

Next we create user in mysql for connecting to database and grant rights to database *hmylakemetrics*. Change *exampleuser* and *password123* by your own preferences.

```
sudo mysql
mysql> CREATE USER 'exampleuser'@'%' IDENTIFIED WITH mysql_native_password BY 'password123';
mysql> GRANT ALL ON hmylakemetrics.* TO 'exampleuser'@'%';
mysql> exit
```

### Step 5: Configuring hmylakemetrics web page database connection

We need to configure database settings etc. to our web page. In this example web page files are located in */var/www/hmylakemetrics*.

First we edit *database.php*:

```
sudo nano /var/www/hmylakemetrics/update/database.php
```

You need to set your mysql server address (you can use localhost address 127.0.0.1 if mysql is running on same server). Also set mysql username and password to same which we created on previous step. You don't need to change DB_NAME if you imported database from dumps in step 2 and didn't change database name. Set API_KEY to same which you created in validator node's setup step 3. You can also change value of how many days old historical data is kept in metrics_history table. Default is 14 days and older data is deleted to keep database small enough.

```
//Database definition
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'exampleuser');
define('DB_PASSWORD', 'password123');
define('DB_NAME', 'hmylakemetrics');

//Api key definition
define('API_KEY', 'xXx123YYYzzz');

//Definition of how many days old historical measurement data is kept in history-table. Default value is '14'.
define('HISTORICAL_DAYS', '14');
```

### Step 6: Configuring hmylakemetrics web page personalization

Let's personalize web page by setting navbar text and link to validator home page. In this example web page files are located in */var/www/hmylakemetrics*.

Edit *staticmenus.php*:

```
sudo nano /var/www/hmylakemetrics/staticmenus.php
```


SITE_TITLE is text showing on web browser title bar. NAVBAR_TXT is text on navigation bar left corner. NAVBAR_LINK is link to your validator home page (or staking page etc.).

```
//Site title
define('SITE_TITLE', 'My validator / Node data');

//Site name on navbar
define('NAVBAR_TXT', 'My validator / Node data');

//Navbar link to validator home page
define('NAVBAR_LINK', 'https://www.myvalidatorhomepage.example');
```


### Step 7: Ready to go!

Now you should be able to access to your validator metric data with browser using your web server address: *https://my.own.webserver.example/hmylakemetrics*.
# Hmylakemetrics

Simple scripts for sending Harmony ONE validator node and backup node performance data to web server and visualize data. For example smartstake.io shows performance data of validator in summary but it doesn't give information about how primary node is performing compared to backup node. With this tool it's easy to visualize both nodes separately and see if there is any problems like bad sign percentage or disk space running low.

 * Hmylakemetrics consists of two parts: Scripts for nodes for sending data and website to visualize data
 * Node sends data to web server by http/https-protocol using php-arguments and web server inserts received values to mysql-database for visualization
 * Support our work by staking to ONE Thousand Lakes: https://staking.harmony.one/validators/mainnet/one1ugvtdxau0mmd38wt42na9la9melp84alnfkyx4
 
 Requirements:
  * Validator node: php-cli - installation is covered in instructions
  * Web server (node can also act as web server but not recommended): web server (apache2 etc.), php (tested with 7.3 but other versions should also work), mysql or mariadb (tested with mysql 8.0.19)
  
## Installing scripts on validator node

Scripts need to be installed on both nodes separately (primary and backup). One of the nodes is called node 1 and other is node 2. Scripts are installed same way on both of them but be careful in configuration between both nodes.

Make installation directory where you do prefer and copy files and folders from *validator* folder to that directory. In this example we're using */data/hmylakemetrics/* but you may also make new directory to your user home directory.

After copy there should be two *.sh* files in that directory and *datasource* and *php* directories with files in them (example: /data/hmylakemetrics/php). Actually files in datasource dir aren't required because they are overwritten anyway but they are there for example output.


### Step 1: Installing php

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

### Step 2: Configuration of php variables

Configure settings related to sending data to web server by editing *config.php* in *php* directory. In this example file is located in */data/hmylakemetrics/php/config.php*. You can use vim/nano/winscp etc. for editing.

These lines should be edited:
```
$server_post_url = 'http://my.own.webserver.example/hmylakemetrics/update_values.php';
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

### Step 3: Configuration of ping addresses

Configure settings related to node's network ping measurement by editing *run_metrics.sh*. In this example file is located in */data/hmylakemetrics/run_metrics.sh*. You can use vim/nano/winscp etc. for editing.

These lines should be edited:
```
ping1address="fi.hma.rocks"
ping2address="ca.us.hma.rocks"
```

ping1address: Local ping address, set this address to some server in same country your node is located to measure if your own network latency is normal.
ping2address: Remote ping address, set this address to some server in same country where harmony api server is located (US) to measure if marine cables and international networks are working normally.

You can also set those variables to empty (="") to disable ping measurement.

### Step 4: Setting .sh scripts executable

Scripts need to be chmoded to executable. In this example files are located */data/hmylakemetrics/* so replace path with your installation directory.

```
chmod +x /data/hmylakemetrics/run_metrics.sh
chmod +x /data/hmylakemetrics/metrics_wrapper.sh
```

### Step 5: Schedule script to run in crontab

Script *metrics_wrapper.sh* needs to be scheduled to run at 1 minute and 5 minute interval. We are using crontab to do that. Two lines need to be added to */etc/crontab*. You can use vim/nano/winscp etc. for editing crontab. There is also example crontab included in this repo at *crontab example* but you shouldn't overwrite you crontab with this file, just add two lines two your existing crontab.

Syntax for metrics_wrapper.sh:
metrics_wrapper.sh -t MINUTEINTERVAL -p BASEPATH -e HMYPATH -s SHARD_NODE_SIGNS -r REMOTENODEADDRESS -h NODEHASH

MINUTEINTERVAL = Specifies which actions are run by this call. 1 minute interval includes latest block headers from local node, remote node (backup) and from harmony api and also local metrics. 5 minute interval includes all 1 minute actions and additionally validator information, disk size information, shard number this node is signing and ping measurements.
BASEPATH = Directory where scripts are installed (datasource and php folders and also .sh scripts)
HMYPATH = Path to hmy executable
SHARD_NODE_SIGNS = Shard number this node is signing
REMOTENODEADDRESS = Ip or domain of this nodes *backup node*. If scripts are installed now on node 1 then input is node 2 address and vice versa.
NODEHASH = Validator node address. Same that can be viewed at https://staking.harmony.one/validators when opening details of validator under "Validator address"

Example lines to be added to */etc/crontab*:
```
1-4,6-9,11-14,16-19,21-24,26-29,31-34,36-39,41-44,46-49,51-54,56-59  *    * * *   example-user /data/hmylakemetrics/metrics_wrapper.sh -t 1 -p /data/hmylakemetrics -e /home/example-user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz
*/5  *    * * *   example-user /data/hmylakemetrics/metrics_wrapper.sh -t 5 -p /data/hmylakemetrics -e /home/example-user/hmy -s 1 -r example.remotenod3.com -h one123456789abcdefghijklmnopqrstuvwxyz
```

Explanation of example:
First line is executed at minutes 1-4, 6-9, etc. and second line is executed at every 5 minutes meaning at minute 0, 5, etc. So every minute metrics_wrapper.sh is executed with other parameters being same but only telling if script should do 1 minute or 5 minute actions (-t flag). You need also specify your user name in crontab to run command. In this case user name is example-user. Location for metrics.wrapper.sh is */data/hmylakemetrics/metrics_wrapper.sh* and basepath for scripts is */data/hmylakemetrics*, hmy-executable is in users home directory so path is */home/example-user/hmy*, node is signing shard 1, backup node's address is *example.remotenod3.com* (so if this node is node 1 then that is node 2's address and vice versa), validator address is *one123456789abcdefghijklmnopqrstuvwxyz*.


### Step 6: Making sure it's working

Every 1 minute there should be .txt files updating in your installation directory's /datasource folder. Some of them are updating at 1 minute interval and some at 5 minute interval. Now script is also trying to update values to web server. Although if web server is not set up yet then those tries will fail.

Now you can continue to set up this node's backup node same way but be careful setting node number and remote node address other way round.


## Installing web server

This guide assumes that there is already web server with apache2/nginx etc, php and mysql/mariadb database installed. If not, there should be plenty of online guides for installing those. For Windows servers there is also XAMPP alternative which contains all needed components.

### Step 1: Creating database




*** REST OF THE DOCUMENTATION WILL BE FINISHED SOON ***
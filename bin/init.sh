#!/bin/bash
# usage: ./init.sh /usr/bin/php http://localhost

phpcli=$1
baseUrl=$2
basePath=$(cd "$(dirname "$0")"; pwd)
if [ $# -ne 1 ]; then
  while :; do
    echo "Please input your php path:(example: /usr/bin/php)"
    read phpcli 
    if [ ! -f $phpcli ]; then 
      echo "php path is error";
    elif [ "$phpcli"x != ""x ]; then
      break;
    fi
  done
  while :; do
    echo "Please input zentao url:(example: http://localhost or http://127.0.0.1:88)"
    read baseUrl 
    if [ -z "$baseUrl" ]; then
      echo "zentao url is error"; 
    else
      break;
    fi
  done
fi 

if [ "`cat $basePath/../config/my.php | grep -c 'PATH_INFO'`" != 0 ];then
  requestType='PATH_INFO';
else
  requestType='GET';
fi

# ztcli
ztcli="$phpcli $basePath/ztcli \$*"
echo $ztcli > $basePath/ztcli.sh
echo "ztcli.sh ok"

# backup database
backup="$phpcli $basePath/php/backup.php"
echo $backup > $basePath/backup.sh
echo "backup.sh ok"

# computeburn
if [ $requestType == 'PATH_INFO' ]; then
  computeburn="$phpcli $basePath/ztcli '$baseUrl/project-computeburn'";
else
  computeburn="$phpcli $basePath/ztcli '$baseUrl/?m=project&f=computeburn'";
fi
echo $computeburn > $basePath/computeburn.sh
echo "computeburn.sh ok"

# daily remind
if [ $requestType == 'PATH_INFO' ]; then
  checkdb="$phpcli $basePath/ztcli '$baseUrl/report-remind'";
else
  checkdb="$phpcli $basePath/ztcli '$baseUrl/?m=report&f=remind'";
fi
echo $checkdb > $basePath/dailyreminder.sh
echo "dailyreminder.sh ok"

# check database
if [ $requestType == 'PATH_INFO' ]; then
  checkdb="$phpcli $basePath/ztcli '$baseUrl/admin-checkdb'";
else
  checkdb="$phpcli $basePath/ztcli '$baseUrl/?m=admin&f=checkdb'";
fi
echo $checkdb > $basePath/checkdb.sh
echo "checkdb.sh ok"

# syncsvn.
if [ $requestType == 'PATH_INFO' ]; then
  syncsvn="$phpcli $basePath/ztcli '$baseUrl/svn-run'";
else
  syncsvn="$phpcli $basePath/ztcli '$baseUrl/?m=svn&f=run'";
fi
echo $syncsvn > $basePath/syncsvn.sh
echo "syncsvn.sh ok"

# cron
if [ ! -d "$basePath/cron" ]; then 
  mkdir $basePath/cron
fi
echo "# system cron." > $basePath/cron/sys.cron
echo "#min   hour day month week  command." >> $basePath/cron/sys.cron
echo "0      1    *   *     *     $basePath/dailyreminder.sh   # dailyreminder."            >> $basePath/cron/sys.cron
echo "1      1    *   *     *     $basePath/backup.sh          # backup database and file." >> $basePath/cron/sys.cron
echo "1      23   *   *     *     $basePath/computeburn.sh     # compute burndown chart."   >> $basePath/cron/sys.cron
echo "1-59/2 *    *   *     *     $basePath/syncsvn.sh         # sync subversion."          >> $basePath/cron/sys.cron
cron="$phpcli $basePath/php/crond.php"
echo $cron > $basePath/cron.sh
echo "cron.sh ok"

chmod 755 $basePath/*.sh

exit 0

#!/bin/bash
set +e

# deploy idm
# ./deploy.sh

BASE_DIR=`dirname $0`

#sleep 5
echo "Transferring data"
rsync -avzh --exclude-from=".deployignore" --delete * -e "ssh -p 822 -o ConnectTimeout=5" headshot_ftp@kdn10.futureweb.at:/idm.headshot.at/

echo "Rsync data, clearing cache"
ssh -p 822 headshot_ftp@kdn10.futureweb.at "rm -rf /idm.headshot.at/var/cache/*"

# rsync -avzh --exclude='/.env' --exclude='/.git' --exclude='/node_modules' * -e "ssh -p 822" headshot_ftp@idm.headshot.at:/idm.headshot.at/
echo "Rsynced data, clearing cache"
ssh -p 822 headshot_ftp@neu.headshot.at "rm -rf /idm.kaiserlan.at/var/cache/* && /.phpenv/versions/8.3/bin/php /idm.kaiserlan.at/bin/console doctrine:schema:update --force --complete"

#!/usr/bin/env bash

cd /tmp/
mkdir peercoin
cd peercoin/

wget https://github.com/peercoin/peercoin/releases/download/v0.6.3ppc/Peercoin_v0.6.3_linux.zip
unzip Peercoin_v0.6.3_linux.zip
mv bin/64/peercoind /usr/local/bin/peercoind

cd ../
rm -rf peercoin/


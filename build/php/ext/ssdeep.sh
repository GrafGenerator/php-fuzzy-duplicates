#!/usr/bin/env bash

pushd $(pwd)
cd $(mktemp -d)

wget https://github.com/ssdeep-project/ssdeep/releases/download/release-2.14.1/ssdeep-2.14.1.tar.gz
tar zxvf ssdeep-2.14.1.tar.gz
cd ssdeep-2.14.1
./configure
make
make install
ssdeep -h # test
pecl install ssdeep
sh -c "echo 'extension=ssdeep.so' >> /etc/php/7.2/mods-available/ssdeep.ini"
phpenmod ssdeep

popd
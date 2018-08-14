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

wget https://github.com/treffynnon/lib_mysqludf_ssdeep/archive/1.0.2.tar.gz
tar zxvf 1.0.2.tar.gz
cd lib_mysqludf_ssdeep-1.0.2

mkdir build
./build.sh


cp lib_mysqludf_ssdeep.so /usr/lib/mysql/plugin/
cp ./src/installdb.sql /docker-entrypoint-initdb.d

ldconfig

popd
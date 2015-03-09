#!/bin/sh
if [ "$#" -ne 1 ]; then
    echo "Usage: $0 email_address"
    exit -1
fi

KEYFILE="$1.key"

if [ -e "${KEYFILE}" ]; then
    echo "File already exists, will not overwrite"
    exit -1
fi

openssl genrsa -out ${KEYFILE} 2048

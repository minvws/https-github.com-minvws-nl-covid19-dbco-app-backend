#!/usr/bin/env python3
import pkcs11
import codecs
import sys
import os

lib = pkcs11.lib(os.getenv('PKCS_MODULE'))
token = lib.get_token(token_label=os.getenv('SECURITY_MODULE_SLOT_LABEL'))
session = token.open(user_pin=os.getenv('SECURITY_MODULE_USER_PIN'))

wanted = int(sys.argv[1])
rand = session.generate_random(wanted * 8)
if len(rand) == wanted:
    print(codecs.encode(rand, 'hex').decode('ascii'))
    sys.exit(0)

print("ERROR_RANDOM_BYTES_LENGTH")
sys.exit(-1)

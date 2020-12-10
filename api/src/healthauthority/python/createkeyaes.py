#!/usr/bin/env python3
import pkcs11
import sys
import os
import json
import codecs
lib = pkcs11.lib(os.getenv('PKCS_MODULE'))
token = lib.get_token(token_label=os.getenv('HSM_SLOT_LABEL'))
session = token.open(user_pin=os.getenv('USERPIN'), rw=True)

if list(session.get_objects(attrs={pkcs11.Attribute.LABEL: sys.argv[1]})) == []:
    key = session.generate_key(pkcs11.KeyType.AES, 256, label=sys.argv[1],store=True)
    iv = session.generate_random(128)
    dbkey = session.generate_random(256)
    storagevalue = json.dumps({'encryptedkey': codecs.encode(key.encrypt(dbkey,mechanism_param=iv),'hex').decode('ascii'), 
                               'iv': codecs.encode(iv,'hex').decode('ascii')})
    session.create_object(attrs = {pkcs11.Attribute.CLASS: pkcs11.ObjectClass.DATA, 
                                   pkcs11.Attribute.APPLICATION: "ggdcontact", 
                                   pkcs11.Attribute.LABEL: sys.argv[1], 
                                   pkcs11.Attribute.VALUE: storagevalue.encode('ascii'),
                                   pkcs11.Attribute.TOKEN: True})
    print("New Key:",codecs.encode(dbkey,'hex').decode('ascii'))
    sys.exit(0)

print("Error duplicate key")
sys.exit(-1)

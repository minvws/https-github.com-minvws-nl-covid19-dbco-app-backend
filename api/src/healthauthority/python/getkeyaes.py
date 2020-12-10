#!/usr/bin/env python3
import pkcs11
import sys
import os
import json
import codecs
lib = pkcs11.lib(os.getenv('PKCS_MODULE'))
token = lib.get_token(token_label=os.getenv('HSM_SLOT_LABEL'))
session = token.open(user_pin=os.getenv('USERPIN'), rw=True)
key = list(session.get_objects(attrs={pkcs11.Attribute.CLASS: pkcs11.ObjectClass.SECRET_KEY, pkcs11.Attribute.KEY_TYPE: pkcs11.KeyType.AES, pkcs11.Attribute.LABEL: sys.argv[1]}))[-1]
jsondata = list(session.get_objects(attrs={pkcs11.Attribute.CLASS: pkcs11.ObjectClass.DATA, pkcs11.Attribute.LABEL: sys.argv[1]}))[-1]
if key and jsondata:
    data = json.loads(jsondata[pkcs11.Attribute.VALUE])
    iv = codecs.decode(data['iv'],'hex')
    encrypted = codecs.decode(data['encryptedkey'],'hex')
    dbkey = key.decrypt(encrypted,mechanism_param=iv)
    print("Key:",codecs.encode(dbkey,'hex').decode('ascii'))
    sys.exit(0)

print("Error: key not found")
sys.exit(-1)

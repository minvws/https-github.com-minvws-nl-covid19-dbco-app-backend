#!/usr/bin/env python3
import pkcs11
import sys
import os
import json
lib = pkcs11.lib(os.getenv('PKCS_MODULE'))
token = lib.get_token(token_label=os.getenv('HSM_SLOT_LABEL'))
session = token.open(user_pin=os.getenv('USERPIN'), rw=True)
print(json.dumps([x.label for x in session.get_objects(attrs={pkcs11.Attribute.CLASS: pkcs11.ObjectClass.SECRET_KEY, pkcs11.Attribute.KEY_TYPE: pkcs11.KeyType.AES}) ]))

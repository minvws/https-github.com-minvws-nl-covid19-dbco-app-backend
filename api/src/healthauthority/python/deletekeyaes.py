#!/usr/bin/env python3
import pkcs11
import sys
import os
lib = pkcs11.lib(os.getenv('PKCS_MODULE'))
token = lib.get_token(token_label=os.getenv('HSM_SLOT_LABEL'))
session = token.open(user_pin=os.getenv('USERPIN'), rw=True)
if list(session.get_objects(attrs={pkcs11.Attribute.LABEL: sys.argv[1]})) != []:
    todelete = list(session.get_objects(attrs={pkcs11.Attribute.LABEL: sys.argv[1]}))[0]
    todelete.destroy()
    todelete = list(session.get_objects(attrs={pkcs11.Attribute.LABEL: sys.argv[1]}))[0]
    todelete.destroy()
    sys.exit(0)
print("Key does not exist")
sys.exit(-1)
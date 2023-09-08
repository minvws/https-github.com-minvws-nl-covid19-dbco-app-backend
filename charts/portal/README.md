# Portal

## Configuration

Create the following secrets:

```yaml
apiVersion: v1
kind: Secret
metadata:
  name: portal-api-pkcs11
type: Opaque
stringData:
  cs_pkcs11_R2.cfg: |
    [Global]
    Logging = 0

    KeysExternal = false
    # KeyStore = /data/utimaco/slot1.pks

    SlotMultiSession = true
    SlotCount = 30
    KeepLeadZeros = false

    FallbackInterval = 0

    [CryptoServer]
    Device = 3001@hsm-simulator

    CommandTimeout = 300000
    ConnectionTimeout = 60000
    KeepAlive = true
```


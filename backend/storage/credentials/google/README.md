Encrypt File

``` bash
gcloud kms encrypt \
--ciphertext-file=./backend/storage/credentials/google/service-account-storage.json.enc \
--plaintext-file=./backend/storage/credentials/google/service-account-storage.json \
--location=global \
--keyring=key-file-json \
--key=service-account
```

Decrypt File

``` bash
gcloud kms decrypt \
--ciphertext-file=./backend/storage/credentials/google/service-account-storage.json.enc \
--plaintext-file=./backend/storage/credentials/google/service-account-storage.json \
--location=global \
--keyring=key-file-json \
--key=service-account
```

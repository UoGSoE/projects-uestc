#!/bin/bash


docker run --rm -p 9000:9000 -p 9001:9001 --name=uestsprojects-minio \
  -e "MINIO_ACCESS_KEY=AKIAIOSFODNN7EXAMPLE" \
  -e "MINIO_SECRET_KEY=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY" \
  minio/minio:RELEASE.2019-10-12T01-39-57Z server /data
